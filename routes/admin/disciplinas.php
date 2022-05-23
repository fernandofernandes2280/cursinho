<?php

use \App\Http\Response;
use \App\Controller\Admin;


//ROTA de Listage de Disciplinas
$obRouter->get('/admin/disciplinas',[
		
    'middlewares' => [
        'require-admin-login'
    ],
		function ($request){
			return new Response(200, Admin\Disciplina::getDisciplina($request));
		}
		]);


//ROTA de Cadastro de um Novo de Disciplina
$obRouter->get('/admin/disciplinas/new',[
		
    'middlewares' => [
        'require-admin-login'
    ],
		
		function ($request){
		    return new Response(200, Admin\Disciplina::getDisciplinaNew($request));
		}
		]);

//ROTA de Cadastro de um Novo de Disciplina (POST)
$obRouter->post('/admin/disciplinas/new',[
    'middlewares' => [
        'require-admin-login'
    ],
		function ($request){
		    return new Response(200, Admin\Disciplina::setDisciplinaNew($request));
		}
		]);

//ROTA de Edição de um de Disciplina
$obRouter->get('/admin/disciplinas/{id}/edit',[
    'middlewares' => [
        'require-admin-login'
    ],
		
		function ($request,$id){
		    return new Response(200, Admin\Disciplina::getDisciplinaEdit($request,$id));
		}
		]);

//ROTA de Edição de um de Disciplina (POST)
$obRouter->post('/admin/disciplinas/{id}/edit',[
    'middlewares' => [
        'require-admin-login'
    ],
		
		
		function ($request,$id){
		    return new Response(200, Admin\Disciplina::setDisciplinaEdit($request,$id));
		}
		]);

//ROTA de Exclusão de um de Disciplina
$obRouter->get('/admin/disciplinas/{id}/delete',[
    'middlewares' => [
        'require-admin-login'
    ],
		
		
		function ($request,$id){
		    return new Response(200, Admin\Disciplina::getDisciplinaDelete($request,$id));
		}
		]);
//ROTA de Exclusão de um de Disciplina (POST)
$obRouter->post('/admin/disciplinas/{id}/delete',[
    'middlewares' => [
        'require-admin-login'
    ],
		
		function ($request,$id){
		    return new Response(200, Admin\Disciplina::setDisciplinaDelete($request,$id));
		}
		]);

