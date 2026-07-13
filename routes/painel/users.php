<?php

use \App\Http\Response;
use \App\Controller\Painel;


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
        'require-user-login',
        'can:usuarios.create'
    ],
		
		function ($request,$id){
			return new Response(200, Painel\User::getNewUser($request,$id));
		}
		]);

//ROTA de Cadastro de um Novo de Usuário (POST)
$obRouter->post('/users/new',[
    
    'middlewares' => [
        'require-user-login',
        'can:usuarios.create'
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

//ROTA de Validação da Senha Atual do Usuário
$obRouter->post('/users/{id}/verify-password',[
    'middlewares' => [
        'require-user-login'
    ],

		function ($request,$id){
			return new Response(200, Painel\User::verifyCurrentPassword($request,$id), 'application/json');
		}
		]);

//ROTA de Exclusão de um de Usuário
$obRouter->get('/users/{id}/delete',[
		'middlewares' => [
				'require-user-login',
				'can:usuarios.delete'
		],
		
		
		function ($request,$id){
			return new Response(200, Painel\User::getDeleteUser($request,$id));
		}
		]);
//ROTA de Exclusão de um de Usuário (POST)
$obRouter->post('/users/{id}/delete',[
		'middlewares' => [
				'require-user-login',
				'can:usuarios.delete'
		],
		
		
		function ($request,$id){
			$response = Painel\User::setDeleteUser($request,$id);

			if(is_array($response)){
				return new Response(200, $response, 'application/json');
			}

			return new Response(200, $response);
		}
		]);

//ROTA get para alterar senha
$obRouter->get('/trocarSenha',[
		'middlewares' => [
				'require-user-login'
		],
		function ($request){
			$id = (int)($_SESSION['usuario']['id'] ?? 0);
			$request->getRouter()->redirect($id > 0 ? '/users/'.$id.'/edit' : '/users');
		}
		]);


$obRouter->post('/trocarSenha',[
		'middlewares' => [
				'require-user-login'
		],
		function ($request){
			$id = (int)($_SESSION['usuario']['id'] ?? 0);
			$request->getRouter()->redirect($id > 0 ? '/users/'.$id.'/edit' : '/users');
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
