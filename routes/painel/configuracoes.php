<?php

use \App\Http\Response;
use \App\Controller\Painel;

$obRouter->get('/configuracoes',[
	'middlewares' => [
		'require-user-login',
		'can:configuracoes.view'
	],

	function ($request){
		return new Response(200, Painel\Configuracao::getConfiguracoes($request));
	}
]);

$obRouter->post('/configuracoes',[
	'middlewares' => [
		'require-user-login',
		'can:configuracoes.update'
	],

	function ($request){
		return new Response(200, Painel\Configuracao::setConfiguracoes($request));
	}
]);
