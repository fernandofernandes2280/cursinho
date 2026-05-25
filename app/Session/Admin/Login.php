<?php

namespace App\Session\Admin;

use \App\Session\User\Login as UserLogin;

class Login{

	//Método responsavel por criar o login do usuário
	public static function login($obUser){
		return UserLogin::login($obUser);
	}
	
	
	
	
	//Método responsavel por veririfcar se o ususario está logado
	public static function isLogged() {
		return UserLogin::isLogged();
	}
	
	//Método responsavel por executar logout do usuario
	public static function logout(){
		return UserLogin::logout();
	}
	
	
}
