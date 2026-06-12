<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;
use \App\Utils\View;

class DisciplinaProfessor{

    public $id;

    public $nome;
	
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

	public static function getDisciplinasPorProfessor($idProfessor){
	    $idProfessor = (int)$idProfessor;
	    if($idProfessor <= 0) return [];

	    $query = 'SELECT DP.idDisciplina, D.nome
	              FROM disciplinasProfessor AS DP
	              INNER JOIN disciplinas AS D ON D.id = DP.idDisciplina
	              WHERE DP.idProfessor = ?
	              ORDER BY D.nome ASC';

	    $results = (new Database())->execute($query, [$idProfessor]);
	    $disciplinas = [];

	    while($obDisciplina = $results->fetchObject(self::class)){
	        $disciplinas[] = [
	            'idDisciplina' => (int)$obDisciplina->idDisciplina,
	            'nome' => $obDisciplina->nome
	        ];
	    }

	    return $disciplinas;
	}

	public static function cadastrarDisciplinaRapida($idProfessor, $nome){
	    $idProfessor = (int)$idProfessor;
	    $nome = preg_replace('/\s+/', ' ', trim((string)$nome));

	    if($idProfessor <= 0){
	        throw new \InvalidArgumentException('Selecione o professor antes de cadastrar a disciplina.');
	    }

	    if($nome === ''){
	        throw new \InvalidArgumentException('Informe o nome da disciplina.');
	    }

	    $database = new Database('disciplinas');
	    $database->beginTransaction();

	    try{
	        $obDisciplina = $database
	            ->execute('SELECT id, nome FROM disciplinas WHERE LOWER(nome) = LOWER(?) LIMIT 1', [$nome])
	            ->fetchObject(self::class);

	        $criada = false;

	        if($obDisciplina){
	            $idDisciplina = (int)$obDisciplina->id;
	            $nomeDisciplina = $obDisciplina->nome;
	        }else{
	            $idDisciplina = (int)$database->insert(['nome' => $nome]);
	            $nomeDisciplina = $nome;
	            $criada = true;
	        }

	        $vinculo = $database
	            ->execute(
	                'SELECT id FROM disciplinasProfessor WHERE idProfessor = ? AND idDisciplina = ? LIMIT 1',
	                [$idProfessor, $idDisciplina]
	            )
	            ->fetchColumn();

	        $vinculada = false;

	        if(!$vinculo){
	            $database->execute(
	                'INSERT INTO disciplinasProfessor (idProfessor, idDisciplina) VALUES (?, ?)',
	                [$idProfessor, $idDisciplina]
	            );
	            $vinculada = true;
	            $vinculo = $database
	                ->execute(
	                    'SELECT id FROM disciplinasProfessor WHERE idProfessor = ? AND idDisciplina = ? LIMIT 1',
	                    [$idProfessor, $idDisciplina]
	                )
	                ->fetchColumn();
	        }

	        $database->commit();

	        return [
	            'idVinculo' => (int)$vinculo,
	            'idDisciplina' => $idDisciplina,
	            'nome' => $nomeDisciplina,
	            'criada' => $criada,
	            'vinculada' => $vinculada
	        ];
	    }catch(\Throwable $e){
	        $database->rollBack();
	        throw $e;
	    }
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
	    $disciplinas = self::getDisciplinasPorProfessor($id);

	    if (!$id || empty($disciplinas)) return $resultados;

	    foreach ($disciplinas as $disciplina) {
	        $selected = (int)$disciplina['idDisciplina'] === (int)$idDisciplina ? 'selected' : '';
	        $resultados .= View::render('painel/modules/selectOption/itemSelect',[
	            'id' => $disciplina['idDisciplina'],
	            'nome' => $disciplina['nome'],
	            'selecionado' => $selected
	        ]);
	    }

	    return $resultados;
	}
	
	
}
