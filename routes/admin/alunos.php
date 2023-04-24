<?php

use \App\Http\Response;
use \App\Controller\Admin;
use \App\Controller\Pages;


//ROTA HOME
$obRouter->get('',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    function ($request){
        return new Response(200, Admin\Aluno::getAlunos($request));
    }
    ]);


//Rota de listagem de alunos
$obRouter->get('/admin/alunos',[

    'middlewares' => [
        'require-admin-login'
    ],
    
    function ($request){
        return new Response(200, Admin\Aluno::getAlunos($request));
    }
    ]);

//Rota de listagem de alunos
$obRouter->post('/admin/alunos',[

 'middlewares' => [
    'require-admin-login'
 ],
 
    
    function ($request){
        return new Response(200, Admin\Aluno::getAlunos($request));
    }
    ]);



//Rota get para Relatórios em PDF
$obRouter->get('/admin/alunos/relatorios',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    function ($request){
        return new Response(200, Admin\Aluno::getPdfAluno($request));
    }
    ]);


//ROTA de Captura Foto do Aluno
$obRouter->get('/admin/alunos/photo/{id}',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    function ($request,$id){
        return new Response(200, Admin\Aluno::getPhotoAluno($request,$id));
    }
    ]);

//ROTA de Captura Foto do Aluno
$obRouter->post('/admin/alunos/photo/{id}',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    function ($request){
        return new Response(200, Admin\Aluno::setPhotoAluno($request));
    }
    ]);



//ROTA de Edição de um de Aluno
$obRouter->get('/admin/alunos/{id}/edit',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    function ($request,$id){
        return new Response(200, Admin\Aluno::getEditAluno($request,$id));
    }
    ]);

//ROTA de Edição de um de Paciente (POST)
$obRouter->post('/admin/alunos/{id}/edit',[
   
    'middlewares' => [
        'require-admin-login'
    ],
    
    function ($request,$id){
        return new Response(200, Admin\Aluno::setEditAluno($request,$id));
    }
    ]);


//Rota GET para Novo aluno
$obRouter->get('/admin/alunos/new',[

 'middlewares' => [
    'require-admin-login'
 ],

    
    function ($request){
        return new Response(200, Admin\Aluno::getNewAluno($request));
    }
    ]);

//Rota POST para Novo aluno
$obRouter->post('/admin/alunos/new',[
    
 'middlewares' => [
    'require-admin-login'
 ],
    
    function ($request){
        return new Response(200, Admin\Aluno::setNewAluno($request));
    }
    ]);


//Rota GET para excluir Aluno
$obRouter->get('/admin/alunos/{id}/delete',[

    'middlewares' => [
        'require-admin-login'
    ],
    
    function ($request,$id){
        return new Response(200, Admin\Aluno::getDeleteAluno($request,$id));
    }
    ]);

//Rota POST para excluir Aluno
$obRouter->post('/admin/alunos/{id}/delete',[

    'middlewares' => [
        'require-admin-login'
    ],
    
    function ($request,$id){
        return new Response(200, Admin\Aluno::setDeleteAluno($request,$id));
    }
    ]);

//ROTA GET DE CARTEIRA DE ALUNO
$obRouter->get('/admin/alunos/{id}/carteira',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    function ($request,$id){
        return new Response(200, Admin\Aluno::getCarteiraAluno($request,$id));
    }
    ]);

//ROTA GET DE CARTEIRA DE ALUNO
$obRouter->post('/admin/alunos/{id}/carteira',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    function ($request,$id){
        return new Response(200, Admin\Aluno::setCarteiraAluno($request,$id));
    }
    ]);
