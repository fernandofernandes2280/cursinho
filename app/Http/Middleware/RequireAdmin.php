<?php

namespace App\Http\Middleware;

use App\Http\Response;
use App\Session\User\Login as SessionUserLogin;

class RequireAdmin{
	public function handle($request, $next){
		if(!SessionUserLogin::isAdmin()){
			return new Response(403, 'Você não tem permissão para acessar esta página. Contate o administrador.');
		}

		return $next($request);
	}
}
