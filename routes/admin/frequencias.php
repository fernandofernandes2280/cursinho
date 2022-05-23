<?php

use \App\Http\Response;
use \App\Controller\Admin;
use \App\File;


//Rota de listagem de frequencias
$obRouter->get('/admin/frequencias',[

    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request){
        return new Response(200, Admin\Frequencia::getfrequencias($request));
    }
    ]);


//ROTA de Edição de uma Frequencia
$obRouter->get('/admin/frequencias/{id}/edit',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request,$id,$idAluno){
        return new Response(200, Admin\Frequencia::getFrequenciaEdit($request,$id,$idAluno));
    }
    ]);


//ROTA de Edição da Frequencia Individual do aluno
$obRouter->get('/admin/frequencias/{id}/edit/individual',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Admin\Frequencia::getFrequenciaEditIndividual($request,$id));
    }
    ]);


//ROTA de Pesuisa de um de Aluno para Frequencia
$obRouter->get('/admin/frequencias/{idAula}/edit/pesqAluno',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request,$idAula){
        return new Response(200, Admin\Frequencia::getFrequenciaEditPesquisa($request,$idAula));
    }
    ]);



//ROTA de Seleção de aluno para frequência
$obRouter->get('/admin/frequencias/{id}/edit/individual/{idAluno}',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request,$id,$idAluno){
        return new Response(200, Admin\Frequencia::getFrequenciaEditIndividualSelect($request,$id,$idAluno));
    }
    ]);

//ROTA de Confirmação da presença do aluno
$obRouter->post('/admin/frequencias/{id}/edit/individual/{idAluno}',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request,$id,$idAluno){
        return new Response(200, Admin\Frequencia::getFrequenciaEditIndividualSelectPresenca($request,$id,$idAluno));
    }
    ]);


//ROTA de Confirmação da presença do aluno
$obRouter->post('/admin/frequencias/{id}/edit',[
    
    'middlewares' => [
        'require-admin-login'
    ],
    
    
    function ($request){
        return new Response(200, Admin\Frequencia::getFrequenciaGeral($request));
    }
    ]);




