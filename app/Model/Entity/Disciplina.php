<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;
use \App\Utils\View;

class Disciplina extends Generica {
	
		
	//Método responsavel por cadastrar um disciplina no banco de dados
	public function cadastrar(){
		
		//Insere no banco de dados
		$this->id = (new Database('disciplinas'))->insert([
				'nome'=>$this->nome,
		]);
		//Sucesso
		return true;
	}
	
	//Método responsavel por retornar um disciplina com base no seu Id
	public static function getDisciplinaById($id){
	    return self::getDisciplinas('id = '.$id)->fetchObject(self::class);
		
	}
	
	//Método responsavel por retornar Disciplinas
	public static function getDisciplinas($where = null, $order = null, $limit = null, $fields = '*') {
		return (new Database('disciplinas'))->select($where,$order,$limit,$fields);
	}
	
	//Método responsavel por listar os disciplinas no select option, selecionando o do paciente
	public static function getSelectDisciplinas($id){
		$resultados = '';
		$results =  self::getDisciplinas(null,'nome asc',null);
		//verifica se o id não é nulo e obtém o Procedencia do banco de dados
		if (!is_null($id)) {
			$selected = '';
			while ($ob = $results -> fetchObject(self::class)) {
				
				//seleciona o Procedencia do paciente
				$ob->id == $id ? $selected = 'selected' : $selected = '';
				//View de Procedencia
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
				$ob->nome == 'Não Informado' ? $selected = 'selected' : $selected = '';
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
	
	//Método responsavel por atualizar os dados no banco
	public function atualizar(){
	    return (new Database('disciplinas'))->update('id = '.$this->id,[
	        'nome' 		=> $this->nome,
	    ]);
	    
	    
	}
	
	//Método responsavel por excluir
	public function excluir(){
	    return (new Database('disciplinas'))->delete('id = '.$this->id);
	    
	    //Sucesso
	    return true;
	}
	
	
	
}