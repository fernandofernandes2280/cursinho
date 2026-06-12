<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;
use \App\Utils\View;

class Frequencia{
    
    public $id;
    public $idAula;
    public $idAluno;
    public $status;
    public $dataReg;
    public $autor;
    public $fotoAuditoria;
    public $dataAuditoria;
    
	
		
	//Método responsavel por cadastrar um disciplina no banco de dados
	public function cadastrar(){
	  
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

	public static function cadastrarFaltasDaAula($idAula, $turma, $autor, $database = null){
	    $database = $database ?: new Database('frequencia');
	    $dataReg = date('Y-m-d H:i:s');

	    $query = 'INSERT IGNORE INTO frequencia (idAula, idAluno, dataReg, status, autor)
	              SELECT ?, A.id, ?, ?, ?
	              FROM alunos AS A
	              WHERE A.turma = ?
	                AND A.status = 1';

	    return $database->execute($query, [
	        (int)$idAula,
	        $dataReg,
	        'F',
	        (int)$autor,
	        (int)$turma
	    ])->rowCount();
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

	public function registrarPresenca($autor, $fotoAuditoria = null){
	    $this->status = 'P';
	    $this->autor = (int)$autor;
	    $this->dataReg = date('Y-m-d H:i:s');

	    $values = [
	        'status' => $this->status,
	        'dataReg' => $this->dataReg,
	        'autor' => $this->autor,
	    ];

	    if(strlen((string)$fotoAuditoria)){
	        $this->fotoAuditoria = $fotoAuditoria;
	        $this->dataAuditoria = $this->dataReg;
	        $values['fotoAuditoria'] = $this->fotoAuditoria;
	        $values['dataAuditoria'] = $this->dataAuditoria;
	    }

	    return (new Database('frequencia'))->update('id = '.$this->id, $values);
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
	
	
	//Método responsavel por retornar ALUNOS DA AULA
	public static function getFrequenciasSQL($where = null, $order = null, $limit = null, $fields = '*', $table = null) {
	    return (new Database($table))->select($where,$order,$limit,$fields);
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
				$resultados .= View::render('painel/modules/alunos/itemSelect',[
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
				$resultados .= View::render('painel/modules/alunos/itemSelect',[
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
