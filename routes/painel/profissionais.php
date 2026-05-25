<?php

use \App\Http\Response;
use \App\Controller\Painel;

//ROTA DE LISTAGEM DE PROFISSIONAL
$obRouter->get('/profissionais',[
		'middlewares' => [
				'require-user-login'
		],
		
		
		function ($request){
		    return new Response(200, Painel\Profissional::getProfissionais($request));
		    
		}
		]);


//ROTA GET DE EDIT DE PROFISSIONAL
$obRouter->get('/profissionais/{id}/edit',[
    'middlewares' => [
        'require-user-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Painel\Profissional::getEditProfissional($request,$id));
        
    }
    ]);

//ROTA DE POST DE EDIT PROFISSIONAL
$obRouter->post('/profissionais/{id}/edit',[
    'middlewares' => [
        'require-user-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Painel\Profissional::setEditProfissional($request,$id));
        
    }
    ]);

//ROTA GET DE ACESSO AO SISTEMA PELO PROFISSIONAL
$obRouter->get('/profissionais/{id}/acesso',[
    'middlewares' => [
        'require-user-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Painel\User::getNewUser($request,$id));
        
    }
    ]);

//ROTA POST DE ACESSO AO SISTEMA PELO PROFISSIONAL
$obRouter->post('/profissionais/{id}/acesso',[
    'middlewares' => [
        'require-user-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Painel\User::setNewUser($request,$id));
        
    }
    ]);

//ROTA GET DE NOVO PROFISSIONAL
$obRouter->get('/profissionais/new',[
    'middlewares' => [
        'require-user-login'
    ],
    
    
    function ($request){
        return new Response(200, Painel\Profissional::getNewProfissional($request));
        
    }
    ]);

//ROTA POST DE NOVO PROFISSIONAL
$obRouter->post('/profissionais/new',[
    'middlewares' => [
        'require-user-login'
    ],
    
    
    function ($request){
        return new Response(200, Painel\Profissional::setNewProfissional($request));
        
    }
    ]);


//ROTA GET DE EXCLUIR PROFISSIONAL
$obRouter->get('/profissionais/{id}/delete',[
    'middlewares' => [
        'require-user-login'
    ],
    
    
    function ($request, $id){
        //apenas administrador pode excluir paciente
        if($_SESSION['usuario']['tipo'] == 'Admin')
                return new Response(200, Painel\Profissional::getDeleteProfissional($request, $id));
            else
                return new Response(200, 'Você não tem permissão. Contate o Administrador! <a href="javascript:history.back()">Voltar</a>');
    }
    ]);

//ROTA POST DE EXCLUIR PROFISSIONAL
$obRouter->post('/profissionais/{id}/delete',[
    'middlewares' => [
        'require-user-login'
    ],
    
    
    function ($request, $id){
        return new Response(200, Painel\Profissional::setDeleteProfissional($request, $id));
        
    }
    ]);
