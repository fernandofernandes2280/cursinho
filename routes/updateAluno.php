<?php

use \App\Http\Response;
use \App\Controller\Pages;
use \App\Controller\Painel;

//ROTA DE PRÉ-CADASTRO DO ALUNO
$obRouter->get('/precadastro',[

    function ($request){

        return new Response(200, Pages\PreCadastroAluno::getPreCadastro($request));

    }
    ]);

//ROTA DE VERIFICAÇÃO/SALVAMENTO DO PRÉ-CADASTRO DO ALUNO
$obRouter->post('/precadastro',[

    function ($request){

        return new Response(200, Pages\PreCadastroAluno::setPreCadastro($request));

    }
    ]);


//ROTA PARA VERIFICAÇÃO DO CPF
$obRouter->get('/aluno',[
    
    function ($request){
        
        return new Response(200, Pages\UpdateAluno::getIndex($request));
        
    }
    ]);

//ROTA PARA VERIFICAÇÃO DO CPF
$obRouter->post('/aluno',[
    
    function ($request){
        
        return new Response(200, Pages\UpdateAluno::setIndex($request));
        
    }
    ]);

//ROTA PARA O ALUNO ATUALIZAR SEU CADASTRO
$obRouter->get('/aluno/update',[
    
    function ($request,$id){
        
        return new Response(200, Pages\UpdateAluno::getUpdate($request,$id));
        
    }
    ]);

//ROTA PARA O ALUNO ATUALIZAR SEU CADASTRO
$obRouter->post('/aluno/update',[
    
    function ($request){
        
        return new Response(200, Pages\UpdateAluno::setUpdate($request));
        
    }
    ]);

//ROTA GET PARA O ALUNO ATUALIZAR FOTO
$obRouter->get('/aluno/update/foto',[
    
    function ($request,$id){
        
        return new Response(200, Pages\UpdateAluno::getUpdateFoto($request,$id));
        
    }
    ]);

//ROTA POST PARA O ALUNO ATUALIZAR FOTO
$obRouter->post('/aluno/update/foto',[
    
    function ($request){
        
        return new Response(200, Pages\UpdateAluno::setUpdateFoto($request));
        
    }
    ]);


//ROTA GET DE CARTEIRA DE ALUNO
$obRouter->get('/aluno/carteira',[
   
    
    function ($request,$id){
        return new Response(200, Painel\Aluno::getCarteiraAluno($request,$id));
    }
    ]);

//ROTA GET DE CARTEIRA DE ALUNO
$obRouter->post('/aluno/carteira',[
    
    
    
    function ($request,$id){
        return new Response(200, Painel\Aluno::setCarteiraAluno($request,$id));
    }
    ]);
