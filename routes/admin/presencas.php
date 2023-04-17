<?php

use \App\Http\Response;
use \App\Controller\Admin;


//Rota de listagem de frequencias
$obRouter->get('/admin/presencas',[

    'middlewares' => [
        'require-admin-login'
    ],
    
    
    //function ($request){
      //  return new Response(200, Admin\Presenca::getfrequencias($request));
    //}
    
    
    function ($request){
        return new Response(200, Admin\Presenca::getPresenca($request));
    }
    
    
    ]);




