<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;
use App\Auth\Permission;
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
	
	//Permissao para excluir aluno
	public $excluirAluno;

	//Permissao para excluir professor
	public $excluirProfessor;
	
	//Permissao para excluir Disciplina
	public $excluirDisciplina;
	
	//Permissao para excluir Disciplina
	public $excluirUsuario;
	
	//Permissao para acessar Menu Aluno
	public $menuAlunos;
	
	//Permissao para acessar Menu Professor
	public $menuProfessores;
	
	//Permissao para acessar Menu Aulas
	public $menuAulas;
	
	//Permissao para acessar Menu Frequencias
	public $menuFrequencias;

	//Permissao para acessar Botão Novo Usuário
	public $btnNovoUsuario;

	//Permissao para acessar Menu Disciplinas
	public $menuDisciplinas;

	//Permissoes granulares em JSON
	public $permissoes;
	
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
				'cpf' 	=> $this->cpf,
		        'excluirAluno' 	=> $this->excluirAluno,
		       'excluirProfessor' 	=> $this->excluirProfessor,
		    'excluirDisciplina' 	=> $this->excluirDisciplina,
		    'excluirUsuario' 	=> $this->excluirUsuario,
		    'menuAlunos' 	=> $this->menuAlunos,
		    'menuProfessores' 	=> $this->menuProfessores,
		    'menuAulas' 	=> $this->menuAulas,
		    'menuFrequencias' 	=> $this->menuFrequencias,
			    'btnNovoUsuario' 	=> $this->btnNovoUsuario,
			    'menuDisciplinas' 	=> $this->menuDisciplinas,
			    'permissoes' 	=> $this->permissoes,
		    
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
				'cpf' 	=> $this->cpf,
		         'excluirAluno' 	=> $this->excluirAluno,
		       'excluirProfessor' 	=> $this->excluirProfessor,
		    'excluirDisciplina' 	=> $this->excluirDisciplina,
		    'excluirUsuario' 	=> $this->excluirUsuario,
		    'menuAlunos' 	=> $this->menuAlunos,
		    'menuProfessores' 	=> $this->menuProfessores,
		    'menuAulas' 	=> $this->menuAulas,
		    'menuFrequencias' 	=> $this->menuFrequencias,
			    'btnNovoUsuario' 	=> $this->btnNovoUsuario,
			    'menuDisciplinas' 	=> $this->menuDisciplinas,
			    'permissoes' 	=> $this->permissoes,
		    
		    
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
		$permissoes = $ob['permissoes'] ?? $ob['permissions'] ?? [];
		if(is_string($permissoes)){
			$permissoes = json_decode($permissoes, true) ?: [];
		}

	    return Funcoes::flashOldInput('usuario.novo', [
	        'nome' => $ob['nome'] ?? '',
	        'email' => $ob['email'] ?? '',
	        'cpf' => $ob['cpf'] ?? '',
	        'tipo' => $ob['tipo'] ?? '',
	        'excluirAluno' => $ob['excluirAluno'] ?? $ob['checkExcluirAluno'] ?? '0',
	        'excluirProfessor' => $ob['excluirProfessor'] ?? $ob['checkExcluirProfessor'] ?? '0',
	        'excluirDisciplina' => $ob['excluirDisciplina'] ?? $ob['checkDisciplina'] ?? '0',
	        'excluirUsuario' => $ob['excluirUsuario'] ?? $ob['checkExcluirUsuario'] ?? '0',
	        'menuAlunos' => $ob['menuAlunos'] ?? $ob['checkMenuAlunos'] ?? '0',
	        'menuProfessores' => $ob['menuProfessores'] ?? $ob['checkMenuProfessores'] ?? '0',
	        'menuAulas' => $ob['menuAulas'] ?? $ob['checkMenuAulas'] ?? '0',
	        'menuFrequencias' => $ob['menuFrequencias'] ?? $ob['checkMenuFrequencias'] ?? '0',
	        'btnNovoUsuario' => $ob['btnNovoUsuario'] ?? $ob['checkBtnNovoUsuario'] ?? '0',
		        'menuDisciplinas' => $ob['menuDisciplinas'] ?? $ob['checkMenuDisciplinas'] ?? '0',
	        'permissoes' => Permission::encodePermissions($permissoes),
	    ]);
	}
	
	//Método responsavel por Finalizar sessao
	public static function getFinalizaSessaoDados(){
	    return Funcoes::clearOldInput('usuario.novo');
	}
	
}
