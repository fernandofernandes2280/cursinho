<?php

namespace App\Http\Middleware;

use App\Http\Response;
use App\Session\User\Login as SessionUserLogin;
use App\Utils\Funcoes;

class Can{
	private $permission;

	public function __construct($permission = null){
		$this->permission = $permission;
	}

	public function handle($request, $next){
		if($this->permission === null || $this->permission === ''){
			if(Funcoes::isAjaxRequest($request)){
				return new Response(500, [
					'success' => false,
					'message' => 'Permissão não informada para a rota.',
				], 'application/json');
			}

			return new Response(500, 'Permissão não informada para a rota.');
		}

		if(!SessionUserLogin::can($this->permission)){
			if(Funcoes::isAjaxRequest($request)){
				return new Response(403, [
					'success' => false,
					'message' => 'Você não tem permissão para acessar esta página. Contate o administrador.',
				], 'application/json');
			}

			return new Response(403, 'Você não tem permissão para acessar esta página. Contate o administrador.');
		}

		return $next($request);
	}
}
