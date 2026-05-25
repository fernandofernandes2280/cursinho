<?php

use \App\Http\Response;
use \App\Controller\Painel;
use \App\Controller\Pages;


//ROTA HOME
$obRouter->get('',[
    
    'middlewares' => [
        'require-user-login'
    ],
    
    function ($request){
        return new Response(200, Painel\Dashboard::getDashboard());
    }
    ]);


//Rota de listagem de alunos
$obRouter->get('/alunos',[

    'middlewares' => [
        'require-user-login'
    ],
    
    function ($request){
        return new Response(200, Painel\Aluno::getAlunos($request));
    }
    ]);

//Rota de listagem de alunos
$obRouter->post('/alunos',[

 'middlewares' => [
    'require-user-login'
 ],
 
    
    function ($request){
        return new Response(200, Painel\Aluno::getAlunos($request));
    }
    ]);




//ROTA de Captura Foto do Aluno
$obRouter->get('/alunos/photo/{id}',[
    
    'middlewares' => [
        'require-user-login'
    ],
    
    function ($request,$id){
        return new Response(200, Painel\Aluno::getPhotoAluno($request,$id));
    }
    ]);

//ROTA de Captura Foto do Aluno
$obRouter->post('/alunos/photo/{id}',[
    
    'middlewares' => [
        'require-user-login'
    ],
    
    function ($request){
        return new Response(200, Painel\Aluno::setPhotoAluno($request));
    }
    ]);



//ROTA de Edição de um de Aluno
$obRouter->get('/alunos/{id}/edit',[
    
    'middlewares' => [
        'require-user-login'
    ],
    
    function ($request,$id){
        return new Response(200, Painel\Aluno::getEditAluno($request,$id));
    }
    ]);

//ROTA de Edição de um de Paciente (POST)
$obRouter->post('/alunos/{id}/edit',[
   
    'middlewares' => [
        'require-user-login'
    ],
    
    function ($request,$id){
        return new Response(200, Painel\Aluno::setEditAluno($request,$id));
    }
    ]);


//Rota GET para Novo aluno
$obRouter->get('/alunos/new',[

 'middlewares' => [
    'require-user-login'
 ],

    
    function ($request){
        return new Response(200, Painel\Aluno::getNewAluno($request));
    }
    ]);

//Rota POST para Novo aluno
$obRouter->post('/alunos/new',[
    
 'middlewares' => [
    'require-user-login'
 ],
    
    function ($request){
        return new Response(200, Painel\Aluno::setNewAluno($request));
    }
    ]);


//Rota GET para excluir Aluno
$obRouter->get('/alunos/{id}/delete',[

    'middlewares' => [
        'require-user-login'
    ],
    
    function ($request,$id){
        return new Response(200, Painel\Aluno::getDeleteAluno($request,$id));
    }
    ]);

//Rota POST para excluir Aluno
$obRouter->post('/alunos/{id}/delete',[

    'middlewares' => [
        'require-user-login'
    ],
    
    function ($request,$id){
        return new Response(200, Painel\Aluno::setDeleteAluno($request,$id));
    }
    ]);

//ROTA GET DE CARTEIRA DE ALUNO
$obRouter->get('/alunos/{id}/carteira',[
    
    'middlewares' => [
        'require-user-login'
    ],
    
    function ($request,$id){
        return new Response(200, Painel\Aluno::getCarteiraAluno($request,$id));
    }
    ]);

//ROTA GET DE CARTEIRA DE ALUNO
$obRouter->post('/alunos/{id}/carteira',[
    
    'middlewares' => [
        'require-user-login'
    ],
    
    function ($request,$id){
        return new Response(200, Painel\Aluno::setCarteiraAluno($request,$id));
    }
    ]);
