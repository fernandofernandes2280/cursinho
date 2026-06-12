<?php

namespace App\Controller\Painel;

use App\Utils\View;

class Alert{

	private static function getAttributes($attributes){
		if(!is_array($attributes) || empty($attributes)){
			return '';
		}

		$html = '';
		foreach ($attributes as $name => $value) {
			$html .= ' '.$name.'="'.htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8').'"';
		}

		return $html;
	}
	
	//Método responsavel por retornar uma mensagem de erro
	public static function getError($message, $attributes = []){
		return View::render('painel/alert/status',[
				'tipo' => 'danger',
				'mensagem' => $message,
				'atributos' => self::getAttributes($attributes)
		]);
	}
	
	//Método responsavel por retornar uma mensagem de sucesso
	public static function getSuccess($message, $attributes = []){
		return View::render('painel/alert/status',[
				'tipo' => 'success',
				'mensagem' => $message,
				'atributos' => self::getAttributes($attributes)
		]);
	}
	
	//Método responsavel por retornar uma mensagem de Atenção
	public static function getWarning($message, $attributes = []){
	    return View::render('painel/alert/status',[
	        'tipo' => 'warning',
	        'mensagem' => $message,
	        'atributos' => self::getAttributes($attributes)
	    ]);
	}

	
}
