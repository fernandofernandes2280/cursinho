<?php

namespace App\Http\Middleware;

use \App\Http\Response;
use \App\Session\User\Login as SessionUserLogin;

class RequireUserLogin{

	private const PERMISSION_ROUTES = [
		'/^\/alunos\/[^\/]+\/delete$/' => 'excluirAluno',
		'/^\/professores\/[^\/]+\/delete$/' => 'excluirProfessor',
		'/^\/disciplinas\/[^\/]+\/delete$/' => 'excluirDisciplina',
		'/^\/users\/[^\/]+\/delete$/' => 'excluirUsuario',
		'/^\/users\/new$/' => 'btnNovoUsuario',
		'/^\/alunos(?:\/|$)/' => 'menuAlunos',
		'/^\/professores(?:\/|$)/' => 'menuProfessores',
		'/^\/aulas(?:\/|$)/' => 'menuAulas',
		'/^\/agendas$/' => 'menuAulas',
		'/^\/frequencias(?:\/|$)/' => 'menuFrequencias',
		'/^\/presencas(?:\/|$)/' => 'menuPresenca',
		'/^\/disciplinas(?:\/|$)/' => 'menuDisciplinas',
		'/^\/escolaridades(?:\/|$)/' => 'menuDisciplinas',
		'/^\/inativar(?:\/|$)/' => 'menuAlunos',
	];

	public function handle($request, $next){
		if(!SessionUserLogin::isLogged()){
			$request->getRouter()->redirect('/login');
		}

		$permission = $this->getRequiredPermission($request->getUri());
		if($permission !== null && !SessionUserLogin::can($permission)){
			return new Response(403, 'Você não tem permissão para acessar esta página. Contate o administrador.');
		}

		return $next($request);
	}

	private function getRequiredPermission($uri){
		$uri = rtrim($uri, '/') ?: '/';

		foreach(self::PERMISSION_ROUTES as $pattern => $permission){
			if(preg_match($pattern, $uri)){
				return $permission;
			}
		}

		return null;
	}
}
