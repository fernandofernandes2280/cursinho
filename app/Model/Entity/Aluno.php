<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;
use App\Utils\Funcoes;
class Aluno extends Generica{
	
	public $endereco;
	
	//bairro do aluno
	public $bairro;
	
	//cidade do aluno
	public $cidade;
	
	//cep do aluno
	public $cep;
	
	//unidade federal do aluno
	public $uf;
	
	//telefone do aluno
	public $fone;
	
	//data de nascimento do aluno
	public $dataNasc;
	
	//data de cadastro do aluno
	public $dataCad;
	
	//cidade de nascimento do aluno
	public $naturalidade;
	
	//nome da mãe do aluno
	public $mae;
	
	//escolaridade do aluno
	public $escolaridade;
	
	//sexo do aluno
	public $sexo;
	
	//caminho da foto do aluno
	public $foto;
	
	//estado civil do aluno
	public $estadoCivil;
	
	//observações do aluno
	public $obs;
	
	//status do aluno (ativo/inativo)
	public $status;
	
	//CPF do aluno
	public $cpf;
	
	//Turma do aluno
	public $turma;
	
	//Matrícula do aluno
	public $matricula;
	
	//Autor do cadastrio
	public $autor;
	
	
	public static function geraMatricula($turma,$id){
	   
	    $nossoNumero = date('Y').$id.$turma;
	    
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
	    
	    $this->foto = 'profile.png';
	    
		//define a data
		$this->dataCad = date('Y-m-d H:i:s');
		//DEFINE A DATA DE NASC TEMPORARIA
		$this->dataNasc = date('Y-m-d H:i:s');
		//Insere aluno no banco de dados
		$this->id = (new Database('alunos'))->insert([
				'nome' => $this->nome,
				'endereco'=>$this->endereco,
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
	    //inicia sessão
	    Funcoes::init();
	    
	    //Define a sessao do usuario
	    $_SESSION['aluno']['novo'] = [
	        'nome' => $ob['nome'],
	        'cep' => $ob['cep'],
	        'endereco' => $ob['endereco'],
	        'bairro' => $ob['bairro'],
	        'cidade' => $ob['cidade'],
	        'uf' => $ob['uf'],
	        'dataNasc' => date('Y-m-d',strtotime($ob['dataNasc'])),
	        'cpf' => $ob['cpf'],
	        'fone' => $ob['fone'],
	        'status' => $ob['status'],
	        'naturalidade' => $ob['naturalidade'],
	        'escolaridade' => $ob['escolaridade'],
	        'estadoCivil' => $ob['estadoCivil'],
	        'sexo' => $ob['sexo'],
	        'dataCad'=> date('Y-m-d',strtotime($ob['dataCad'])),
	        'turma' => $ob['turma'],
	        'mae' => $ob['mae'],
	        'obs' => $ob['obs']
	        
	        
	    ];
	    
	    //Sucesso
	    return true;
	}
	
	//Método responsavel por Finalizar sessao
	public static function getFinalizaSessaoDados(){
	    //Inicia a Sessão
	    Funcoes::init();
	    
	    //destri a sessao
	    unset($_SESSION['aluno']['novo']);
	    
	    //sucesso
	    return true;
	}
	
	
}