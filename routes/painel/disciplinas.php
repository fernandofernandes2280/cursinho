<?php

use \App\Http\Response;
use \App\Controller\Painel;


//ROTA de Listage de Disciplinas
$obRouter->get('/disciplinas',[
		
    'middlewares' => [
        'require-user-login',
        'can:disciplinas.view'
    ],
		function ($request){
			return new Response(200, Painel\Disciplina::getDisciplina($request));
		}
		]);


//ROTA de Cadastro de um Novo de Disciplina
$obRouter->get('/disciplinas/new',[
		
    'middlewares' => [
        'require-user-login',
        'can:disciplinas.create'
    ],
		
		function ($request){
		    return new Response(200, Painel\Disciplina::getDisciplinaNew($request));
		}
		]);

//ROTA de Cadastro de um Novo de Disciplina (POST)
$obRouter->post('/disciplinas/new',[
    'middlewares' => [
        'require-user-login',
        'can:disciplinas.create'
    ],
		function ($request){
		    return new Response(200, Painel\Disciplina::setDisciplinaNew($request));
		}
		]);

//ROTA de Edição de um de Disciplina
$obRouter->get('/disciplinas/{id}/edit',[
    'middlewares' => [
        'require-user-login',
        'can:disciplinas.update'
    ],
		
		function ($request,$id){
		    return new Response(200, Painel\Disciplina::getDisciplinaEdit($request,$id));
		}
		]);

//ROTA de Edição de um de Disciplina (POST)
$obRouter->post('/disciplinas/{id}/edit',[
    'middlewares' => [
        'require-user-login',
        'can:disciplinas.update'
    ],
		
		
		function ($request,$id){
		    return new Response(200, Painel\Disciplina::setDisciplinaEdit($request,$id));
		}
		]);

//ROTA de Exclusão de um de Disciplina
$obRouter->get('/disciplinas/{id}/delete',[
    'middlewares' => [
        'require-user-login',
        'can:disciplinas.delete'
    ],
		
		
		function ($request,$id){
		    return new Response(200, Painel\Disciplina::getDisciplinaDelete($request,$id));
		}
		]);
//ROTA de Exclusão de um de Disciplina (POST)
$obRouter->post('/disciplinas/{id}/delete',[
    'middlewares' => [
        'require-user-login',
        'can:disciplinas.delete'
    ],
		
		function ($request,$id){
		    $response = Painel\Disciplina::setDisciplinaDelete($request,$id);

		    if(is_array($response)){
		        return new Response(200, $response, 'application/json');
		    }

		    return new Response(200, $response);
		}
		]);

//ROTA de post para cadastro rápido de disciplinas do professor
$obRouter->post('/ajax/professores/disciplinas/nova',[
		'middlewares' => [
				'require-user-login',
				'can:disciplinas.link-professor'
		],

		function ($request){
			return new Response(200, Painel\Ajax::setDisciplinaProfessor($request), 'application/json');
		}
		]);

//ROTA de busca das disciplinas vinculadas ao professor
$obRouter->post('/ajax/professores/disciplinas',[
		'middlewares' => [
				'require-user-login',
				'can:disciplinas.link-professor'
		],

		function ($request){
			return new Response(200, Painel\Ajax::getDisciplinasProfessor($request), 'application/json');
		}
		]);
