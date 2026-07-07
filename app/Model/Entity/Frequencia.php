<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;
use \App\Utils\View;
use PDO;
use PDOException;

class Frequencia{
    private static $comparacaoFacialColumnsReady = null;

    public $id;
    public $idAula;
    public $idAluno;
    public $status;
    public $dataReg;
    public $autor;
    public $fotoAuditoria;
    public $dataAuditoria;
    public $comparacaoFacialResultado;
    public $comparacaoFacialPontuacao;
    public $comparacaoFacialDetalhes;
    public $comparacaoFacialData;
    
	
		
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

	public static function getCondicaoAlunoAtivoParaFrequencia($alias = 'A'){
	    $alias = preg_replace('/[^A-Za-z0-9_]/', '', (string)$alias);
	    $alias = $alias !== '' ? $alias : 'A';

	    return 'COALESCE('.$alias.'.status, 0) = 1
	            AND TRIM(COALESCE('.$alias.'.nome, "")) <> ""
	            AND TRIM(COALESCE('.$alias.'.matricula, "")) <> ""';
	}

	public static function cadastrarFaltasDaAula($idAula, $turma, $autor, $database = null){
	    $database = $database ?: new Database('frequencia');
	    $dataReg = date('Y-m-d H:i:s');

	    self::removerFaltasDeAlunosInativosDaAula($idAula, $turma, $database);

	    $query = 'INSERT IGNORE INTO frequencia (idAula, idAluno, dataReg, status, autor)
	              SELECT ?, A.id, ?, ?, ?
	              FROM alunos AS A
	              WHERE A.turma = ?
	                AND '.self::getCondicaoAlunoAtivoParaFrequencia('A');

	    return $database->execute($query, [
	        (int)$idAula,
	        $dataReg,
	        'F',
	        (int)$autor,
	        (int)$turma
	    ])->rowCount();
	}

	public static function removerFaltasDeAlunosInativosDaAula($idAula, $turma = null, $database = null){
	    $idAula = (int)$idAula;

	    if($idAula <= 0){
	        return 0;
	    }

	    $database = $database ?: new Database('frequencia');
	    $params = [$idAula];
	    $filtroTurma = '';

	    if($turma !== null && $turma !== ''){
	        $filtroTurma = ' AND A.turma = ?';
	        $params[] = (int)$turma;
	    }

	    return $database->execute(
	        'DELETE F
	           FROM frequencia AS F
	           INNER JOIN alunos AS A ON A.id = F.idAluno
	          WHERE F.idAula = ?
	            '.$filtroTurma.'
	            AND NOT ('.self::getCondicaoAlunoAtivoParaFrequencia('A').')
	            AND F.status <> "P"',
	        $params
	    )->rowCount();
	}

	public static function garantirVinculoAlunoAula($idAula, $idAluno, $autor, $status = 'F', $database = null){
	    $idAula = (int)$idAula;
	    $idAluno = (int)$idAluno;

	    if($idAula <= 0 || $idAluno <= 0){
	        return null;
	    }

	    $where = 'idAula = '.$idAula.' AND idAluno = '.$idAluno;
	    $obFrequencia = (new Database('frequencia AS F INNER JOIN alunos AS A ON A.id = F.idAluno'))
	        ->select(
	            'F.idAula = '.$idAula.' AND F.idAluno = '.$idAluno.' AND '.self::getCondicaoAlunoAtivoParaFrequencia('A'),
	            null,
	            '1',
	            'F.*'
	        )
	        ->fetchObject(self::class);

	    if($obFrequencia instanceof self){
	        return $obFrequencia;
	    }

	    $database = $database ?: new Database('frequencia');
	    $database->execute(
	        'INSERT IGNORE INTO frequencia (idAula, idAluno, dataReg, status, autor)
	         SELECT ?, A.id, ?, ?, ?
	         FROM alunos AS A
	         WHERE A.id = ?
	           AND '.self::getCondicaoAlunoAtivoParaFrequencia('A'),
	        [
	            $idAula,
	            date('Y-m-d H:i:s'),
	            $status,
	            (int)$autor,
	            $idAluno
	        ]
	    );

	    return self::getFrequencias($where)->fetchObject(self::class);
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

	private static function getSchemaConnection(){
	    $port = getenv('DB_PORT') ?: 3306;
	    $dsn = 'mysql:host='.getenv('DB_HOST').';dbname='.getenv('DB_NAME').';port='.$port.';charset=utf8mb4';
	    $connection = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'));
	    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	    return $connection;
	}

	private static function hasColumn(PDO $connection, $column){
	    $statement = $connection->prepare(
	        'SELECT COUNT(*)
	         FROM INFORMATION_SCHEMA.COLUMNS
	         WHERE TABLE_SCHEMA = DATABASE()
	           AND TABLE_NAME = "frequencia"
	           AND COLUMN_NAME = :column'
	    );
	    $statement->execute([':column' => $column]);

	    return (int)$statement->fetchColumn() > 0;
	}

	private static function ensureComparacaoFacialColumns(){
	    if(self::$comparacaoFacialColumnsReady !== null){
	        return self::$comparacaoFacialColumnsReady;
	    }

	    try{
	        $connection = self::getSchemaConnection();
	        $columns = [
	            'comparacaoFacialResultado' => 'VARCHAR(30) NULL',
	            'comparacaoFacialPontuacao' => 'DECIMAL(5,2) NULL',
	            'comparacaoFacialDetalhes' => 'TEXT NULL',
	            'comparacaoFacialData' => 'DATETIME NULL',
	        ];

	        foreach($columns as $column => $definition){
	            if(!self::hasColumn($connection, $column)){
	                $connection->exec('ALTER TABLE frequencia ADD COLUMN '.$column.' '.$definition);
	            }
	        }

	        self::$comparacaoFacialColumnsReady = true;
	    }catch(PDOException $e){
	        self::$comparacaoFacialColumnsReady = false;
	    }

	    return self::$comparacaoFacialColumnsReady;
	}

	public function registrarPresenca($autor, $fotoAuditoria = null, $comparacaoFacial = null){
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

	    if(is_array($comparacaoFacial) && self::ensureComparacaoFacialColumns()){
	        $this->comparacaoFacialResultado = $comparacaoFacial['status'] ?? null;
	        $this->comparacaoFacialPontuacao = $comparacaoFacial['score'] ?? null;
	        $this->comparacaoFacialDetalhes = json_encode($comparacaoFacial, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	        $this->comparacaoFacialData = $comparacaoFacial['createdAt'] ?? $this->dataReg;

	        $values['comparacaoFacialResultado'] = $this->comparacaoFacialResultado;
	        $values['comparacaoFacialPontuacao'] = $this->comparacaoFacialPontuacao;
	        $values['comparacaoFacialDetalhes'] = $this->comparacaoFacialDetalhes;
	        $values['comparacaoFacialData'] = $this->comparacaoFacialData;
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
