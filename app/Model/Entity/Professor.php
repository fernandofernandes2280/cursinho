<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;
use App\Utils\View;
use App\Utils\Funcoes;

class Professor extends Generica {
	
    //endereço (rua/avenida) do professor
    public $endereco;
    
    //bairro do professor
    public $bairro;
    
    //cidade do professor
    public $cidade;
    
    //cep do professor
    public $cep;
    
    //unidade federal do professor
    public $uf;
    
    //telefone do professor
    public $fone;
    
    //data de nascimento do professor
    public $dataNasc;
    
    //data de cadastro do professor
    public $dataCad;
    
    //função do professor
    public $funcao;
    
    //cpf do do professor
    public $cpf;
		
    //status 1-ativo 0-inativo
    public $status;
    
    //e-mail do professor
    public $email;
    
    //foto do professor
    public $foto;
    
    
    //Método responsavel por cadastrar um Professor no banco de dados
	public function cadastrar(){
	    $this->foto = 'profile.png';
	    //Insere Professor no banco de dados
		$this->id = (new Database('professores'))->insert([
				'nome'=>$this->nome,
    		    'endereco'=>$this->endereco,
    		    'bairro'=>$this->bairro,
    		    'cidade'=>$this->cidade,
    		    'uf'=>$this->uf,
    		    'cep'=>$this->cep,
    		    'fone'=>$this->fone,
    		    'dataNasc'=>$this->dataNasc,
    		    'status'=>$this->status,
		        'funcao'=>$this->funcao,
		        'cpf'=>$this->cpf,
                'email'=>$this->email,
		        'foto'=>$this->foto
		]);
		//Sucesso
		return true;
	}
	
	//Método responsavel por atualizar os dados no banco
	public function atualizar(){
	    return (new Database('professores'))->update('id = '.$this->id,[
	        'nome'=>$this->nome,
	        'endereco'=>$this->endereco,
	        'bairro'=>$this->bairro,
	        'cidade'=>$this->cidade,
	        'uf'=>$this->uf,
	        'cep'=>$this->cep,
	        'fone'=>$this->fone,
	        'dataNasc'=>$this->dataNasc,
	        'status'=>$this->status,
	        'funcao'=>$this->funcao,
	        'cpf'=>$this->cpf,
	        'email'=>$this->email,
	        'foto'=>$this->foto
	    ]);
	    
	    
	}
	
	
	//Método responsavel por retornar um Professor com base no seu Id
	public static function getProfessorById($id){
		return self::getprofessores('id = '.$id)->fetchObject(self::class);
		
	}
	
	//Método responsavel por retornar Professor
	public static function getProfessores($where = null, $order = null, $limit = null, $fields = '*') {
		return (new Database('professores'))->select($where,$order,$limit,$fields);
	}
	
	
	//Método responsavel por retornar um Professor com base em seu e-mail
	public static function getProfessorByCPF($cpf){
	    return self::getProfessores('cpf = "'.$cpf.'"')->fetchObject(self::class);
	    
	    //Sucesso
	    return true;
	}
	
	//Método responsavel por retornar um usuario com base em seu e-mail
	public static function getProfessorByEmail($email){
	    return self::getProfessores('email = "'.$email.'"')->fetchObject(self::class);
	    
	    //Sucesso
	    return true;
	}
	
	
	//Método responsavel por excluir um Professor do banco de dados
	public function excluir(){
	    //Exclui o Professor no Banco de Dados
	    return (new Database('professores'))->delete('id = '.$this->id);
	    //Sucesso
	    return true;
	}
	
    //Método responsavel por iniciar sessao com dados do form
	public static function getSessaoDados($obProfessor){
	    //inicia sessão
	    Funcoes::init();
	    
	    //Define a sessao do usuario
	    $_SESSION['professor']['novo'] = [
	        'nome' => $obProfessor['nome'],
	        'cep' => $obProfessor['cep'],
	        'endereco' => $obProfessor['endereco'],
	        'bairro' => $obProfessor['bairro'],
	        'cidade' => $obProfessor['cidade'],
	        'uf' => $obProfessor['uf'],
	        'funcao' => $obProfessor['funcao'],
	        'dataNasc' =>date('Y-m-d',strtotime($obProfessor['dataNasc'])),
	        'cpf' => $obProfessor['cpf'],
	        'fone' => $obProfessor['fone'],
	        'status' => $obProfessor['status'],
	        'email' => $obProfessor['email']
	        
	        
	    ];
	    
	    //Sucesso
	    return true;
	}
	
	//Método responsavel por Finalizar sessao 
	public static function getFinalizaSessaoDados(){
	    //Inicia a Sessão
	    Funcoes::init();
	    
	    //destri a sessao
	    unset($_SESSION['professor']['novo']);
	    
	    //sucesso
	    return true;
	}
	
	
	//Método responsavel por listar os Professores no select option,
	public static function getSelectProfessores($id){
	    $resultados = '';
	    $results =  self::getProfessores(null,'nome asc',null);
	    //verifica se o id não é nulo e obtém o Procedencia do banco de dados
	    if (!is_null($id)) {
	        $selected = '';
	        while ($ob = $results -> fetchObject(self::class)) {
	            
	            //seleciona a Turma do aluno
	            $ob->id == $id ? $selected = 'selected' : $selected = '';
	            //View de Turmas
	            $resultados .= View::render('admin/modules/alunos/itemSelect',[
	                'id' => $ob ->id,
	                'nome' => $ob->nome,
	                'selecionado' => $selected
	            ]);
	        }
	        //retorna
	        return $resultados;
	    }else{ //se for nulo, lista todos e seleciona um em branco
	        while ($ob = $results -> fetchObject(self::class)) {
	            $selected = '';
	            $resultados .= View::render('admin/modules/alunos/itemSelect',[
	                'id' => $ob ->id,
	                'nome' => $ob->nome,
	                'selecionado' => $selected
	            ]);
	        }
	        //retorna a listagem
	        return $resultados;
	    }
	}
	
	
}