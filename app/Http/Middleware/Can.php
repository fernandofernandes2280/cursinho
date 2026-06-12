<?php

namespace App\Http\Middleware;

use App\Http\Response;
use App\Session\User\Login as SessionUserLogin;

class Can{
	private $permission;

	public function __construct($permission = null){
		$this->permission = $permission;
	}

	public function handle($request, $next){
		if($this->permission === null || $this->permission === ''){
			return new Response(500, 'Permissão não informada para a rota.');
		}

		if(!SessionUserLogin::can($this->permission)){
			return new Response(403, 'Você não tem permissão para acessar esta página. Contate o administrador.');
		}

		return $next($request);
	}
}
