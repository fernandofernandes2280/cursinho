<?php

use \App\Http\Response;
use \App\Controller\Painel;


//ROTA de Listage de Usuários
$obRouter->get('/logs',[
		'middlewares' => [
				'require-user-login'
		],
		
		
		function ($request){
		    //apenas administrador pode excluir paciente
		    if($_SESSION['usuario']['tipo'] == 'Admin')
		        return new Response(200, Painel\Logs::getLogs($request));
		          else
		        return new Response(200, 'Você não tem permissão. Contate o Administrador! <a href="javascript:history.back()">Voltar</a>');
		}
		]);

