<?php

use \App\Http\Response;
use \App\Controller\Admin;


//ROTA de get para Inativar Aluno
$obRouter->get('/admin/inativar',[
		'middlewares' => [
				'require-admin-login'
		],
		
		
		function ($request){
		        return new Response(200, Admin\Inativar::getInativar($request));
		}
		]);


//ROTA post para Inativar Aluno
$obRouter->post('/admin/inativar',[
    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request){
        return new Response(200, Admin\Inativar::setInativar($request));
    }
    ]);