<?php

use \App\Http\Response;
use \App\Controller\Painel;


//Rota de listagem de frequencias
$obRouter->get('/presencas',[

    'middlewares' => [
        'require-user-login'
    ],
    
    
    //function ($request){
      //  return new Response(200, Painel\Presenca::getfrequencias($request));
    //}
    
    
    function ($request){
        return new Response(200, Painel\Presenca::getPresenca($request));
    }
    
    
    ]);




