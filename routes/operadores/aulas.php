<?php

use \App\Http\Response;
use \App\Controller\Operador;

//ROTA de Listagem de Aulas
$obRouter->get('/operador/aulas',[

    'middlewares' => [
        'require-operador-login'
    ],
                function ($request){
                    return new Response(200, Operador\Aula::getAulas($request));
					
				}
		]);



//ROTA de get de nova Aula
$obRouter->get('/operador/aulas/new',[
    'middlewares' => [
        'require-operador-login'
    ],
    
    function ($request){
        return new Response(200, Operador\Aula::getAulasNew($request));
        
    }
    ]);

//ROTA de POST de nova Aula
$obRouter->post('/operador/aulas/new',[
    'middlewares' => [
        'require-operador-login'
    ],
    
    function ($request){
        return new Response(200, Operador\Aula::setAulasNew($request));
        
    }
    ]);


//ROTA de Get de Edição de Aula
$obRouter->get('/operador/aulas/{id}/edit',[
	
    'middlewares' => [
        'require-operador-login'
    ],
		function ($request,$id){
		    return new Response(200, Operador\Aula::getAulaEdit($request,$id));
			
		}
		]);

//ROTA de Post de Edição de Aulas
$obRouter->post('/operador/aulas/{id}/edit',[
    'middlewares' => [
        'require-operador-login'
    ],
    
		function ($request, $id){
		    return new Response(200, Operador\Aula::setAulaEdit($request, $id));
			
		}
		]);


//ROTA de Get de EXCLUSÃO de Aula
$obRouter->get('/operador/aulas/{id}/delete',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    function ($request,$id){
        return new Response(200, Operador\Aula::getAulaDelete($request,$id));
        
    }
    ]);

//ROTA de Post de EXCLUSÃO de Aulas
$obRouter->post('/operador/aulas/{id}/delete',[
    'middlewares' => [
        'require-admin-login'
    ],
    
    function ($request, $id){
        return new Response(200, Operador\Aula::setAulaDelete($request, $id));
        
    }
    ]);
