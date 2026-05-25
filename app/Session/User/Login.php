<?php

namespace App\Session\User;

class Login{

	private static function init(){
		if(session_status() != PHP_SESSION_ACTIVE ){
			session_start();
		}
	}

	private static function getUsuarioArray($obUser){
		return [
			'id' => $obUser->id,
			'nome' => $obUser->nome,
			'email' => $obUser->email,
			'tipo' => $obUser->tipo,
			'cpf' => $obUser->cpf ?? '',
			'foto' => $obUser->foto,
			'excluirAluno' => $obUser->excluirAluno ?? 0,
			'excluirProfessor' => $obUser->excluirProfessor ?? 0,
			'excluirDisciplina' => $obUser->excluirDisciplina ?? 0,
			'excluirUsuario' => $obUser->excluirUsuario ?? 0,
			'menuAlunos' => $obUser->menuAlunos ?? 0,
			'menuProfessores' => $obUser->menuProfessores ?? 0,
			'menuAulas' => $obUser->menuAulas ?? 0,
			'menuFrequencias' => $obUser->menuFrequencias ?? 0,
			'btnNovoUsuario' => $obUser->btnNovoUsuario ?? 0,
			'menuPresenca' => $obUser->menuPresenca ?? 0,
			'menuDisciplinas' => $obUser->menuDisciplinas ?? 0,
		];
	}

	public static function login($obUser){
		self::init();

		$usuario = self::getUsuarioArray($obUser);
		$_SESSION['usuario'] = $usuario;

		// Compatibilidade temporaria enquanto rotas/controllers antigos ainda existem.
		$_SESSION['admin']['tipo'] = $usuario['tipo'];
		$_SESSION['admin']['usuario'] = $usuario;
		$_SESSION['operador']['usuario'] = $usuario;

		return true;
	}

	public static function isLogged() {
		self::init();

		return isset($_SESSION['usuario']['id']);
	}

	public static function logout(){
		self::init();

		unset($_SESSION['usuario']);
		unset($_SESSION['admin']);
		unset($_SESSION['operador']);

		return true;
	}

	public static function isAdmin(){
		self::init();

		return ($_SESSION['usuario']['tipo'] ?? '') == 'Admin';
	}

	public static function can($permission){
		self::init();

		return self::isAdmin() || (int)($_SESSION['usuario'][$permission] ?? 0) == 1;
	}
}
