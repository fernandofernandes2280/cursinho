<?php

use \App\Http\Response;
use \App\Controller\Pages;


//ROTA Admin
$obRouter->get('',[
		'middlewares' => [
				//'require-admin-login'
		],
		
		
		function ($request){
			return new Response(200, Pages\Home::getHome($request));
		    
		}
		]);
		
