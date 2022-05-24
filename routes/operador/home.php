<?php

use \App\Http\Response;
use \App\Controller\Admin;


//ROTA Admin
$obRouter->get('/admin',[
		'middlewares' => [
				'require-admin-login'
		],
		
		
		function ($request){
			//return new Response(200, Admin\Home::getHome($request));
		    return new Response(200, Admin\Aluno::getAlunos($request));
		}
		]);
		
		