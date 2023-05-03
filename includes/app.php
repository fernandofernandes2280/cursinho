<?php
require __DIR__.'/../vendor/autoload.php';



use \App\Utils\View;
use \WilliamCosta\DotEnv\Environment;
use \WilliamCosta\DatabaseManager\Database;
use \App\Http\Middleware\Queue as MiddlewareQueue;
use \App\Utils\Funcoes;
//Carrega variáveis de ambiente
Environment::load(__DIR__.'/../');

//DEfine as configurações de Banco de Dados
Database::config(
		getenv('DB_HOST'),
		getenv('DB_NAME'),
		getenv('DB_USER'),
		getenv('DB_PASS'),
		getenv('DB_PORT')
		
		);

//Define a constante de URL do projeto
define('URL',getenv('URL'));

//recebe os valores das permissões do usuário para serem usadas no Menu
$permissao = Funcoes::getPermissoes();
$permissaoMenuAlunos = $permissao['menuAlunos'];
define('permissaoMenuAlunos', $permissaoMenuAlunos);
$permissaoMenuProfessores = $permissao['menuProfessores'];
define('permissaoMenuProfessores', $permissaoMenuProfessores);
$permissaoMenuAulas = $permissao['menuAulas'];
define('permissaoMenuAulas', $permissaoMenuAulas);
$permissaoMenuFrequencias = $permissao['menuFrequencias'];
define('permissaoMenuFrequencias', $permissaoMenuFrequencias);


//Define o valor padrão das variáveis
View::init([
		'URL' => URL
]);

//Define o mapeamento de Middleware
MiddlewareQueue::setMap([
		'maintenance' => \App\Http\Middleware\Maintenance::class,
		'require-admin-logout' => \App\Http\Middleware\RequireAdminLogout::class,
		'require-admin-login' => \App\Http\Middleware\RequireAdminLogin::class,
		'require-visitor-logout' => \App\Http\Middleware\RequireVisitorLogout::class,
		'require-visitor-login' => \App\Http\Middleware\RequireVisitorLogin::class,
    'require-operador-logout' => \App\Http\Middleware\RequireOperadorLogout::class,
    'require-operador-login' => \App\Http\Middleware\RequireOperadorLogin::class,
    
      	'api' => \App\Http\Middleware\Api::class
]);

//Define o mapeamento de Middleware Padrões(Executados em todas as rotas)
MiddlewareQueue::setDefault([
		'maintenance'
]);





