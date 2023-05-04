<?php

use \App\Http\Response;
use \App\Controller\Operador;


//Rota de listagem de professores
$obRouter->get('/operador/professores',[

    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request){
        return new Response(200, Operador\Professor::getProfessores($request));
    }
    ]);

//Rota de listagem de professores
$obRouter->post('/operador/professores',[

    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request){
        return new Response(200, Operador\Professor::getProfessores($request));
    }
    ]);


//ROTA de Edição de um de Professor
$obRouter->get('/operador/professores/{id}/edit',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Operador\Professor::getEditProfessor($request,$id));
    }
    ]);

//ROTA de Edição de um de Paciente (POST)
$obRouter->post('/operador/professores/{id}/edit',[
   
    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Operador\Professor::setEditProfessor($request,$id));
    }
    ]);


//Rota GET para Novo Professor
$obRouter->get('/operador/professores/new',[

    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request){
        return new Response(200, Operador\Professor::getNewProfessor($request));
    }
    ]);

//Rota POST para Novo Professor
$obRouter->post('/operador/professores/new',[

    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request){
        return new Response(200, Operador\Professor::setNewProfessor($request));
    }
    ]);


//Rota GET para excluir Professor
$obRouter->get('/operador/professores/{id}/delete',[

    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Operador\Professor::getDeleteProfessor($request,$id));
    }
    ]);

//Rota POST para excluir Professor
$obRouter->post('/operador/professores/{id}/delete',[

    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Operador\Professor::setDeleteProfessor($request,$id));
    }
    ]);

//ROTA de Captura Foto do professores
$obRouter->get('/operador/professores/photo/{id}',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Operador\Professor::getPhotoProfessor($request,$id));
    }
    ]);

//ROTA de Captura Foto do professores
$obRouter->post('/operador/professores/photo/{id}',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request){
        return new Response(200, Operador\Professor::setPhotoProfessor($request));
    }
    ]);



