<?php

use \App\Http\Response;
use \App\Controller\Operador;



//Rota de listagem de frequencias
$obRouter->get('/operador/frequencias',[

    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request){
        return new Response(200, Operador\Frequencia::getfrequencias($request));
    }
    ]);


//ROTA de Edição de uma Frequencia
$obRouter->get('/operador/frequencias/{id}/edit',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request,$id,$idAluno){
        return new Response(200, Operador\Frequencia::getFrequenciaEdit($request,$id,$idAluno));
    }
    ]);


//ROTA de Edição da Frequencia Individual do aluno
$obRouter->get('/operador/frequencias/{id}/edit/individual',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Operador\Frequencia::getFrequenciaEditIndividual($request,$id));
    }
    ]);


//ROTA de Pesuisa de um de Aluno para Frequencia
$obRouter->get('/operador/frequencias/{idAula}/edit/pesqAluno',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request,$idAula){
        return new Response(200, Operador\Frequencia::getFrequenciaEditPesquisa($request,$idAula));
    }
    ]);



//ROTA de Seleção de aluno para frequência
$obRouter->get('/operador/frequencias/{id}/edit/individual/{idAluno}',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request,$id,$idAluno){
        return new Response(200, Operador\Frequencia::getFrequenciaEditIndividualSelect($request,$id,$idAluno));
    }
    ]);

//ROTA de Confirmação da presença do aluno
$obRouter->post('/operador/frequencias/{id}/edit/individual/{idAluno}',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request,$id,$idAluno){
        return new Response(200, Operador\Frequencia::getFrequenciaEditIndividualSelectPresenca($request,$id,$idAluno));
    }
    ]);


//ROTA FREQUENCIA GERAL DO ALUNO
$obRouter->post('/operador/frequencias/{id}/edit',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    function ($request){
        return new Response(200, Operador\Frequencia::getFrequenciaGeral($request));
    }
    ]);

//ROTA DE FREQUENCIA GERAL PELO QRCODE NO CELULAR USANDO A CÂMERA TRASEIRA
$obRouter->get('/operador/frequencias/{id}/edit/mobile',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request,$id){
        return new Response(200, Operador\Frequencia::getFrequenciaGeralMobile($request,$id));
    }
    ]);

//ROTA de REATIVAÇÃO DO ALUNO NA FREQUÊNCIA
$obRouter->get('/operador/frequencias/{id}/reactive/{idMatricula}',[
    
    'middlewares' => [
        'require-operador-login'
    ],
    
    
    function ($request,$id,$idMatricula){
        return new Response(200, Operador\Frequencia::setFrequenciaReactiveAluno($request,$id,$idMatricula));
    }
    ]);



