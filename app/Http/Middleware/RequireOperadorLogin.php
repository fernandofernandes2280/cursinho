<?php

namespace App\Http\Middleware;

use \App\Session\Operador\Login as SessionOperadorLogin;


class RequireOperadorLogin{
	
	//Método responsavel por executar o middleware
	public function handle($request, $next){
		
		
		//Verifica se o usuario está logado
	    if(!SessionOperadorLogin::isLogged()){
			$request->getRouter()->redirect('/operador/login');
		}
		
		
		//Continua a execução
		return $next($request);
		
		
	}
	
}