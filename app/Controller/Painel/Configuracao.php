<?php

namespace App\Controller\Painel;

use \App\Utils\View;
use \App\Utils\Funcoes;
use \App\Model\Entity\Configuracao as EntityConfiguracao;

class Configuracao extends Page{
	private const LOGO_UPLOAD_RELATIVE_DIR = 'app/Controller/File/files/logos';
	private const LOGO_ALLOWED_MIMES = [
		'image/png' => 'png',
		'image/jpeg' => 'jpg',
		'image/webp' => 'webp',
	];

	private static function getLogoVersion(){
		$logoPath = dirname(__DIR__, 3).'/'.ltrim(EntityConfiguracao::getLogoRelatorio(), '/');

		return is_file($logoPath) ? filemtime($logoPath) : time();
	}

	private static function uploadLogoRelatorio($request){
		$fileVars = $request->getFileVars();
		$file = $fileVars['logoRelatorio'] ?? null;

		if(!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE){
			return true;
		}

		if(($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')){
			return false;
		}

		$mime = '';
		if(function_exists('finfo_open')){
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
			if($finfo){
				finfo_close($finfo);
			}
		}

		if(!$mime && function_exists('mime_content_type')){
			$mime = mime_content_type($file['tmp_name']);
		}

		if(!isset(self::LOGO_ALLOWED_MIMES[$mime])){
			return false;
		}

		$uploadDir = dirname(__DIR__).'/File/files/logos';

		if(!is_dir($uploadDir)){
			mkdir($uploadDir, 0775, true);
		}

		$extension = self::LOGO_ALLOWED_MIMES[$mime];
		$fileName = 'relatorio-logo-'.date('YmdHis').'-'.bin2hex(random_bytes(4)).'.'.$extension;
		$destination = $uploadDir.'/'.$fileName;

		if(!move_uploaded_file($file['tmp_name'], $destination)){
			return false;
		}

		EntityConfiguracao::setLogoRelatorio(self::LOGO_UPLOAD_RELATIVE_DIR.'/'.$fileName);

		return true;
	}

	public static function getConfiguracoes($request){
		$logoRelatorio = EntityConfiguracao::getLogoRelatorioUrl();

		$content = View::render('painel/modules/configuracoes/form',[
			'title' => 'Configurações',
			'statusMessage' => Funcoes::getStatus($request),
			'tituloRelatorio' => htmlspecialchars(EntityConfiguracao::getTituloRelatorio(), ENT_QUOTES, 'UTF-8'),
			'logoRelatorio' => htmlspecialchars($logoRelatorio, ENT_QUOTES, 'UTF-8'),
			'logoVersion' => self::getLogoVersion(),
		]);

		return parent::getPanel('Configurações > Cursinho', $content, 'configuracoes');
	}

	public static function setConfiguracoes($request){
		$postVars = $request->getPostVars();
		$tituloRelatorio = $postVars['tituloRelatorio'] ?? '';

		EntityConfiguracao::setTituloRelatorio($tituloRelatorio);
		$status = self::uploadLogoRelatorio($request) ? 'updated' : 'logoInvalid';

		$request->getRouter()->redirect('/configuracoes?statusMessage='.$status);
	}
}
