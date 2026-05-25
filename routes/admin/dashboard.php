<?php

use \App\Http\Response;
use App\Controller\Admin;



//ROTA GET DE DASHBOARD DO ADMIN
$obRouter->get('/admin/dashboard',[
		'middlewares' => [
				'require-user-login'
		],
		
		
		function ($request){
			return new Response(200, Admin\Dashboard::getDashboard($request));
		    
		}
		]);
		

//Compatibilidade com URLs antigas de operador.
$obRouter->get('/operador/dashboard',[
    'middlewares' => [
        'require-user-login'
    ],
    
    
    function ($request){
        $request->getRouter()->redirect('/dashboard');
    }
    ]);
