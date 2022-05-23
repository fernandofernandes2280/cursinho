<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;
use \App\Utils\View;
use \App\Model\Entity\Disciplina as EntityDisciplina;
use \App\Model\Entity\Aula as EntityAula;

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
	
	//Método responsavel por listar os disciplinas do PRofessor no select option
	public static function getSelectDisciplinasProfessor($id, $idAula,$idDisciplina){
	    $resultados = '';
	    
	    $obAula = EntityAula::getAulaById($idAula);
	    
	    $discProf = self::getDisciplinasProfessor('idProfessor = '.$id);
        	   
	    while ($ob = $discProf -> fetchObject(self::class)) {
	        //ARMAZENA OS IDS DAS DISCIPLINAS DO PROFESSOR NUM ARRAY
	        $idDisc[] = $ob->idDisciplina; 
	    }
	    //SELECIONA AS DISCIPLINAS DO PROFESSOR
	    $results =  EntityDisciplina::getDisciplinas('id IN ('.implode(",", $idDisc).')','nome asc',null);
	    

	    if (($id)) {
	        $selected = '';
	        while ($obDisciplina = $results -> fetchObject(self::class)) {
	            
	            //SELECIONA A DISCIPLINA DA AULA
	            $obDisciplina->id == $idDisciplina ? $selected = 'selected' : $selected = '';

	            //RENDEREIZA A VIEW DE ITENS DO SELECT
	            $resultados .= View::render('admin/modules/selectOption/itemSelect',[
	                'id' => $obDisciplina ->id,
	                'nome' => $obDisciplina->nome,
	                'selecionado' => $selected
	            ]);
	        }
	        //RETORNA OS RESULTADOS
	        return $resultados;
	    }
	    
	    
	    }
	
	
}