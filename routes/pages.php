<?php

//NÃO ESTÁ SENDO UTILIZADA

use \App\Http\Response;
use \App\Controller\Pages;
use \App\Controller\Admin;

//ROTA HOME
$obRouter->get('',[
		
		function (){
			return new Response(200, Admin::getHome());
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


//ROTA depoimentos
$obRouter->get('/depoimentos',[
		function ($request){
			return new Response(200, Pages\Testimony::getTestimonies($request));
		}
		]);

//ROTA depoimentos (Insert)
$obRouter->post('/depoimentos',[
		function ($request){
		
			return new Response(200, Pages\Testimony::insertTestimony($request));
		}
		]);

//ROTA Pacientes GET
$obRouter->get('/pacientes',[
		function ($request){
			return new Response(200, Pages\Paciente::getPacientes($request));
		}
		]);

//ROTA Pacientes (Insert) 
$obRouter->post('/pacientes',[
		function ($request){
			
			return new Response(200, Pages\Paciente::insertPaciente($request));
		}
		]);



