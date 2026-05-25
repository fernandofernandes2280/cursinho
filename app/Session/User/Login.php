<?php

namespace App\Session\User;

class Login{

	private const OPERADOR_DEFAULT_PERMISSIONS = [
		'menuAlunos',
		'menuProfessores',
		'menuAulas',
		'menuFrequencias',
		'menuPresenca',
		'menuDisciplinas',
	];

	private const OPERADOR_BLOCKED_PERMISSIONS = [
		'menuUsuarios',
		'btnNovoUsuario',
		'excluirUsuario',
	];

	private static function init(){
		if(session_status() != PHP_SESSION_ACTIVE ){
			session_start();
		}
	}

	private static function isAdminType($tipo){
		return $tipo == 'Admin';
	}

	private static function isOperadorType($tipo){
		return $tipo == 'Operador';
	}

	public static function hasDefaultPermissionForType($tipo, $permission){
		return self::isOperadorType($tipo) && in_array($permission, self::OPERADOR_DEFAULT_PERMISSIONS, true);
	}

	public static function isPermissionBlockedForType($tipo, $permission){
		return self::isOperadorType($tipo) && in_array($permission, self::OPERADOR_BLOCKED_PERMISSIONS, true);
	}

	public static function getPermissionValueForUser($obUser, $permission){
		$tipo = $obUser->tipo ?? '';

		if(self::isPermissionBlockedForType($tipo, $permission)){
			return 0;
		}

		if(self::isAdminType($tipo) || self::hasDefaultPermissionForType($tipo, $permission)){
			return 1;
		}

		return (int)($obUser->{$permission} ?? 0);
	}

	private static function getUsuarioArray($obUser){
		return [
			'id' => $obUser->id,
			'nome' => $obUser->nome,
			'email' => $obUser->email,
			'tipo' => $obUser->tipo,
			'cpf' => $obUser->cpf ?? '',
			'foto' => $obUser->foto,
			'excluirAluno' => self::getPermissionValueForUser($obUser, 'excluirAluno'),
			'excluirProfessor' => self::getPermissionValueForUser($obUser, 'excluirProfessor'),
			'excluirDisciplina' => self::getPermissionValueForUser($obUser, 'excluirDisciplina'),
			'excluirUsuario' => self::getPermissionValueForUser($obUser, 'excluirUsuario'),
			'menuAlunos' => self::getPermissionValueForUser($obUser, 'menuAlunos'),
			'menuProfessores' => self::getPermissionValueForUser($obUser, 'menuProfessores'),
			'menuAulas' => self::getPermissionValueForUser($obUser, 'menuAulas'),
			'menuFrequencias' => self::getPermissionValueForUser($obUser, 'menuFrequencias'),
			'btnNovoUsuario' => self::getPermissionValueForUser($obUser, 'btnNovoUsuario'),
			'menuPresenca' => self::getPermissionValueForUser($obUser, 'menuPresenca'),
			'menuDisciplinas' => self::getPermissionValueForUser($obUser, 'menuDisciplinas'),
		];
	}

	public static function login($obUser){
		self::init();

		$usuario = self::getUsuarioArray($obUser);
		$_SESSION['usuario'] = $usuario;

		return true;
	}

	public static function isLogged() {
		self::init();

		return isset($_SESSION['usuario']['id']);
	}

	public static function logout(){
		self::init();

		unset($_SESSION['usuario']);

		return true;
	}

	public static function isAdmin(){
		self::init();

		return self::isAdminType($_SESSION['usuario']['tipo'] ?? '');
	}

	public static function can($permission){
		self::init();

		$tipo = $_SESSION['usuario']['tipo'] ?? '';

		if(self::isPermissionBlockedForType($tipo, $permission)){
			return false;
		}

		return self::isAdmin() || self::hasDefaultPermissionForType($tipo, $permission) || (int)($_SESSION['usuario'][$permission] ?? 0) == 1;
	}
}
