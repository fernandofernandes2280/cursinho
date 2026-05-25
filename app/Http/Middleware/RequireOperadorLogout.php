<?php

namespace App\Http\Middleware;

use \App\Session\User\Login as SessionUserLogin;


class RequireOperadorLogout{
	
	//Método responsavel por executar o middleware
	public function handle($request, $next){
		
		
		//Verifica se o usuario está logado
	    if(SessionUserLogin::isLogged()){
			$request->getRouter()->redirect('/dashboard');
		}
		
		
		//Continua a execução
		return $next($request);
		
		
	}
	
}
