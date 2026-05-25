<?php

use \App\Http\Response;
use \App\Controller\Admin;


//Rota get para Relatórios em PDF
$obRouter->get('/admin/alunos/relatorios',[
    
    'middlewares' => [
        'require-user-login'
    ],
    
    function ($request){
        return new Response(200, Admin\Relatorio::getPdfAluno($request));
    }
    ]);

