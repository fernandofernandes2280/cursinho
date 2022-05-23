<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;
use App\Utils\View;

class StatusAula extends Generica {
		
	//MÉTODO RESPONSÁVEL POR CADASTRAR UM STATUS DA AULA
	public function cadastrar(){
		
		//Insere paciente no banco de dados
		$this->id = (new Database('statusAula'))->insert([
				'nome'=>$this->nome,
		]);
		//Sucesso
		return true;
	}
	
	//METODO RESPONSÁVEL POR BUSCAR UM STATUS COM BASE NO SEU ID
	public static function getStatusAulaById($id){
	   
		return self::getStatusAula('id = '.$id)->fetchObject(self::class);
		
	}
	
	//MÉTODO RESPONSÁVEL POR RETORNAS STATUS DA AULA
	public static function getStatusAula($where = null, $order = null, $limit = null, $fields = '*') {
		return (new Database('statusAula'))->select($where,$order,$limit,$fields);
	}
	
	//MÉTODO RESPONSÁVEL POR LISTAR OS STATUS NO SELECT OPTION
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