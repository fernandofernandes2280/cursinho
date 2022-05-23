<?php

use \App\Http\Response;
use \App\Controller\Admin;
use \App\File;


//Rota de listagem de professores
$obRouter->get('/admin/professores',[

    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request){
        return new Response(200, Admin\Professor::getProfessores($request));
    }
    ]);

//Rota de listagem de professores
$obRouter->post('/admin/professores',[

    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request){
        return new Response(200, Admin\Professor::getProfessores($request));
    }
    ]);


//ROTA de Edição de um de Professor
$obRouter->get('/admin/professores/{id}/edit',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Admin\Professor::getEditProfessor($request,$id));
    }
    ]);

//ROTA de Edição de um de Paciente (POST)
$obRouter->post('/admin/professores/{id}/edit',[
   
    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Admin\Professor::setEditProfessor($request,$id));
    }
    ]);


//Rota GET para Novo Professor
$obRouter->get('/admin/professores/new',[

    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request){
        return new Response(200, Admin\Professor::getNewProfessor($request));
    }
    ]);

//Rota POST para Novo Professor
$obRouter->post('/admin/professores/new',[

    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request){
        return new Response(200, Admin\Professor::setNewProfessor($request));
    }
    ]);


//Rota GET para excluir Professor
$obRouter->get('/admin/professores/{id}/delete',[

    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Admin\Professor::getDeleteProfessor($request,$id));
    }
    ]);

//Rota POST para excluir Professor
$obRouter->post('/admin/professores/{id}/delete',[

    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Admin\Professor::setDeleteProfessor($request,$id));
    }
    ]);

//ROTA de Captura Foto do professores
$obRouter->get('/admin/professores/photo/{id}',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Admin\Professor::getPhotoProfessor($request,$id));
    }
    ]);

//ROTA de Captura Foto do professores
$obRouter->post('/admin/professores/photo/{id}',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request){
        return new Response(200, Admin\Professor::setPhotoProfessor($request));
    }
    ]);



