<?php

use \App\Http\Response;
use \App\Controller\Painel;


//Rota de listagem de frequencias
$obRouter->get('/frequencias',[

    'middlewares' => [
        'require-user-login',
        'can:frequencias.view'
    ],
    
    
    function ($request){
        return new Response(200, Painel\Frequencia::getfrequencias($request));
    }
    ]);


//ROTA de Edição de uma Frequencia
$obRouter->get('/frequencias/{id}/edit',[
    
    'middlewares' => [
        'require-user-login',
        'can:frequencias.update'
    ],
    
    
    function ($request,$id,$idAluno){
        return new Response(200, Painel\Frequencia::getFrequenciaEdit($request,$id,$idAluno));
    }
    ]);


//ROTA de Edição da Frequencia Individual do aluno
$obRouter->get('/frequencias/{id}/edit/individual',[
    
    'middlewares' => [
        'require-user-login',
        'can:frequencias.update'
    ],
    
    
    function ($request,$id){
        return new Response(200, Painel\Frequencia::getFrequenciaEditIndividual($request,$id));
    }
    ]);


//ROTA de Pesuisa de um de Aluno para Frequencia
$obRouter->get('/frequencias/{idAula}/edit/pesqAluno',[
    
    'middlewares' => [
        'require-user-login',
        'can:frequencias.update'
    ],
    
    
    function ($request,$idAula){
        return new Response(200, Painel\Frequencia::getFrequenciaEditPesquisa($request,$idAula));
    }
    ]);



//ROTA de Seleção de aluno para frequência
$obRouter->get('/frequencias/{id}/edit/individual/{idAluno}',[
    
    'middlewares' => [
        'require-user-login',
        'can:frequencias.update'
    ],
    
    
    function ($request,$id,$idAluno){
        return new Response(200, Painel\Frequencia::getFrequenciaEditIndividualSelect($request,$id,$idAluno));
    }
    ]);

//ROTA de Confirmação da presença do aluno
$obRouter->post('/frequencias/{id}/edit/individual/{idAluno}',[
    
    'middlewares' => [
        'require-user-login',
        'can:frequencias.confirm'
    ],
    
    
    function ($request,$id,$idAluno){
        return new Response(200, Painel\Frequencia::getFrequenciaEditIndividualSelectPresenca($request,$id,$idAluno));
    }
    ]);


//ROTA DE FREQUENCIA GERAL PELO QRCODE NO DESKTOP
$obRouter->post('/frequencias/{id}/edit',[
    
    'middlewares' => [
        'require-user-login',
        'can:frequencias.qrcode'
    ],
    
    
    function ($request){
        return new Response(200, Painel\Frequencia::getFrequenciaGeral($request));
    }
    ]);


//ROTA DE FREQUENCIA GERAL PELO QRCODE NO CELULAR USANDO A CÂMERA TRASEIRA
$obRouter->get('/frequencias/{id}/edit/mobile',[
    
    'middlewares' => [
        'require-user-login',
        'can:frequencias.qrcode'
    ],
    
    
    function ($request,$id){
        return new Response(200, Painel\Frequencia::getFrequenciaGeralMobile($request,$id));
    }
    ]);


//ROTA de REATIVAÇÃO DO ALUNO NA FREQUÊNCIA DESKTOP
$obRouter->get('/frequencias/{id}/reactive/{idMatricula}',[
    
    'middlewares' => [
        'require-user-login',
        'can:frequencias.confirm'
    ],
    
    
    function ($request,$id,$idMatricula){
        return new Response(200, Painel\Frequencia::setFrequenciaReactiveAluno($request,$id,$idMatricula));
    }
    ]);

//ROTA de REATIVAÇÃO DO ALUNO NA FREQUÊNCIA MOBILE
$obRouter->get('/frequencias/{id}/reactiveMobile/{idMatricula}',[
    
    'middlewares' => [
        'require-user-login',
        'can:frequencias.confirm'
    ],
    
    
    function ($request,$id,$idMatricula){
        return new Response(200, Painel\Frequencia::setFrequenciaReactiveMobileAluno($request,$id,$idMatricula));
    }
    ]);
