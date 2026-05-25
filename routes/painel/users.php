<?php

use \App\Http\Response;
use \App\Controller\Painel;
use \App\Controller\Visitor;


//ROTA de Listage de Usuários
$obRouter->get('/users',[
    
    'middlewares' => [
        'require-user-login'
    ],
		
		function ($request){
			return new Response(200, Painel\User::getUsers($request));
		}
		]);


//ROTA de Cadastro de um Novo de Usuário
$obRouter->get('/users/new',[
		
    
    'middlewares' => [
        'require-user-login'
    ],
		
		function ($request,$id){
			return new Response(200, Painel\User::getNewUser($request,$id));
		}
		]);

//ROTA de Cadastro de um Novo de Usuário (POST)
$obRouter->post('/users/new',[
    
    'middlewares' => [
        'require-user-login'
    ],
		
		
		function ($request,$id){
			return new Response(200, Painel\User::setNewUser($request,$id));
		}
		]);

//ROTA de Edição de um de Usuário
$obRouter->get('/users/{id}/edit',[
		
    
    'middlewares' => [
        'require-user-login'
    ],
		
		function ($request,$id){
			return new Response(200, Painel\User::getEditUser($request,$id));
		}
		]);

//ROTA de Edição de um de Usuário (POST)
$obRouter->post('/users/{id}/edit',[
		
    
    'middlewares' => [
        'require-user-login'
    ],
		
		function ($request,$id){
			return new Response(200, Painel\User::setEditUser($request,$id));
		}
		]);

//ROTA de Exclusão de um de Usuário
$obRouter->get('/users/{id}/delete',[
		'middlewares' => [
				'require-user-login'
		],
		
		
		function ($request,$id){
			return new Response(200, Painel\User::getDeleteUser($request,$id));
		}
		]);
//ROTA de Exclusão de um de Usuário (POST)
$obRouter->post('/users/{id}/delete',[
		'middlewares' => [
				'require-user-login'
		],
		
		
		function ($request,$id){
			return new Response(200, Painel\User::setDeleteUser($request,$id));
		}
		]);

//ROTA get para alterar senha
$obRouter->get('/trocarSenha',[
		'middlewares' => [
				'require-user-login'
		],
		function ($request){
			return new Response(200, Painel\Senha::getTrocarSenha($request));
			//return new Response(200, Visitor\Home::getHome($request));
		}
		]);


$obRouter->post('/trocarSenha',[
		'middlewares' => [
				'require-user-login'
		],
		function ($request){
			return new Response(200, Painel\Senha::setTrocarSenha($request));
			//return new Response(200, Visitor\Home::getHome($request));
		}
		]);

//ROTA de Captura Foto do Usuário
$obRouter->get('/users/photo/{id}',[
    
    'middlewares' => [
        'require-user-login'
    ],
    
    function ($request,$id){
        return new Response(200, Painel\User::getPhoto($request,$id));
    }
    ]);

//ROTA de Captura Foto do Usuário
$obRouter->post('/users/photo/{id}',[
    
    'middlewares' => [
        'require-user-login'
    ],
    
    function ($request){
        return new Response(200, Painel\User::setPhoto($request));
    }
    ]);

