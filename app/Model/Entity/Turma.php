<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;
use App\Utils\View;

class Turma extends Generica {
		
	//Método responsavel por cadastrar um bairro no banco de dados
	public function cadastrar(){
		
		//Insere paciente no banco de dados
		$this->id = (new Database('turmas'))->insert([
				'nome'=>$this->nome,
		]);
		//Sucesso
		return true;
	}
	
	//Método responsavel por retornar uma turma com base no seu Id
	public static function getTurmaById($id){
	   
		return self::getTurmas('id = '.$id)->fetchObject(self::class);
		
	}
	
	//Método responsavel por retornar Turmas
	public static function getTurmas($where = null, $order = null, $limit = null, $fields = '*') {
		return (new Database('turmas'))->select($where,$order,$limit,$fields);
	}
	
	//Método responsavel por listar As Turmas no select option, selecionando o do Aluno
	public static function getSelectTurmas($id){
	    $resultados = '';
	    $results =  self::getTurmas(null,'nome asc',null);
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