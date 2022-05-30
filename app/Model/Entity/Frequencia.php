<?php

namespace App\Model\Entity;
date_default_timezone_set('America/Sao_Paulo');
use \WilliamCosta\DatabaseManager\Database;
use \App\Utils\View;

class Frequencia{
    
    public $id;
    public $idAula;
    public $idAluno;
    public $status;
    public $dataReg;
    public $autor;
    
	
		
	//Método responsavel por cadastrar um disciplina no banco de dados
	public function cadastrar(){
		
	    //define a data
	    $this->dataReg = date('Y-m-d H:i:s');
	    
		//Insere no banco de dados
		$this->id = (new Database('frequencia'))->insert([
				'idAula'=>$this->idAula,
    		    'idAluno'=>$this->idAluno,
    		    'dataReg'=>$this->dataReg,
		        'status'=>$this->status,
		        'autor'=>$_SESSION['usuario']['id'] //id  do usuario logado,
    		    
		]);
		//Sucesso
		return true;
	}
	
	//Método responsavel por atualizar os banco de dados com os dados da instancia atual de paciente
	public function atualizar(){
	    //define a data
	    $this->dataReg = date('Y-m-d H:i:s');
	    
	        //Atualiza frequencia no banco de dados
	    return (new Database('frequencia'))->update('id = '.$this->id,[
	        'status'=>$this->status,
	        'dataReg'=>$this->dataReg,
    		'autor'=>$this->autor,
	    ]);
	}
	
	//Método responsavel por retornar uma Frequencia com base no seu Id
	public static function getFrequenciaById($id){
	    return self::getFrequencias('id = '.$id)->fetchObject(self::class);
		
	}
	
	//Método responsavel por retornar Alunos com presença numa determinada frequencia
	public static function getFreqPresenca($idAula,$status){
	    return self::getFrequencias('idAula = '.$idAula.' AND status = " '.$status.' "',null,null,'count(status) as qtd')->fetchObject(self::class);
	    //Sucesso
	    return true;
	}
	
	//Método responsavel por retornar Disciplinas
	public static function getFrequencias($where = null, $order = null, $limit = null, $fields = '*') {
		return (new Database('frequencia'))->select($where,$order,$limit,$fields);
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
				$ob->nome == 'Não Informado' ? $selected = 'selected' : $selected = '';
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