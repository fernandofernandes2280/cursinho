<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;
use App\Utils\Funcoes;
class Aluno extends Generica{

	const FOTO_PADRAO = 'profile.png';

	/** @var string|null */
	public $endereco;

	/** @var string|null */
	public $numero;
	
	//bairro do aluno
	/** @var string|null */
	public $bairro;
	
	//cidade do aluno
	/** @var string|null */
	public $cidade;
	
	//cep do aluno
	/** @var string|null */
	public $cep;
	
	//unidade federal do aluno	
	/** @var string|null */
	public $uf;
	
	//telefone do aluno 
	/** @var string|null */
	public $fone;
	
	//data de nascimento do aluno
	/** @var string|null */
	public $dataNasc;
	
	//data de cadastro do aluno
	/** @var string|null */
	public $dataCad;
	
	//cidade de nascimento do aluno
	/** @var string|null */
	public $naturalidade;
	
	//nome da mãe do aluno
	/** @var string|null */
	public $mae;
	
	//escolaridade do aluno
	/** @var string|null */
	public $escolaridade;
	
	//sexo do aluno
	/** @var string|null */
	public $sexo;
	
	//caminho da foto do aluno
	/** @var string|null */
	public $foto;
	
	//estado civil do aluno
	/** @var string|null */
	public $estadoCivil;
	
	//observações do aluno
	/** @var string|null */
	public $obs;
	
	//status do aluno (ativo/inativo)
	/** @var string|null */
	public $status;
	
	//CPF do aluno
	/** @var string|null */
	public $cpf;
	
	//Turma do aluno
	/** @var string|null */
	public $turma;
	
	//Matrícula do aluno
	/** @var string|null */
	public $matricula;
	
	//Autor do cadastrio
	/** @var string|null */
	public $autor;

	public function getFoto($cache = true){
	    $foto = trim((string)$this->foto);
	    $path = parse_url($foto, PHP_URL_PATH);
	    $foto = basename($path ?: $foto);
	    $diretorioFotos = dirname(__DIR__, 2).'/Controller/File/files/fotos/';

	    if($foto === '' || strtolower($foto) === 'null' || !is_file($diretorioFotos.$foto)){
	        $foto = self::FOTO_PADRAO;
	    }

	    return $cache ? $foto.'?var='.rand() : $foto;
	}

	public function semFoto(){
	    return $this->getFoto(false) === self::FOTO_PADRAO;
	}


	public static function geraMatricula($id){
	   
	    $nossoNumero = date('Y').$id;
	    
	    // agora vamos definir os índices de multiplicação
	    $indices = "29876543298765432";
	    // e aqui a soma da multiplicação coluna por coluna
	    $soma = 0;
	    
	    // fazemos a multiplicação coluna por coluna agora
	    for($i = 0; $i < strlen($nossoNumero); $i++){
	        $soma = $soma + ((int)($nossoNumero[$i])) *
	        ((int)($indices[$i]));
	    }
	    
	    // obtemos o resto da divisão da soma por onze
	    $resto = $soma % 11;
	    
	    // subtraímos onze pelo resto da divisão
	    $digito = 11 - $resto;
	    
	    // atenção: Se o resultado da subtração for
	    // maior que 9 (nove), o dígito será 0 (zero)
	    if($digito > 9){
	        $digito = 0;
	    }
	    
	    $matricula = $nossoNumero.'-'.$digito;
	    
	    return $matricula;
	    
	}
	
	
		
	//Método responsavel por cadastrar um aluno no banco de dados
	public function cadastrar(){
	    
	    $this->foto = self::FOTO_PADRAO;
	    
		//define a data
		$this->dataCad = date('Y-m-d H:i:s');
		$this->dataNasc = trim((string)$this->dataNasc) !== '' ? $this->dataNasc : null;
		//Insere aluno no banco de dados
		$this->id = (new Database('alunos'))->insert([
				'nome' => $this->nome,
				'endereco'=>$this->endereco,
		    	'numero'=>$this->numero,
				'bairro'=>$this->bairro,
				'cidade'=>$this->cidade,
				'uf'=>$this->uf,
				'cep'=>$this->cep,
				'fone'=>$this->fone,
				'dataNasc'=>$this->dataNasc,
				'dataCad'=>$this->dataCad,
				'naturalidade'=>$this->naturalidade,
				'mae'=>$this->mae,
				'escolaridade'=>$this->escolaridade,
				'sexo'=>$this->sexo,
				'foto'=>$this->foto,
				'estadoCivil'=>$this->estadoCivil,
				'obs'=>$this->obs,
				'status'=>$this->status,
		        'cpf'=>$this->cpf,
		        'turma'=>$this->turma,
		        'autor'=>$_SESSION['usuario']['id'] //id  do usuario logado,
		        
		]);
		//Sucesso
		return true;
	}
	
	
	//Método responsavel por atualizar os banco de dados com os dados da instancia atual de aluno
	public function atualizar(){
		
		//Atualiza aluno no banco de dados
		return (new Database('alunos'))->update('id = '.$this->id,[
				'nome' => $this->nome,
				'endereco'=>$this->endereco,
		    	'numero'=>$this->numero,
				'bairro'=>$this->bairro,
				'cidade'=>$this->cidade,
				'uf'=>$this->uf,
				'cep'=>$this->cep,
				'fone'=>$this->fone,
				'dataNasc'=>$this->dataNasc,
		        'dataCad'=>$this->dataCad,
				'naturalidade'=>$this->naturalidade,
				'mae'=>$this->mae,
				'escolaridade'=>$this->escolaridade,
				'sexo'=>$this->sexo,
				'foto'=>$this->foto,
				'estadoCivil'=>$this->estadoCivil,
				'obs'=>$this->obs,
				'status'=>$this->status,
				'cpf'=>$this->cpf,
		        'turma'=>$this->turma,
		        'matricula'=> $this->matricula,
		   
			
		]);
	
	}
	//Método responsavel por retornar um Aluno com base no seu Id
	public static function getAlunoById($id){
		return self::getalunos('id = '.$id)->fetchObject(self::class);
		
	}
	
	//Método responsavel por retornar um Aluno com base no seu CPF
	public static function getAlunoByCpf($cpf){
		return self::getAlunos('cpf = '.$cpf)->fetchObject(self::class);
	}
	
	//Método responsavel por retornar um Aluno com base na sua Matrícula
	public static function getAlunoByMatricula($matricula){
	    return self::getAlunos('matricula = "'.$matricula.'"')->fetchObject(self::class);
	}
	
	
	//Método responsavel por retornar alunos
	public static function getAlunos($where = null, $order = null, $limit = null, $fields = '*') {
		return (new Database('alunos'))->select($where,$order,$limit,$fields);
	}
	
	//Método responsavel por excluir um Aluno do banco de dadosl
	public function excluir(){
		
	    //Exclui o Aluno no Banco de Dados
		return (new Database('alunos'))->delete('id = '.$this->id);
		
		//Sucesso
		return true;
	}
	
	//Método responsavel por iniciar sessao com dados do form
	public static function getSessaoDados($ob){
	    return Funcoes::flashOldInput('aluno.novo', [
	        'nome' => $ob['nome'] ?? '',
	        'cep' => $ob['cep'] ?? '',
	        'endereco' => $ob['endereco'] ?? '',
	        'numero' => $ob['numero'] ?? '',
	        'bairro' => $ob['bairro'] ?? '',
	        'cidade' => $ob['cidade'] ?? '',
	        'uf' => $ob['uf'] ?? '',
	        'dataNasc' => $ob['dataNasc'] ?? '',
	        'cpf' => $ob['cpf'] ?? '',
	        'fone' => $ob['fone'] ?? '',
	        'status' => $ob['status'] ?? '',
	        'naturalidade' => $ob['naturalidade'] ?? '',
	        'escolaridade' => $ob['escolaridade'] ?? '',
	        'estadoCivil' => $ob['estadoCivil'] ?? '',
	        'sexo' => $ob['sexo'] ?? '',
	        'dataCad'=> $ob['dataCad'] ?? date('Y-m-d'),
	        'turma' => $ob['turma'] ?? '',
	        'mae' => $ob['mae'] ?? '',
	        'obs' => $ob['obs'] ?? ''
	    ]);
	}
	
	//Método responsavel por Finalizar sessao
	public static function getFinalizaSessaoDados(){
	    return Funcoes::clearOldInput('aluno.novo');
	}
	
	
}
