<?php

use \App\Http\Response;
use \App\Controller\Painel;

//Rota de listagem de professores
$obRouter->get('/professores',[

    'middlewares' => [
        'require-user-login',
        'can:professores.view'
    ],
    
    
    function ($request){
        return new Response(200, Painel\Professor::getProfessores($request));
    }
    ]);

//Rota de listagem de professores
$obRouter->post('/professores',[

    'middlewares' => [
        'require-user-login',
        'can:professores.view'
    ],
    
    
    function ($request){
        return new Response(200, Painel\Professor::getProfessores($request));
    }
    ]);


//ROTA de Edição de um de Professor
$obRouter->get('/professores/{id}/edit',[
    
    'middlewares' => [
        'require-user-login',
        'can:professores.update'
    ],
    
    
    function ($request,$id){
        return new Response(200, Painel\Professor::getEditProfessor($request,$id));
    }
    ]);

//ROTA de Edição de um de Paciente (POST)
$obRouter->post('/professores/{id}/edit',[
   
    'middlewares' => [
        'require-user-login',
        'can:professores.update'
    ],
    
    
    function ($request,$id){
        return new Response(200, Painel\Professor::setEditProfessor($request,$id));
    }
    ]);

//ROTA de Exclusão de documento do professor
$obRouter->post('/professores/{id}/documentos/{documento}/delete',[

    'middlewares' => [
        'require-user-login',
        'can:professores.update'
    ],

    function ($request,$id,$documento){
        return new Response(200, Painel\Professor::setDeleteDocumentoProfessor($request,$id,$documento));
    }
    ]);


//Rota GET para Novo Professor
$obRouter->get('/professores/new',[

    'middlewares' => [
        'require-user-login',
        'can:professores.create'
    ],
    
    
    function ($request){
        return new Response(200, Painel\Professor::getNewProfessor($request));
    }
    ]);

//Rota POST para Novo Professor
$obRouter->post('/professores/new',[

    'middlewares' => [
        'require-user-login',
        'can:professores.create'
    ],
    
    
    function ($request){
        return new Response(200, Painel\Professor::setNewProfessor($request));
    }
    ]);


//Rota GET para excluir Professor
$obRouter->get('/professores/{id}/delete',[

    'middlewares' => [
        'require-user-login',
        'can:professores.delete'
    ],
    
    
    function ($request,$id){
        return new Response(200, Painel\Professor::getDeleteProfessor($request,$id));
    }
    ]);

//Rota POST para excluir Professor
$obRouter->post('/professores/{id}/delete',[

    'middlewares' => [
        'require-user-login',
        'can:professores.delete'
    ],
    
    
    function ($request,$id){
        return new Response(200, Painel\Professor::setDeleteProfessor($request,$id));
    }
    ]);

//ROTA de Captura Foto do professores
$obRouter->get('/professores/photo/{id}',[
    
    'middlewares' => [
        'require-user-login',
        'can:professores.photo'
    ],
    
    
    function ($request,$id){
        return new Response(200, Painel\Professor::getPhotoProfessor($request,$id));
    }
    ]);

//ROTA de Captura Foto do professores
$obRouter->post('/professores/photo/{id}',[
    
    'middlewares' => [
        'require-user-login',
        'can:professores.photo'
    ],
    
    
    function ($request){
        return new Response(200, Painel\Professor::setPhotoProfessor($request));
    }
    ]);

