<?php

namespace App\Controller\Operador;

use App\Utils\View;

class Alert{
	
	//Método responsavel por retornar uma mensagem de erro
	public static function getError($message){
		return View::render('admin/alert/status',[
				'tipo' => 'danger',
				'mensagem' => $message
		]);
	}
	
	//Método responsavel por retornar uma mensagem de sucesso
	public static function getSuccess($message){
		return View::render('admin/alert/status',[
				'tipo' => 'success',
				'mensagem' => $message
		]);
	}
	
	//Método responsavel por retornar uma mensagem de Atenção
	public static function getWarning($message){
	    return View::render('admin/alert/status',[
	        'tipo' => 'warning',
	        'mensagem' => $message
	    ]);
	}

	
}