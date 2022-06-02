<?php

use \App\Http\Response;
use \App\Controller\Pages;
use \App\Controller\Admin;


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

//ROTA GET DE CARTEIRA DE ALUNO
$obRouter->get('/aluno/carteira',[
   
    
    function ($request,$id){
        return new Response(200, Admin\Aluno::getCarteiraAluno($request,$id));
    }
    ]);

//ROTA GET DE CARTEIRA DE ALUNO
$obRouter->post('/aluno/carteira',[
    
    
    
    function ($request,$id){
        return new Response(200, Admin\Aluno::setCarteiraAluno($request,$id));
    }
    ]);
