<?php

use \App\Http\Response;
use \App\Controller\Admin;
use \App\File;

//ROTA HOME
$obRouter->get('',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    
    function ($request){
        return new Response(200, Admin\Aluno::getAlunos($request));
    }
    ]);


//Rota de listagem de alunos
$obRouter->get('/operador/alunos',[

    'middlewares' => [
        'require-operador-login'
    ],
    
    function ($request){
        return new Response(200, Admin\Aluno::getAlunos($request));
    }
    ]);

//Rota de listagem de alunos
$obRouter->post('/operador/alunos',[

 'middlewares' => [
    'require-operador-login'
 ],
 
    
    function ($request){
        return new Response(200, Admin\Aluno::getAlunos($request));
    }
    ]);


//ROTA de Captura Foto do Aluno
$obRouter->get('/operador/alunos/photo/{id}',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    
    function ($request,$id){
        return new Response(200, Admin\Aluno::getPhotoAluno($request,$id));
    }
    ]);

//ROTA de Captura Foto do Aluno
$obRouter->post('/operador/alunos/photo/{id}',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    
    function ($request){
        return new Response(200, Admin\Aluno::setPhotoAluno($request));
    }
    ]);



//ROTA de Edição de um de Aluno
$obRouter->get('/operador/alunos/{id}/edit',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    
    function ($request,$id){
        return new Response(200, Admin\Aluno::getEditAluno($request,$id));
    }
    ]);

//ROTA de Edição de um de Paciente (POST)
$obRouter->post('/operador/alunos/{id}/edit',[
   
    'middlewares' => [
        'require-operador-login'
    ],
    
    function ($request,$id){
        return new Response(200, Admin\Aluno::setEditAluno($request,$id));
    }
    ]);


//Rota GET para Novo aluno
$obRouter->get('/operador/alunos/new',[

 'middlewares' => [
    'require-operador-login'
 ],

    
    function ($request){
        return new Response(200, Admin\Aluno::getNewAluno($request));
    }
    ]);

//Rota POST para Novo aluno
$obRouter->post('/operador/alunos/new',[
    
 'middlewares' => [
    'require-operador-login'
 ],
    
    function ($request){
        return new Response(200, Admin\Aluno::setNewAluno($request));
    }
    ]);


//Rota GET para excluir Aluno
$obRouter->get('/operador/alunos/{id}/delete',[

    'middlewares' => [
        'require-operador-login'
    ],
    
    function ($request,$id){
        return new Response(200, Admin\Aluno::getDeleteAluno($request,$id));
    }
    ]);

//Rota POST para excluir Aluno
$obRouter->post('/operador/alunos/{id}/delete',[

    'middlewares' => [
        'require-operador-login'
    ],
    
    function ($request,$id){
        return new Response(200, Admin\Aluno::setDeleteAluno($request,$id));
    }
    ]);


