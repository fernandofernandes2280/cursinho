<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;
use App\Utils\Funcoes;

class User{
	
	//ID do usuário
	public $id;
	
	//nome do usuário
	public $nome;
	
	//email do usuario
	public $email;
	
	//senha do usuário
	public $senha;
	
	//tipo do usuário
	public $tipo;
	
	//foto do usuário
	public $foto;
	
	//CPF do usuário
	public $cpf;
	
	//Método responsavel por cadastrar o usuário no Banco de Dados
	public function cadastrar(){
        
	    //insere foto genérica no cadastro
	    $this->foto = 'profile.png';
	    
		//Insere usuário no Banco de Dados
		$this->id=(new Database('usuarios'))->insert([
				'nome' 		=> $this->nome,
				'email' 	=> $this->email,
				'tipo' 		=> $this->tipo,
				'senha' 	=> $this->senha,
				'foto' 	=> $this->foto,
				'cpf' 	=> $this->cpf
		]);
		
		//Sucesso
		return true;
	}
	
	//Método responsavel por atualizar os dados no banco
	public function atualizar(){
		return (new Database('usuarios'))->update('id = '.$this->id,[
				'nome' 		=> $this->nome,
				'email' 	=> $this->email,
				'tipo' 	=> $this->tipo,
				'senha' 	=> $this->senha,
				'foto' 	=> $this->foto,
				'cpf' 	=> $this->cpf
		]);
		
		
	}
	
	//Método responsavel por excluir usuário do banco
	public function excluir(){
		return (new Database('usuarios'))->delete('id = '.$this->id);
		
		//Sucesso
		return true;
	}
	
	//Método responsavel por retornar uma instancia com base no id
	public static  function getUserById($id){
		return self::getUsers('id = '.$id)->fetchObject(self::class);
		
	}
	
	
	//Método responsavel por retornar um usuario com base em seu e-mail
	public static function getUserByCPF($cpf){
		return self::getUsers('cpf = "'.$cpf.'"')->fetchObject(self::class);
		//Sucesso
		return true;
	}
	
	//Método responsavel por retornar um usuario com base em seu e-mail
	public static function getUserByEmail($email){
		return self::getUsers('email = "'.$email.'"')->fetchObject(self::class);
		
		//Sucesso
		return true;
	}
	
	//Método responsavel por retornar Usuários
	public static function getUsers($where = null, $order = null, $limit = null, $fields = '*') {
		return (new Database('usuarios'))->select($where,$order,$limit,$fields);
	}
	
	//Método responsavel por iniciar sessao com dados do form
	public static function getSessaoDados($ob){
	    //inicia sessão
	    Funcoes::init();
	    
	    //Define a sessao do usuario
	    $_SESSION['usuario']['novo'] = [
	        'nome' => $ob['nome'],
	        'email' => $ob['email'],
	        'senha' => $ob['senha'],
	        'cpf' => $ob['cpf'],
	        'tipo' => $ob['tipo'],
	    ];
	   
	    //Sucesso
	    return true;
	}
	
	//Método responsavel por Finalizar sessao
	public static function getFinalizaSessaoDados(){
	    //Inicia a Sessão
	    Funcoes::init();
	    
	    //destri a sessao
	    unset($_SESSION['usuario']['novo']);
	    
	    //sucesso
	    return true;
	}
	
}