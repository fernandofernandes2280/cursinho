<?php

//NÃO ESTÁ SENDO UTILIZADA

use \App\Http\Response;
use \App\Controller\Pages;
use \App\Controller\Painel;

//ROTA HOME
$obRouter->get('',[
		
		function (){
			return new Response(200, Painel\Home::getHome());
		}
		]);

//ROTA SOBRE
$obRouter->get('/sobre',[
		function (){
			return new Response(200, Pages\About::getAbout());
		}
		]);


//ROTA Dinâmica
$obRouter->get('/pagina/{idPagina}',[
		function ($idPagina){
			return new Response(200, 'Página '.$idPagina);
		}
		]);




