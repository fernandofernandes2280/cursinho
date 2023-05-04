<?php

use \App\Http\Response;
use \App\Controller\Operador;


//ROTA de Listage de Disciplinas
$obRouter->get('/operador/disciplinas',[
		
    'middlewares' => [
        'require-operador-login'
    ],
		function ($request){
		    return new Response(200, Operador\Disciplina::getDisciplina($request));
		}
		]);


//ROTA de Cadastro de um Novo de Disciplina
$obRouter->get('/operador/disciplinas/new',[
		
    'middlewares' => [
        'require-operador-login'
    ],
		
		function ($request){
		    return new Response(200, Operador\Disciplina::getDisciplinaNew($request));
		}
		]);

//ROTA de Cadastro de um Novo de Disciplina (POST)
$obRouter->post('/operador/disciplinas/new',[
    'middlewares' => [
        'require-operador-login'
    ],
		function ($request){
		    return new Response(200, Operador\Disciplina::setDisciplinaNew($request));
		}
		]);

//ROTA de Edição de um de Disciplina
$obRouter->get('/operador/disciplinas/{id}/edit',[
    'middlewares' => [
        'require-operador-login'
    ],
		
		function ($request,$id){
		    return new Response(200, Operador\Disciplina::getDisciplinaEdit($request,$id));
		}
		]);

//ROTA de Edição de um de Disciplina (POST)
$obRouter->post('/operador/disciplinas/{id}/edit',[
    'middlewares' => [
        'require-operador-login'
    ],
		
		
		function ($request,$id){
		    return new Response(200, Operador\Disciplina::setDisciplinaEdit($request,$id));
		}
		]);

//ROTA de Exclusão de um de Disciplina
$obRouter->get('/operador/disciplinas/{id}/delete',[
    'middlewares' => [
        'require-operador-login'
    ],
		
		
		function ($request,$id){
		    return new Response(200, Operador\Disciplina::getDisciplinaDelete($request,$id));
		}
		]);
//ROTA de Exclusão de um de Disciplina (POST)
$obRouter->post('/operador/disciplinas/{id}/delete',[
    'middlewares' => [
        'require-admin-login'
    ],
		
		function ($request,$id){
		    return new Response(200, Operador\Disciplina::setDisciplinaDelete($request,$id));
		}
		]);

