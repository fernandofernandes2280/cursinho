<?php

use \App\Http\Response;
use \App\Controller\Painel;


//ROTA de Listage de escolaridades
$obRouter->get('/escolaridades',[
		'middlewares' => [
				'require-user-login',
				'can:disciplinas.view'
		],
		
		
		function ($request){
			return new Response(200, Painel\Escolaridade::getEscolaridades($request));
		}
		]);


//ROTA de Cadastro de um Novo de escolaridades
$obRouter->get('/escolaridades/new',[
		'middlewares' => [
				'require-user-login',
				'can:disciplinas.create'
		],
		
		
		function ($request){
			return new Response(200, Painel\Escolaridade::getNewEscolaridade($request));
		}
		]);

//ROTA de Cadastro de um Novo de escolaridades (POST)
$obRouter->post('/escolaridades/new',[
		'middlewares' => [
				'require-user-login',
				'can:disciplinas.create'
		],
		
		
		function ($request){
			return new Response(200, Painel\Escolaridade::setNewEscolaridade($request));
		}
		]);

//ROTA de Edição de um de escolaridades
$obRouter->get('/escolaridades/{id}/edit',[
		'middlewares' => [
				'require-user-login',
				'can:disciplinas.update'
		],
		
		
		function ($request,$id){
			return new Response(200, Painel\Escolaridade::getEditEscolaridade($request,$id));
		}
		]);

//ROTA de Edição de um de escolaridades (POST)
$obRouter->post('/escolaridades/{id}/edit',[
		'middlewares' => [
				'require-user-login',
				'can:disciplinas.update'
		],
		
		
		function ($request,$id){
			return new Response(200, Painel\Escolaridade::setEditEscolaridade($request,$id));
		}
		]);

//ROTA de Exclusão de um de escolaridades
$obRouter->get('/escolaridades/{id}/delete',[
		'middlewares' => [
				'require-user-login',
				'can:disciplinas.delete'
		],
		
		
		function ($request,$id){
			return new Response(200, Painel\Escolaridade::getDeleteEscolaridade($request,$id));
		}
		]);
//ROTA de Exclusão de um de escolaridades (POST)
$obRouter->post('/escolaridades/{id}/delete',[
		'middlewares' => [
				'require-user-login',
				'can:disciplinas.delete'
		],
		
		
		function ($request,$id){
			return new Response(200, Painel\Escolaridade::setDeleteEscolaridade($request,$id));
		}
		]);
