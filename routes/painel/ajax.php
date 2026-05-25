<?php

use \App\Http\Response;
use \App\Controller\Painel;
use App\Http\Request;


//ROTA de Listage de Cid10
$obRouter->post('/delete',[
		'middlewares' => [
				'require-user-login'
		],
		
		
		function ($request){
			return new Response(200, Painel\Ajax::create($request));
		}
		]);

