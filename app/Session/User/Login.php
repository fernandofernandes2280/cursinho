<?php

namespace App\Session\User;

use App\Auth\Permission;
use App\Model\Entity\User as EntityUser;

class Login{

	private static $currentUser = null;

	private static function init(){
		if(session_status() != PHP_SESSION_ACTIVE ){
			session_start();
		}
	}

	private static function isAdminType($tipo){
		return $tipo == 'Admin';
	}

	public static function hasDefaultPermissionForType($tipo, $permission){
		return Permission::roleAllows($tipo, $permission);
	}

	public static function isPermissionBlockedForType($tipo, $permission){
		return Permission::roleDenies($tipo, $permission);
	}

	public static function getPermissionValueForUser($obUser, $permission){
		return Permission::isAllowed($obUser, $permission) ? 1 : 0;
	}

	private static function getUsuarioArray($obUser){
		return [
			'id' => $obUser->id,
			'nome' => $obUser->nome,
			'email' => $obUser->email,
			'tipo' => $obUser->tipo,
			'cpf' => $obUser->cpf ?? '',
			'foto' => $obUser->foto,
		];
	}

	private static function getCurrentUser(){
		self::init();

		if(self::$currentUser instanceof EntityUser){
			return self::$currentUser;
		}

		$id = $_SESSION['usuario']['id'] ?? null;
		if($id === null){
			return null;
		}

		$obUser = EntityUser::getUserById($id);
		if($obUser instanceof EntityUser){
			self::$currentUser = $obUser;
			return self::$currentUser;
		}

		return null;
	}

	public static function login($obUser){
		self::init();

		$usuario = self::getUsuarioArray($obUser);
		$_SESSION['usuario'] = $usuario;
		self::$currentUser = $obUser instanceof EntityUser ? $obUser : null;

		return true;
	}

	public static function isLogged() {
		self::init();

		return isset($_SESSION['usuario']['id']);
	}

	public static function logout(){
		self::init();

		unset($_SESSION['usuario']);
		self::$currentUser = null;

		return true;
	}

	public static function isAdmin(){
		self::init();

		$obUser = self::getCurrentUser();

		return $obUser instanceof EntityUser && self::isAdminType($obUser->tipo ?? '');
	}

	public static function can($permission){
		self::init();

		$obUser = self::getCurrentUser();

		return $obUser instanceof EntityUser && Permission::isAllowed($obUser, $permission);
	}
}
