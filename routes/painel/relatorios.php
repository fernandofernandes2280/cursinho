<?php

use \App\Http\Response;
use \App\Controller\Painel;


//Rota get para Relatórios em PDF
$obRouter->get('/alunos/relatorios',[
    
    'middlewares' => [
        'require-user-login',
        'can:relatorios.view'
    ],
    
    function ($request){
        return new Response(200, Painel\Relatorio::getPdfAluno($request));
    }
    ]);
