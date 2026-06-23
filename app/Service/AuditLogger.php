<?php

namespace App\Service;

use App\Model\Entity\Auditoria;

class AuditLogger{
	private const HIDDEN_KEYS = [
		'senha',
		'password',
		'senhaAtual',
		'novaSenha',
		'confirmeNovaSenha',
		'image',
		'fImage',
	];

	public static function snapshot($source, $fields){
		$data = [];

		foreach($fields as $field){
			if(is_array($source)){
				$value = $source[$field] ?? null;
			}else{
				$value = $source->$field ?? null;
			}

			$data[$field] = self::sanitizeValue($field, $value);
		}

		return $data;
	}

	public static function changedFields($before, $after){
		$changed = [];
		$keys = array_unique(array_merge(array_keys((array)$before), array_keys((array)$after)));

		foreach($keys as $key){
			$old = $before[$key] ?? null;
			$new = $after[$key] ?? null;

			if($old !== $new){
				$changed[] = $key;
			}
		}

		return $changed;
	}

	public static function record($request, $acao, $modulo, $entidade, $entidadeId, $descricao, $dadosAntes = null, $dadosDepois = null){
		$actor = self::getActor();

		return Auditoria::registrar([
			'usuario_id' => $actor['id'],
			'usuario_nome' => $actor['nome'],
			'usuario_tipo' => $actor['tipo'],
			'acao' => $acao,
			'modulo' => $modulo,
			'entidade' => $entidade,
			'entidade_id' => $entidadeId,
			'descricao' => $descricao,
			'dados_antes' => self::encodeJson($dadosAntes),
			'dados_depois' => self::encodeJson($dadosDepois),
			'ip' => self::getIp(),
			'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
			'criado_em' => date('Y-m-d H:i:s'),
		]);
	}

	private static function getActor(){
		if(session_status() !== PHP_SESSION_ACTIVE){
			session_start();
		}

		$user = $_SESSION['usuario'] ?? [];

		return [
			'id' => isset($user['id']) ? (int)$user['id'] : null,
			'nome' => $user['nome'] ?? 'Sistema',
			'tipo' => $user['tipo'] ?? '',
		];
	}

	private static function getIp(){
		$forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
		if($forwardedFor !== ''){
			$parts = explode(',', $forwardedFor);
			return trim($parts[0]);
		}

		return $_SERVER['REMOTE_ADDR'] ?? '';
	}

	private static function encodeJson($data){
		if($data === null || $data === []){
			return null;
		}

		$json = json_encode(self::sanitizeValue('', $data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		if($json === false){
			return null;
		}

		return strlen($json) > 60000 ? substr($json, 0, 60000) : $json;
	}

	private static function sanitizeValue($key, $value){
		$key = (string)$key;

		if(in_array($key, self::HIDDEN_KEYS, true)){
			return '[oculto]';
		}

		if(is_array($value)){
			$sanitized = [];
			foreach($value as $itemKey => $itemValue){
				$sanitized[$itemKey] = self::sanitizeValue($itemKey, $itemValue);
			}
			return $sanitized;
		}

		if(is_object($value)){
			return self::sanitizeValue($key, get_object_vars($value));
		}

		if(stripos($key, 'cpf') !== false){
			return self::maskCpf($value);
		}

		return $value;
	}

	private static function maskCpf($cpf){
		$digits = preg_replace('/\D+/', '', (string)$cpf);
		if(strlen($digits) !== 11){
			return $cpf;
		}

		return substr($digits, 0, 3).'.***.***-'.substr($digits, -2);
	}
}
