<?php

use \App\Http\Response;
use \App\Controller\Login;


//ROTAS DE LOGIN RAIZ

//ROTA Logout
$obRouter->get('/logout',[
		
		function ($request){
			return new Response(200, Login\Login::setLogout($request));
		
		}
		]);


//ROTA Get Recuperar Senha
$obRouter->get('/login/recuperarSenha',[
		'middlewares' => [
				'require-visitor-logout'
		],
		function ($request){
			
			return new Response(200, Login\Login::getRecuperarSenha($request));
		}
		]);


//ROTA Post Recuperar Senha
$obRouter->post('/login/recuperarSenha',[
		'middlewares' => [
				'require-visitor-logout'
		],
		function ($request){
			
			return new Response(200, Login\Login::setRecuperarSenha($request));
		}
		]);



//ROTAS DE LOGINDE VISITANTES

//ROTA Login
$obRouter->get('/visitor/login',[
		'middlewares' => [
				'require-visitor-logout'
		],
		function ($request){
			return new Response(200, Login\Login::getLogin($request));
		}
		]);



//ROTA Login POst
$obRouter->post('/visitor/login',[
		'middlewares' => [
				'require-visitor-logout'
		],
		function ($request){
			
			return new Response(200, Login\Login::setLogin($request));
		}
		]);

//ROTA Logout
$obRouter->get('/visitor/logout',[
		'middlewares' => [
				'require-visitor-login'
		],
		function ($request){
			return new Response(200, Login\Login::setLogout($request));
		}
		]);

///////////ROTAS DE LOGIN DA HOME///////////////////

//ROTA Login
$obRouter->get('/login',[
    'middlewares' => [
        'require-user-logout'
    ],

    function ($request){
        return new Response(200, Login\Login::getLogin($request));
    }
    ]);

//ROTA Login POst
$obRouter->post('/login',[
    'middlewares' => [
        'require-user-logout'
    ],
    
    function ($request){
        
        return new Response(200, Login\Login::setLogin($request));
    }
    ]);
