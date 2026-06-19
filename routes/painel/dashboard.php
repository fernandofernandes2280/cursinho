<?php

use \App\Http\Response;
use App\Controller\Painel;



//ROTA GET DE DASHBOARD DO ADMIN
$obRouter->get('/dashboard',[
		'middlewares' => [
				'require-user-login',
				'can:dashboard.view'
		],
		
		
		function ($request){
			return new Response(200, Painel\Dashboard::getDashboard($request));
		    
		}
		]);

//ROTA GET DOS DADOS ATUALIZADOS DO DASHBOARD
$obRouter->get('/dashboard/live',[
		'middlewares' => [
				'require-user-login',
				'can:dashboard.view'
		],

		function ($request){
			return new Response(200, Painel\Dashboard::getDashboardLive($request), 'application/json');
		}
		]);
		
