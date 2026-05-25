<?php

use \App\Http\Response;
use \App\Controller\Painel;


//ROTA de get para Inativar Aluno
$obRouter->get('/inativar',[
		'middlewares' => [
				'require-user-login'
		],
		
		
		function ($request){
		        return new Response(200, Painel\Inativar::getInativar($request));
		}
		]);


//ROTA post para Inativar Aluno
$obRouter->post('/inativar',[
    'middlewares' => [
        'require-user-login'
    ],
    
    
    function ($request){
        return new Response(200, Painel\Inativar::setInativar($request));
    }
    ]);