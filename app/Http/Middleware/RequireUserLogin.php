<?php

namespace App\Http\Middleware;

use \App\Http\Response;
use \App\Session\User\Login as SessionUserLogin;
use \App\Utils\Funcoes;

class RequireUserLogin{
	public function handle($request, $next){
		if(!SessionUserLogin::isLogged()){
			if(Funcoes::isAjaxRequest($request)){
				return new Response(401, [
					'success' => false,
					'message' => 'Sua sessão expirou. Faça login novamente.',
					'redirectUrl' => URL.'/login',
				], 'application/json');
			}

			$request->getRouter()->redirect('/login');
		}

		return $next($request);
	}
}
