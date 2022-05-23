<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;
use \App\Utils\View;

class DisciplinaProfessor{
	
    //id do professor
    public $idProfessor;
    
    //id da disciplina
    public $idDisciplina;
		
    
    //Método responsavel por retornar um disciplinaProfessor com base no seu Id
    public static function getDisciplinaProfessorById($id){
        return self::getDisciplinasProfessor('id = '.$id)->fetchObject(self::class);
        
    }
    
	//Método responsavel por retornar Disciplinas do Professor
	public static function getDisciplinasProfessor($where = null, $order = null, $limit = null, $fields = '*') {
	    return (new Database('disciplinasProfessor'))->select($where,$order,$limit,$fields);
	}
	//Método responsavel por cadastrar um disciplina do Professor
	public function cadastrar(){
	    
	    //Insere no banco de dados
	    $this->id = (new Database('disciplinasProfessor'))->insert([
	        'idProfessor'=>$this->idProfessor,
	        'idDisciplina' => $this->idDisciplina
	    ]);
	    //Sucesso
	    return true;
	}
	
	//Método responsavel por excluir
	public function excluir(){
	    return (new Database('disciplinasProfessor'))->delete('id = '.$this->id);
	    
	    //Sucesso
	    return true;
	}
	

	
}