<?php

use \App\Http\Response;
use \App\Controller\Painel;
use \App\Session\User\Login as SessionUserLogin;

//ROTA de Listagem de Aulas
$obRouter->get('/aulas',[

    'middlewares' => [
        'require-user-login',
        'can:aulas.view'
    ],
                function ($request){
					return new Response(200, Painel\Aula::getAulas($request));
					
				}
		]);

//ROTA de get de nova Aula
$obRouter->get('/aulas/new',[
    'middlewares' => [
        'require-user-login',
        'can:aulas.create'
    ],
    
    function ($request){
        return new Response(200, Painel\Aula::getAulasNew($request));
        
    }
    ]);

//ROTA de POST de nova Aula
$obRouter->post('/aulas/new',[
    'middlewares' => [
        'require-user-login',
        'can:aulas.create'
    ],
    
    function ($request){
        return new Response(200, Painel\Aula::setAulasNew($request));
        
    }
    ]);


//ROTA de Get de Edição de Aula
$obRouter->get('/aulas/{id}/edit',[
	
    'middlewares' => [
        'require-user-login',
        'can:aulas.update'
    ],
		function ($request,$id){
		    return new Response(200, Painel\Aula::getAulaEdit($request,$id));
			
		}
		]);

//ROTA de Post de Edição de Aulas
$obRouter->post('/aulas/{id}/edit',[
    'middlewares' => [
        'require-user-login',
        'can:aulas.update'
    ],
    
		function ($request, $id){
		    return new Response(200, Painel\Aula::setAulaEdit($request, $id));
			
		}
		]);

//ROTA de Get de EXCLUSÃO de Aula
$obRouter->get('/aulas/{id}/delete',[
    
    'middlewares' => [
        'require-user-login',
        'can:aulas.delete'
    ],
    function ($request,$id){
        if(!SessionUserLogin::isAdmin()){
            $request->getRouter()->redirect('/aulas');
        }

        return new Response(200, Painel\Aula::getAulaDelete($request,$id));
        
    }
    ]);

//ROTA de Post de EXCLUSÃO de Aulas
$obRouter->post('/aulas/{id}/delete',[
    'middlewares' => [
        'require-user-login',
        'can:aulas.delete'
    ],
    
    function ($request, $id){
        if(!SessionUserLogin::isAdmin()){
            $request->getRouter()->redirect('/aulas');
        }

        return new Response(200, Painel\Aula::setAulaDelete($request, $id));
        
    }
    ]);


//ROTA de GET de presentes na aula
$obRouter->get('/aulas/{id}/presentes',[
    'middlewares' => [
        'require-user-login',
        'can:aulas.presentes'
    ],
    
    function ($request, $id){
        return new Response(200, Painel\Aula::getAulaPresentes($request, $id));
        
    }
    ]);
