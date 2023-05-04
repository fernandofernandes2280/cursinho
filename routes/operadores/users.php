<?php

use \App\Http\Response;
use \App\Controller\Operador;



//ROTA get para alterar senha
$obRouter->get('/operador/trocarSenha',[
		'middlewares' => [
				'require-operador-login'
		],
		function ($request){
		    return new Response(200, Operador\Senha::getTrocarSenha($request));
			//return new Response(200, Visitor\Home::getHome($request));
		}
		]);


$obRouter->post('/operador/trocarSenha',[
		'middlewares' => [
				'require-operador-login'
		],
		function ($request){
		    return new Response(200, Operador\Senha::setTrocarSenha($request));
			//return new Response(200, Visitor\Home::getHome($request));
		}
		]);
