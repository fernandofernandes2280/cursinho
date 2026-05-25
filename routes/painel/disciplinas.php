<?php

use \App\Http\Response;
use \App\Controller\Painel;


//ROTA de Listage de Disciplinas
$obRouter->get('/disciplinas',[
		
    'middlewares' => [
        'require-user-login'
    ],
		function ($request){
			return new Response(200, Painel\Disciplina::getDisciplina($request));
		}
		]);


//ROTA de Cadastro de um Novo de Disciplina
$obRouter->get('/disciplinas/new',[
		
    'middlewares' => [
        'require-user-login'
    ],
		
		function ($request){
		    return new Response(200, Painel\Disciplina::getDisciplinaNew($request));
		}
		]);

//ROTA de Cadastro de um Novo de Disciplina (POST)
$obRouter->post('/disciplinas/new',[
    'middlewares' => [
        'require-user-login'
    ],
		function ($request){
		    return new Response(200, Painel\Disciplina::setDisciplinaNew($request));
		}
		]);

//ROTA de Edição de um de Disciplina
$obRouter->get('/disciplinas/{id}/edit',[
    'middlewares' => [
        'require-user-login'
    ],
		
		function ($request,$id){
		    return new Response(200, Painel\Disciplina::getDisciplinaEdit($request,$id));
		}
		]);

//ROTA de Edição de um de Disciplina (POST)
$obRouter->post('/disciplinas/{id}/edit',[
    'middlewares' => [
        'require-user-login'
    ],
		
		
		function ($request,$id){
		    return new Response(200, Painel\Disciplina::setDisciplinaEdit($request,$id));
		}
		]);

//ROTA de Exclusão de um de Disciplina
$obRouter->get('/disciplinas/{id}/delete',[
    'middlewares' => [
        'require-user-login'
    ],
		
		
		function ($request,$id){
		    return new Response(200, Painel\Disciplina::getDisciplinaDelete($request,$id));
		}
		]);
//ROTA de Exclusão de um de Disciplina (POST)
$obRouter->post('/disciplinas/{id}/delete',[
    'middlewares' => [
        'require-user-login'
    ],
		
		function ($request,$id){
		    return new Response(200, Painel\Disciplina::setDisciplinaDelete($request,$id));
		}
		]);

