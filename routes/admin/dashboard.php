<?php

use \App\Http\Response;
use App\Controller\Admin;
use App\Controller\Operador;



//ROTA GET DE DASHBOARD DO ADMIN
$obRouter->get('/admin/dashboard',[
		'middlewares' => [
				'require-admin-login'
		],
		
		
		function ($request){
			return new Response(200, Admin\Dashboard::getDashboard($request));
		    
		}
		]);
		

//ROTA GET DE DASHBOARD DO OPERADOR
$obRouter->get('/operador/dashboard',[
    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request){
        return new Response(200, Operador\Dashboard::getDashboard($request));
       
        
    }
    ]);
