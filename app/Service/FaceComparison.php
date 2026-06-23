<?php

namespace App\Service;

use App\Model\Entity\Aluno as EntityAluno;

class FaceComparison{
	private const FOTO_PADRAO = 'profile.png';

	public static function comparar($fotoAuditoria, EntityAluno $obAluno){
		$auditFile = self::getAuditPhotoPath($fotoAuditoria);
		$studentFile = self::getStudentPhotoPath($obAluno);

		if($auditFile === null){
			return self::result('sem_captura', 'Sem captura', null, 'A foto de auditoria não foi capturada.');
		}

		if($studentFile === null){
			return self::result('sem_foto_aluno', 'Sem foto do aluno', null, 'A foto cadastrada do aluno não está disponível.');
		}

		if(!function_exists('imagecreatetruecolor') || !function_exists('getimagesize')){
			return self::result('indisponivel', 'Indisponível', null, 'A extensão GD do PHP não está disponível.');
		}

		$audit = self::buildSignature($auditFile);
		$student = self::buildSignature($studentFile);

		if($audit === null || $student === null){
			return self::result('indisponivel', 'Indisponível', null, 'Não foi possível processar uma das imagens.');
		}

		$phash = self::hashSimilarity($audit['phash'], $student['phash']);
		$dhash = self::hashSimilarity($audit['dhash'], $student['dhash']);
		$histogram = self::histogramSimilarity($audit['histogram'], $student['histogram']);
		$score = round((($phash * 0.45) + ($dhash * 0.35) + ($histogram * 0.20)) * 100, 2);

		if($score >= 68){
			$status = 'compativel';
			$label = 'Compatível';
		}elseif($score >= 45){
			$status = 'verificar';
			$label = 'Verificar';
		}else{
			$status = 'divergente';
			$label = 'Divergente';
		}

		return self::result($status, $label, $score, 'Comparação local por assinatura visual.', [
			'phash' => round($phash * 100, 2),
			'dhash' => round($dhash * 100, 2),
			'histograma' => round($histogram * 100, 2),
		]);
	}

	private static function result($status, $label, $score, $message, $metrics = []){
		return [
			'status' => $status,
			'label' => $label,
			'score' => $score,
			'message' => $message,
			'metrics' => $metrics,
			'createdAt' => date('Y-m-d H:i:s'),
		];
	}

	private static function getAuditPhotoPath($fotoAuditoria){
		$fotoAuditoria = basename((string)$fotoAuditoria);
		if($fotoAuditoria === ''){
			return null;
		}

		$path = dirname(__DIR__).'/Controller/File/files/frequencias-auditoria/'.$fotoAuditoria;

		return is_file($path) ? $path : null;
	}

	private static function getStudentPhotoPath(EntityAluno $obAluno){
		$foto = basename((string)$obAluno->getFoto(false));
		if($foto === '' || $foto === self::FOTO_PADRAO){
			return null;
		}

		$path = dirname(__DIR__).'/Controller/File/files/fotos/'.$foto;

		return is_file($path) ? $path : null;
	}

	private static function buildSignature($path){
		$image = self::createImage($path);
		if(!$image){
			return null;
		}

		$normalized = self::normalizeImage($image, 64, 64);
		imagedestroy($image);

		if(!$normalized){
			return null;
		}

		$signature = [
			'phash' => self::pHash($normalized),
			'dhash' => self::dHash($normalized),
			'histogram' => self::histogram($normalized),
		];

		imagedestroy($normalized);

		return $signature;
	}

	private static function createImage($path){
		$info = @getimagesize($path);
		if($info === false){
			return null;
		}

		switch($info['mime'] ?? ''){
			case 'image/jpeg':
				return @imagecreatefromjpeg($path);
			case 'image/png':
				return @imagecreatefrompng($path);
			case 'image/gif':
				return @imagecreatefromgif($path);
			case 'image/webp':
				return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null;
		}

		return null;
	}

	private static function normalizeImage($image, $width, $height){
		$sourceWidth = imagesx($image);
		$sourceHeight = imagesy($image);

		if($sourceWidth <= 0 || $sourceHeight <= 0){
			return null;
		}

		$cropWidth = (int)round($sourceWidth * 0.72);
		$cropHeight = (int)round($sourceHeight * 0.78);
		$cropSize = max(1, min($cropWidth, $cropHeight));
		$sourceX = max(0, (int)round(($sourceWidth - $cropSize) / 2));
		$sourceY = max(0, (int)round(($sourceHeight - $cropSize) / 2));

		$target = imagecreatetruecolor($width, $height);
		imagecopyresampled($target, $image, 0, 0, $sourceX, $sourceY, $width, $height, $cropSize, $cropSize);

		return $target;
	}

	private static function grayscaleMatrix($image, $size){
		$width = imagesx($image);
		$height = imagesy($image);
		$matrix = [];

		for($y = 0; $y < $size; $y++){
			$row = [];
			for($x = 0; $x < $size; $x++){
				$sourceX = (int)floor($x * $width / $size);
				$sourceY = (int)floor($y * $height / $size);
				$rgb = imagecolorat($image, $sourceX, $sourceY);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				$row[] = (0.299 * $r) + (0.587 * $g) + (0.114 * $b);
			}
			$matrix[] = $row;
		}

		return $matrix;
	}

	private static function pHash($image){
		$size = 32;
		$matrix = self::grayscaleMatrix($image, $size);
		$dct = [];

		for($u = 0; $u < 8; $u++){
			for($v = 0; $v < 8; $v++){
				$sum = 0;
				for($x = 0; $x < $size; $x++){
					for($y = 0; $y < $size; $y++){
						$sum += $matrix[$y][$x]
							* cos(((2 * $x + 1) * $u * M_PI) / (2 * $size))
							* cos(((2 * $y + 1) * $v * M_PI) / (2 * $size));
					}
				}

				$cu = $u === 0 ? 1 / sqrt(2) : 1;
				$cv = $v === 0 ? 1 / sqrt(2) : 1;
				$dct[] = 0.25 * $cu * $cv * $sum;
			}
		}

		$values = array_slice($dct, 1);
		$median = self::median($values);

		return array_map(function($value) use ($median){
			return $value >= $median ? 1 : 0;
		}, $values);
	}

	private static function dHash($image){
		$size = 17;
		$matrix = self::grayscaleMatrix($image, $size);
		$hash = [];

		for($y = 0; $y < 16; $y++){
			for($x = 0; $x < 16; $x++){
				$hash[] = $matrix[$y][$x] > $matrix[$y][$x + 1] ? 1 : 0;
			}
		}

		return $hash;
	}

	private static function histogram($image){
		$matrix = self::grayscaleMatrix($image, 32);
		$histogram = array_fill(0, 16, 0);
		$total = 0;

		foreach($matrix as $row){
			foreach($row as $value){
				$index = min(15, max(0, (int)floor($value / 16)));
				$histogram[$index]++;
				$total++;
			}
		}

		if($total === 0){
			return $histogram;
		}

		return array_map(function($value) use ($total){
			return $value / $total;
		}, $histogram);
	}

	private static function median($values){
		sort($values);
		$count = count($values);
		$middle = (int)floor($count / 2);

		if($count % 2){
			return $values[$middle];
		}

		return ($values[$middle - 1] + $values[$middle]) / 2;
	}

	private static function hashSimilarity($a, $b){
		$count = min(count($a), count($b));
		if($count === 0){
			return 0;
		}

		$distance = 0;
		for($i = 0; $i < $count; $i++){
			if((int)$a[$i] !== (int)$b[$i]){
				$distance++;
			}
		}

		return max(0, 1 - ($distance / $count));
	}

	private static function histogramSimilarity($a, $b){
		$count = min(count($a), count($b));
		$intersection = 0;

		for($i = 0; $i < $count; $i++){
			$intersection += min((float)$a[$i], (float)$b[$i]);
		}

		return max(0, min(1, $intersection));
	}
}
