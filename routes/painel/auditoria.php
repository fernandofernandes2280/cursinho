<?php

use App\Http\Response;
use App\Controller\Painel;

$obRouter->get('/auditoria', [
	'middlewares' => [
		'require-user-login',
		'require-admin',
	],

	function($request){
		return new Response(200, Painel\Auditoria::getAuditoria($request));
	}
]);

$obRouter->get('/auditoria/{id}', [
	'middlewares' => [
		'require-user-login',
		'require-admin',
	],

	function($request, $id){
		return new Response(200, Painel\Auditoria::getDetalhe($request, $id));
	}
]);
