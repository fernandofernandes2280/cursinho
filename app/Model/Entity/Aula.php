<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;
use App\Controller\Admin\Logs;
use App\Utils\View;

class Aula {
	
	
	public $id;
	public $data;
	public $turma;
	public $professor1;
	public $disciplina1;
	public $professor2;
	public $disciplina2;
	public $obs;
	public $status;
	public $diaSemana;
	public $dataReg;
	public $autor;
		
	//Método responsavel por cadastrar um bairro no banco de dados
	public function cadastrar(){
		
	    //define a data
	    	$this->dataReg = date('Y-m-d H:i:s');
	    	
		//Insere paciente no banco de dados
		$this->id = (new Database('aulas'))->insert([
				'data'=>date(''.$this->data.' H:i:s'),
				'turma'=>$this->turma,
				'professor1'=>$this->professor1,
    		    'professor2'=>$this->professor2,
    		    'disciplina1'=>$this->disciplina1,
    		    'disciplina2'=>$this->disciplina2,
    		    'obs'=>$this->obs,
		        'status'=>$this->status,
		        'diaSemana'=>$this->diaSemana,
    		    'dataReg'=>$this->dataReg,
		        'autor'=>$_SESSION['usuario']['id'] //id  do usuario logado
		]);
		
		//Grava o Log do usuário
//		Logs::setNewLog('atendimentos',__FUNCTION__ ,'Profissional: '.Profissional::getProfissionalById($this->idProfissional)->nome.' Procedimento: '.Procedimento::getProcedimentoById($this->idProcedimento)->nome.' Status: '.$this->status);
		
		//Sucesso
		return true;
	}
	
	
	//Método responsavel por atualizar os banco de dados com os dados da instancia atual de paciente
	public function atualizar(){
//		$this->data = date('Y-m-d H:i:s');

	
		//Grava o Log do usuário
//		Logs::setNewLog('atendimentos',__FUNCTION__ ,'Profissional: '.Profissional::getProfissionalById($this->idProfissional)->nome.' Procedimento: '.Procedimento::getProcedimentoById($this->idProcedimento)->nome.' Status: '.$this->status);
		
		//Atualiza paciente no banco de dados
		return (new Database('aulas'))->update('id = '.$this->id,[
				'data'=>$this->data,
		    'turma'=>$this->turma,
		    'professor1'=>$this->professor1,
		    'professor2'=>$this->professor2,
		    'disciplina1'=>$this->disciplina1,
		    'disciplina2'=>$this->disciplina2,
		    'obs'=>$this->obs,
		    'status'=>$this->status,
		    'diaSemana'=>$this->diaSemana,
		    'autor'=>$this->autor,
		    
		]);
	}
	
	
	//Método responsavel por excluir um Atendimento do banco de dados
	public function excluir(){
		
		//Grava o Log do usuário
//		Logs::setNewLog('atendimentos',__FUNCTION__ ,'Profissional: '.Profissional::getProfissionalById($this->idProfissional)->nome.' Procedimento: '.Procedimento::getProcedimentoById($this->idProcedimento)->nome.' Status: '.$this->status);
		
			//Exclui o depoimento no Banco de Dados
		return (new Database('aulas'))->delete('id = '.$this->id);
		//Sucesso
		return true;
	}
	
	
	//Método responsavel por retornar uma Aula já cadastrada
	public static function getAulaDuplicada($data,$turma){
	    return self::getAulas(' DATE(data) ="'.$data .'" and turma = '.$turma )->fetchObject(self::class);
	}
	
	
	//Método responsavel por retornar uma agenda com base no seu Id
	public static function getAulaById($id){
	    return self::getAulas('id = '.$id)->fetchObject(self::class);
	}
	
	//Método responsavel por retornar Agendas
	public static function getAulas($where = null, $order = null, $limit = null, $fields = '*') {
		return (new Database('aulas'))->select($where,$order,$limit,$fields);
	}
	
	//Método responsavel por retornar ALUNOS COM FALTAS PRA SEREM INATIVADOS
	public static function getAulasInativaAluno($where = null, $order = null, $limit = null, $fields = '*',$table) {
	    return (new Database($table))->select($where,$order,$limit,$fields);
	}
	
	
	//*****************STATUS DA AULA **********************************
	
	//Método responsavel por retornar Status da Agenda
	public static function getStatusAula($where = null, $order = null, $limit = null, $fields = '*') {
	    return (new Database('statusAula'))->select($where,$order,$limit,$fields);
	}
	
	//Método responsavel por retornar um Status com base no seu Id
	public static function getStatusAulaById($id){
	    return self::getStatusAula('id = '.$id)->fetchObject(self::class);
	}
	
	//Método responsavel por listar os Status da Aula no select option,
	public static function getSelectStatusAula($id){
	    $resultados = '';
	    $results =  self::getStatusAula(null,'nome asc',null);
	    //verifica se o id não é nulo e obtém o Procedencia do banco de dados
	    if (!is_null($id)) {
	        $selected = '';
	        while ($ob = $results -> fetchObject(self::class)) {
	            
	            //seleciona a Turma do aluno
	            $ob->id == $id ? $selected = 'selected' : $selected = '';
	            //View de Turmas
	            $resultados .= View::render('admin/modules/selectOption/itemSelect',[
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
	            $resultados .= View::render('admin/modules/selectOption/itemSelect',[
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