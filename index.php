<?php
require __DIR__.'/includes/app.php';
use \App\Http\Router;

//Inicia o router
$obRouter = new Router(URL);

//Inclui as rotas do Painel
include __DIR__.'/routes/painel.php';

//Inclui as rotas de Atualizcao Cadastral do aluno
include __DIR__.'/routes/updateAluno.php';

//Inclui as da API
include __DIR__.'/routes/login.php';

//Imprime o response da Rota teste
$obRouter->run()
				->sendResponse();
