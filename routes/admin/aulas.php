<?php

use \App\Http\Response;
use \App\Controller\Admin;

//ROTA de Listagem de Aulas
$obRouter->get('/admin/aulas',[

    'middlewares' => [
        'require-admin-login'
    ],
                function ($request){
					return new Response(200, Admin\Aula::getAulas($request));
					
				}
		]);

//ROTA de POST de Agendas
$obRouter->post('/admin/agendas',[
	
    'middlewares' => [
        'require-admin-login'
    ],
		function ($request){
		    return new Response(200, Admin\Aula::setAgendas($request));
			
		}
		]);


//ROTA de get de nova Aula
$obRouter->get('/admin/aulas/new',[
    'middlewares' => [
        'require-admin-login'
    ],
    
    function ($request){
        return new Response(200, Admin\Aula::getAulasNew($request));
        
    }
    ]);

//ROTA de POST de nova Aula
$obRouter->post('/admin/aulas/new',[
    'middlewares' => [
        'require-admin-login'
    ],
    
    function ($request){
        return new Response(200, Admin\Aula::setAulasNew($request));
        
    }
    ]);


//ROTA de Get de Edição de Aula
$obRouter->get('/admin/aulas/{id}/edit',[
	
    'middlewares' => [
        'require-admin-login'
    ],
		function ($request,$id){
		    return new Response(200, Admin\Aula::getAulaEdit($request,$id));
			
		}
		]);

//ROTA de Post de Edição de Aulas
$obRouter->post('/admin/aulas/{id}/edit',[
    'middlewares' => [
        'require-admin-login'
    ],
    
		function ($request, $id){
		    return new Response(200, Admin\Aula::setAulaEdit($request, $id));
			
		}
		]);

