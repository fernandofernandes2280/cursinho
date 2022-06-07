<?php

namespace App\Controller\Operador;

use \App\Utils\View;
use \App\Model\Entity\Aluno as EntityAluno;
use \App\Model\Entity\Bairro as EntityBairro;
use \App\Model\Entity\Escolaridade as EntityEscolaridade;
use \App\Model\Entity\EstadoCivil as EntityEstadoCivil;
use \App\Model\Entity\Turma as EntityTurma;
use \App\Model\Entity\Status as EntityStatus;
use \App\Utils\Funcoes;
use \App\Controller\File\Upload as Upload;
use \App\Controller\Qrcode;
use \App\Model\Entity\User as EntityUser;

use \WilliamCosta\DatabaseManager\Pagination;
use Dompdf\Dompdf;
use Bissolli\ValidadorCpfCnpj\CPF;
use WilliamCosta\DatabaseManager\Database;


class Aluno extends Page{
	
	//Armazena quantidade total de pacientes listados
	private static $qtdTotal ;
	//esconde busca rápida de prontuário no navBar
	private static $hidden = '';
	
	//Método responsavel por obter a rendereizacao dos pacientes para a página
	private static function getAlunoItems($request, &$obPagination){
	   
	    $postVars = $request->getPostVars();
		
		$resultados = '';
		
		//Pagina Atual
		$queryParams = $request->getQueryParams();
		$paginaAtual = $queryParams['page'] ?? 1;
		
		
		
		$nome = $queryParams['nome'] ?? '';
		
		$id = $queryParams['id'] ?? '';
		
		$turma = $queryParams['turma'] ?? '';
		
		
		If(@$queryParams['cpfPesq'] != ''){
		
    		$cpf = $queryParams['cpfPesq'] ?? '';
    		
    		//instancia classe pra verificar CPF
    		$validaCpf = new CPF($cpf);
    		
    		//verifica se é válido o cpf
    		if (!$validaCpf->isValid()){
    		    $request->getRouter()->redirect('/operador/alunos?statusMessage=cpfInvalid');
    		}
            //ARMAZENA O CPF (SOMENTE OS NÚMEROS)		
    		$cpf= $validaCpf->getValue();
		}else{$cpf = null;}
		
		//recebe a matrícula vindo do form de pesquisa ou da Navbar
		$matricula = $queryParams['matricula'] ?? '';
		
		$status = $queryParams['status'] ?? '';
		
		//retira zeros à esquerda
		//if($pront != '') $pront += 0;
		
		
		
		//Condições SQL
		$condicoes = [
				
				strlen($nome) ? 'nome LIKE "%'.str_replace(' ', '%', $nome).'%"' : null,
				strlen($id) ? 'id = "'.$id.'"' : null,
		        strlen($turma) ? 'turma = "'.$turma.'"' : null,
		        strlen($matricula) ? 'matricula = "'.$matricula.'"' : null,
		        strlen($status) ? 'status = "'.$status.'" ' : null,
		        strlen($cpf) ? 'cpf = "'.$cpf.'" ' : null,
				
		];
		
		//Remove posições vazias
		$condicoes = array_filter($condicoes);
		
		//cláusula where
		$where = implode(' AND ', $condicoes);
		
	
		//Quantidade total de registros
		// $quantidadeTotal = EntityPaciente::getPacientes($where, null,null,'COUNT(*) as qtd')->fetchObject()->qtd;
		
		self::$qtdTotal = EntityAluno::getAlunos($where, null,null,'COUNT(*) as qtd')->fetchObject()->qtd;
		
		//Instancia de paginação
		$obPagination = new Pagination(self::$qtdTotal,$paginaAtual,5);
		#############################################
		
		
		//Verifica se existe pesquisa, se sim, ordena pelo ulltimo pac cadastrado, se nao, ordena pelo Prontuário
		$order = 'id DESC' ;
		
		
		
		//Obtem os pacientes
		$results = EntityAluno::getAlunos($where, $order, $obPagination->getLimit());
		
				
		//Renderiza
		while ($obAluno = $results -> fetchObject(EntityAluno::class)) {
	 
		    $reload = rand();
		    
		    //View de ALUNOS
			$resultados .= View::render('operador/modules/alunos/item',[
			    
			    //muda cor do texto do status para azul(ativo) ou vermelho(inativo)
			    $obAluno->status == 1 ? $cor = 'bg-gradient-success' : $cor = 'bg-gradient-danger',

			    'nome' => $obAluno->nome,
			    'status' =>EntityStatus::getStatusById($obAluno->status)->nome,
			    'cpf' => Funcoes::mask($obAluno->cpf, '###.###.###-##') ,
			    'id' => $obAluno->id,
			    'matricula' => $obAluno->matricula,
			    'turma' =>EntityTurma::getTurmaById($obAluno->turma)->nome,
			    'foto' => $obAluno->foto.'?var='.$reload,
			    'cor' => $cor,
			    'autor' => EntityUser::getUserById($obAluno->autor)->nome
					
					
					
			]);
			
		}
	
		//Grava o Log do usuário
	//	if(!empty($queryParams)) Logs::setNewLog('pacientes', 'Pesquisa' , implode(", ", $condicoes));

		//Retorna os pacientes
		return $resultados;
		
		
		
	}
	

	
	//Método responsavel por renderizar a view de Listagem de Pacientes
	public static function getAlunos($request){
	    
	    //finaliza sessao de aluno novo caso estejam ativas
	    Funcoes::init();
	    EntityAluno::getFinalizaSessaoDados();
	    
		$selectedAtivo = '';
		$selectedInativo = '';
		$selectedAtIn = '';
		$selectedAd = '';
		$selectedTm = '';
		$selectedAdTm = '';
		//Recebe os parâmetros da requisição
		$queryParams = $request->getQueryParams();
		
		if (isset($queryParams['status'])) {
			if($queryParams['status'] == 'ATIVO')$selectedAtivo = 'selected';
			else if($queryParams['status'] == 'INATIVO') $selectedInativo = 'selected';
			else $selectedAtIn = 'selected';
		}
		
		//esconde busca rápida de prontuário no navBar
		$hidden = '';
		
		
		
		//oculta o botão excluir para usuário Operador
	//	($_SESSION['operador']['usuario']['tipo'] == 'Operador' ? $botãoExcluir = 'hidden' : $botãoExcluir =  '' );
		//Conteúdo da Home
		$content = View::render('operador/modules/alunos/index',[
				'title' => 'Alunos > Pesquisa ',
				'itens' => self::getAlunoItems($request,$obPagination),
				'pagination' => parent::getPagination($request, $obPagination),
				'statusMessage' => self::getStatus($request),
				'nome' =>  $queryParams['nome'] ?? '',
				'matricula' =>  $queryParams['matricula'] ?? '',
		        'id' =>  $queryParams['id'] ?? '',
		        'matricula' =>  $queryParams['matricula'] ?? '',
		        'cpf' =>  $queryParams['cpfPesq'] ?? '',
				'total' => self::$qtdTotal,
				'selectedAtivo' =>  $selectedAtivo,
				'selectedInativo' =>  $selectedInativo,
		       'optionTurma' => EntityTurma::getSelectTurmas( @$queryParams['turma']) ,
		       'optionStatus' => EntityStatus::getSelectStatus( @$queryParams['status']) ,
		    //    'botaoExcluir' => $botãoExcluir
				
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Alunos > Cursinho', $content,'alunos', self::$hidden);
		
	}
	

	

	
	//Método responsavel por retornar a mensagem de status
	private static function getStatus($request){
	    //Query PArams
	    $queryParams = $request->getQueryParams();
	    
	    //Status
	    if(!isset($queryParams['statusMessage'])) return '';
	    
	    //Mensagens de status
	    switch ($queryParams['statusMessage']) {
	        case 'created':
	            return Alert::getSuccess('Aluno criado com sucesso!');
	            break;
	        case 'updated':
	            return Alert::getSuccess('Aluno atualizado com sucesso!');
	            break;
	        case 'deleted':
	            return Alert::getSuccess('Aluno excluído com sucesso!');
	            break;
	        case 'duplicad':
	            return Alert::getError('Aluno Já cadastrado!');
	            break;
	        case 'deletedfail':
	            return Alert::getError('Você não tem permissão para Excluir! Contate o operadoristrador.');
	            break;
	        case 'semfoto':
	            return Alert::getError('Nenhuma foto foi enviada!');
	            break;
	        case 'cpfInvalid':
	            return Alert::getError('CPF Inválido!');
	            break;
	    }
	}
	
	
	
	
	
	//Metodo responsávelpor retornar o formulário de Captura de foto do aluno
	public static function getPhotoAluno($request,$id){
	    $obAluno = EntityAluno::getAlunoById($id);
	    
	    //Conteúdo do Formulário
	    $content = View::render('operador/modules/alunos/formPhoto',[
	       'title' => 'Alunos > Capturar foto',
	        'aluno' => $obAluno->matricula.' '.$obAluno->nome,
	        'id' => $obAluno->id
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Editar Aluno > Cursinho', $content,'alunos', self::$hidden);
	    
	}
	
	
	//Metodo responsávelpor retornar o formulário de Captura de foto do aluno
	public static function setPhotoAluno($request){
	    
	    //Post Vars
	    $postVars = $request->getPostVars();
	    $fileVars = $request->getFileVars();
	  
	   
	    $obAluno = EntityAluno::getAlunoById($postVars['id']);
	    
	    if(!empty($fileVars['fImage']['name'] != '')){
	        $postVars['image'] = '';
	     
	        Upload::setUploadImages($request);
	        //Redireciona o usuário
	        $request->getRouter()->redirect('/operador/alunos/'.$obAluno->id.'/edit?statusMessage=updated');
	    }
	    
	    if ($postVars['image'] != ''){
	        
    	 
    	    
    	    //MÉTODO RESPONSÁVEL POR FAZER O UPLOADO DA IMAGE VINDA DA WEB CAM DO PROFESSOR
    	    Upload::setUploadImagesWebCamAluno($request);
    	    
    	    
    	    
    	    
    	    //Redireciona o usuário
    	    $request->getRouter()->redirect('/operador/alunos/'.$obAluno->id.'/edit?statusMessage=updated');
	    }
	    
	    $request->getRouter()->redirect('/operador/alunos/'.$obAluno->id.'/edit?statusMessage=semfoto');
	   
	    
	}
	
	
	
	
	//Metodo responsávelpor retornar o formulário de Edição de um Aluno
	public static function getEditAluno($request,$id){
	    
	    Funcoes::init();
	    if(isset($_SESSION['idAula'])){
	        $idAula = $_SESSION['idAula'];
	        $hideBtnFreq = '';
	    }else{
	        
	        $hideBtnFreq = 'hidden';
	    }
	    
	    
	    //Query PArams
	    $queryParams = $request->getQueryParams();

	    //busca o aluno pela matrícula
	    if(@$queryParams['matricula']){
	        $obAluno = EntityAluno::getAlunoByMatricula($queryParams['matricula']);
	        $request->getRouter()->redirect('/operador/alunos/'.$obAluno->id.'/edit');
	    }
	    
	    //obtém o Aluno do banco de dados
	    $obAluno = EntityAluno::getAlunoById($id);
	    
	    //Valida a instancia
	    if(!$obAluno instanceof EntityAluno){
	        $request->getRouter()->redirect('/operador/alunos');
	    }
	    $reload = rand();
	    //Conteúdo do Formulário
	    $content = View::render('operador/modules/alunos/form',[
	        'matricula'=>$obAluno->matricula,
	        'id' => $obAluno->id,
	        'title' => 'Alunos > Editar',
	        'nome' => $obAluno->nome,
	        'cep' => $obAluno->cep,
	        'endereco' => $obAluno->endereco,
	        'statusMessage' => self::getStatus($request),
	        'naturalidade' => $obAluno->naturalidade,
	        'fone' =>$obAluno->fone ,
	        'mae' => $obAluno->mae,
	        'obs' => $obAluno->obs,
	        'cpf' => Funcoes::mask($obAluno->cpf, '###.###.###-##') ,
	        'optionBairros' => EntityBairro::getSelectBairros($obAluno->bairro),
	        'optionEscolaridade' => EntityEscolaridade::getSelectEscolaridade($obAluno->escolaridade),
	        'optionEstadoCivil' => EntityEstadoCivil::getSelectEstadoCivil($obAluno->estadoCivil),
	        'cidade' => $obAluno->cidade,
	        'uf' => $obAluno->uf,
	        'dataNasc' => date('Y-m-d', strtotime($obAluno->dataNasc)),
	        'dataCad' => date('Y-m-d', strtotime($obAluno->dataCad)),
	        'optionTurma' => EntityTurma::getSelectTurmas($obAluno->turma),
	        'optionStatus' => EntityStatus::getSelectStatus($obAluno->status),
	        'foto' => $obAluno->foto.'?var='.$reload,
	        'ponteiro' => '',
	        'idAula' => @$idAula,
	        'hideBtnFreq' => $hideBtnFreq,
	        'idAluno' => $obAluno->id,
	       
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Editar Aluno > Cursinho', $content,'alunos', self::$hidden);
	    
	}
	
	//Metodo responsável por gravar a atualização de um Paciente
	public static function setEditAluno($request,$id){
	    //Post Vars
	    $postVars = $request->getPostVars();
	    
	    
	    
	    
	    //instancia classe pra verificar CPF
	    $validaCpf = new CPF($postVars['cpf']);

	    //obtém o Aluno do banco de dados
	    $obAluno = EntityAluno::getAlunoById($id);
	    
	    
	    //verifica se cpf informado é diferente do cpf já cadastrado
	    if($obAluno->cpf != $validaCpf->getValue()){
	        //busca usuário pelo CPF sem a maskara
	        $ob = EntityAluno::getAlunoByCpf($validaCpf->getValue());
	        //verifica se cpf informado já está cadastrado
	        if(($ob instanceof EntityAluno)){
	            $request->getRouter()->redirect('/operador/alunos/'.$ob->id.'/edit?statusMessage=cpfduplicated');
	        }
	    }
	    
	    
	    //Valida a instancia
	    if(!$obAluno instanceof EntityAluno){
	        $request->getRouter()->redirect('/operador/alunos');
	    }
	    
	    //redireciona caso seja feita busca rápida pela Matrícula
	    if(@$postVars['matricula']){
	        //obtém o Aluno do banco de dados
	        $obAlunoMatricula = EntityAluno::getAlunoByMatricula($postVars['matricula']);
	        //redireciona para os dados do aluno 
	        $request->getRouter()->redirect('/operador/alunos/'.$obAlunoMatricula->id.'/edit');
	        
	    }
	    
	    
	    //Atualiza a instância
	    $obAluno->nome = Funcoes::convertePriMaiuscula($postVars['nome']) ?? $obAluno->nome;
	    $obAluno->cep = $postVars['cep'] ?? $obAluno->cep;
	    $obAluno->endereco = Funcoes::convertePriMaiuscula($postVars['endereco']) ?? $obAluno->endereco;
	    $obAluno->bairro =  $postVars['bairro'] ?? $obAluno->bairro;
	    $obAluno->cidade = $postVars['cidade'] ?? $obAluno->cidade;
	    $obAluno->uf = Funcoes::convertePriMaiuscula($postVars['uf']) ?? $obAluno->uf;
	    $obAluno->dataNasc = implode("-",array_reverse(explode("/",$postVars['dataNasc'])));
	    //recebe a data do formulário e converte para objeto data
	    $dataCad = date_create_from_format('Y-m-d', $postVars['dataCad']);
	    //formata a data vinda do formulário com a hora atual
	    $obAluno->dataCad = $dataCad->format('Y-m-d H:i:s');
	    $obAluno->sexo = $postVars['sexo'] ?? $obAluno->sexo;
	    $obAluno->naturalidade = $postVars['naturalidade'] ?? $obAluno->naturalidade;
	    $obAluno->escolaridade = $postVars['escolaridade'] ?? $obAluno->escolaridade;
	    $obAluno->fone =str_replace('-', '', $postVars['fone']) ?? $obAluno->fone;
	    $obAluno->mae = Funcoes::convertePriMaiuscula($postVars['mae'])?? $obAluno->mae;
	    $obAluno->estadoCivil = $postVars['estadoCivil'] ?? $obAluno->estadoCivil;
	    $obAluno->status = $postVars['status'] ?? $obAluno->status;
	    $obAluno->obs = $postVars['obs'] ?? $obAluno->obs;
	    //recebe apenas os números do cpf
	    $obAluno->cpf = $validaCpf->getValue() ?? $obAluno->cpf;
	    $obAluno->turma = $postVars['turma'] ?? $obAluno->turma;
	    $obAluno->atualizar();
	    
	    
	    //	Logs::setNewLog($request);
	    
	    //Redireciona o usuário
	    $request->getRouter()->redirect('/operador/alunos/'.$obAluno->id.'/edit?statusMessage=updated');
	    
	}
	
	//Metodo responsávelpor retornar o formulário de Cadastro de um Aluno
	public static function getNewAluno($request){
	    
	    //Inicia sessão
	    Funcoes::init();
	    
	    $queryParams = $request->getQueryParams();
	    
	    
	    //instancia classe pra verificar CPF
	    $validaCpf = new CPF($queryParams['cpfAluno']);
	    
	    //verifica se é válido o cpf
	    if (!$validaCpf->isValid()){
	        
	        $request->getRouter()->redirect('/operador/alunos/?statusMessage=cpfInvalid');
	    }
	    
	    
	    //busca usuário pelo CPF sem a maskara
	    $ob = EntityAluno::getAlunoByCpf($validaCpf->getValue());
	    //verifica se o cpf já está cadastrado
	    if($ob instanceof EntityAluno){
	        $request->getRouter()->redirect('/operador/alunos?statusMessage=duplicad');
	    }
	    
	    
	    //Conteúdo do Formulário
	    $content = View::render('operador/modules/alunos/form',[
	        'matricula'=> '',
	        'title' => 'Alunos > Novo',
	        'nome' => @$_SESSION['aluno']['novo']['nome'] ?? '',
	        'cep' => @$_SESSION['aluno']['novo']['cep'] ?? '',
	        'endereco' => @$_SESSION['aluno']['novo']['endereco'] ?? '',
	        'naturalidade' => @$_SESSION['aluno']['novo']['naturalidade'] ?? '',
	        'fone' => @$_SESSION['aluno']['novo']['fone'] ?? '',
	        'mae' => @$_SESSION['aluno']['novo']['mae'] ?? '',
	        'obs' => @$_SESSION['aluno']['novo']['obs'] ?? '',
	        'cpf' => @$_SESSION['aluno']['novo']['cpf'] ?? @$validaCpf->getValue(),
	        'dataNasc' => @$_SESSION['aluno']['novo']['dataNasc'] ??'',
	        'dataCad' => @$_SESSION['aluno']['novo']['dataCad'] ??'',
	        'statusMessage' => self::getStatus($request),
	        'optionBairros' => @$_SESSION['aluno'] ? EntityBairro::getSelectBairros($_SESSION['aluno']['novo']['bairro']) : EntityBairro::getSelectBairros(null),
	        'optionEscolaridade' =>@$_SESSION['aluno'] ? EntityEscolaridade::getSelectEscolaridade($_SESSION['aluno']['novo']['escolaridade']) : EntityEscolaridade::getSelectEscolaridade(null),
	        'optionEstadoCivil' => @$_SESSION['aluno'] ? EntityEstadoCivil::getSelectEstadoCivil($_SESSION['aluno']['novo']['estadoCivil']) : EntityEstadoCivil::getSelectEstadoCivil(null),
	        'cidade' => @$_SESSION['aluno']['novo']['cidade'] ?? 'Santana',
	        'uf' => @$_SESSION['aluno']['novo']['uf'] ?? 'Ap',
	        'optionTurma' =>  @$_SESSION['aluno'] ? EntityTurma::getSelectTurmas($_SESSION['aluno']['novo']['turma']) : EntityTurma::getSelectTurmas(null),
	        'optionStatus' => @$_SESSION['aluno'] ? EntityStatus::getSelectStatus($_SESSION['aluno']['novo']['status']) : EntityStatus::getSelectStatus(null),
	        'foto' => 'profile.png',
	        'ponteiro' => 'pointer-events: none;'
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Novo Aluno > Cursinho', $content,'alunos', self::$hidden);
	    
	}
	
	//Metodo responsável por gravar um Novo Aluno
	public static function setNewAluno($request){
	    
	    
	    //Post Vars
	    $postVars = $request->getPostVars();
	    
	    //Cria sessão com os dados do form
	    EntityAluno::getSessaoDados($postVars);
	    
	    
	    //instancia classe pra verificar CPF
	    $validaCpf = new CPF($postVars['cpf']);
	    
	    //busca usuário pelo CPF sem a maskara
	    $ob = EntityAluno::getAlunoByCpf($validaCpf->getValue());
	    //verifica se o cpf já está cadastrado
	    if($ob instanceof EntityAluno){
	        $request->getRouter()->redirect('/operador/alunos/new?statusMessage=cpfduplicated');
	    }
	    
	    
	    //Nova instância de Aluno
	    $obAluno = new EntityAluno;
	    
	    
	    //recebe os dados
	    $obAluno->nome = Funcoes::convertePriMaiuscula($postVars['nome']);
	    $obAluno->cep = $postVars['cep'];
	    $obAluno->endereco = Funcoes::convertePriMaiuscula($postVars['endereco']);
	    $obAluno->bairro =  $postVars['bairro'];
	    $obAluno->cidade = $postVars['cidade'];
	    $obAluno->uf = Funcoes::convertePriMaiuscula($postVars['uf']);
	    $obAluno->dataNasc = implode("-",array_reverse(explode("/",$postVars['dataNasc'])));
	    //recebe a data do formulário e converte para objeto data
	    $dataCad = date_create_from_format('Y-m-d', $postVars['dataCad']);
	    //formata a data vinda do formulário com a hora atual
	    //$obAluno->dataCad = $dataCad->format('Y-m-d H:i:s');
	    $obAluno->sexo = $postVars['sexo'];
	    $obAluno->naturalidade = $postVars['naturalidade'];
	    $obAluno->escolaridade = $postVars['escolaridade'];
	    $obAluno->fone = $postVars['fone'];
	    $obAluno->mae = Funcoes::convertePriMaiuscula($postVars['mae']);
	    $obAluno->estadoCivil = $postVars['estadoCivil'];
	    $obAluno->status = $postVars['status'];
	    $obAluno->obs = $postVars['obs'];
	    //recebe apenas os números do cpf
	    $obAluno->cpf = $validaCpf->getValue();
	    $obAluno->turma = $postVars['turma'];
	    $obAluno->cadastrar();
	    
	    //define a matrícula
	    $obMatricula = EntityAluno::getAlunoByCpf($validaCpf->getValue());
	    $obMatricula->matricula = EntityAluno::geraMatricula($obMatricula->id);
	    $obMatricula->atualizar();
	    
	    //encerra sessão com os dados do form
	    EntityAluno::getFinalizaSessaoDados();
	    
	    //	Logs::setNewLog($request);
	    
	    //Redireciona o usuário
	    $request->getRouter()->redirect('/operador/alunos/'.$obAluno->id.'/edit?statusMessage=created');
	    
	}
	
	//Metodo responsávelpor retornar o formulário de Exclusão de um Aluno
	public static function getDeleteAluno($request,$id){
	    //obtém o deopimento do banco de dados
	    $obAluno = EntityAluno::getAlunoById($id);
	    
	    //Valida a instancia
	    if(!$obAluno instanceof EntityAluno){
	        $request->getRouter()->redirect('/operador/alunos');
	    }
	    
	    
	    //Conteúdo do Formulário
	    $content = View::render('operador/modules/alunos/delete',[
	        'title'=> 'Alunos > Excluir',
	        'matricula' => $obAluno->matricula,
	        'nome' => $obAluno->nome
	        
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Excluir Aluno > Cursinho', $content,'alunos', self::$hidden);
	    
	}
	//Metodo responsável por Excluir um Aluno
	public static function setDeleteAluno($request,$id){
	    
	    
	    //obtém o paciente do banco de dados
	    $obAluno = EntityAluno::getAlunoById($id);
	    
	    //Valida a instancia
	    if(!$obAluno instanceof EntityAluno){
	        $request->getRouter()->redirect('/operador/alunos');
	    }
	    
	    //Exclui o depoimento
	    $obAluno->excluir();
	    
	    //Redireciona o usuário
	    $request->getRouter()->redirect('/operador/alunos?statusMessage=deleted');
	    
	    
	}
	
	//MÉTODO RESPONSÁVEL POR RENDERIZAR A CARTEIRA DE ALUNO
	public static function getCarteiraAluno($request,$id){
	    Funcoes::init();
	    
	    if(empty($id)){
	        //VERIFICA SE O CADASTRO ESTÁ INCOMPLETO
	        if(isset($_SESSION['naoCompleto'])) $request->getRouter()->redirect('/aluno');
	        
	        if(!isset($_SESSION['idAluno'])) $request->getRouter()->redirect('/aluno');
	        
	    }
	    
	    @$_SESSION['idAluno'] ? $id = $_SESSION['idAluno'] : $id = $id;
	    
	    @$_SESSION['idAluno'] ? $hiddenBtnSairUpdate = '' : $hiddenBtnSairUpdate = 'hidden';
	    @$_SESSION['idAluno'] ? $hiddenBtnSair = 'hidden' : $hiddenBtnSair = '';
	    
	    
	    @$_SESSION['updated'] ? $hiddenAlterar = '' : $hiddenAlterar = 'hidden';
	    
	    
	    //obtém o Aluno do banco de dados
	    $obAluno = EntityAluno::getAlunoById($id);
	    
	    //Valida a instancia
	    if(!$obAluno instanceof EntityAluno){
	        $request->getRouter()->redirect('/operador/alunos');
	    }
	    
	    
	    
	    $oQRC = new \App\Controller\Qrcode\Qrcode(); // Create vCard Object
	    $oQRC->fullName($obAluno->matricula); // Add Full Name
	    //  ->finish(); // End vCard
	    
	    
	    $path = $oQRC->get(300);
	    header('Content-Type: image/png');
	    //  header('Content-Disposition: attachment; filename="chart.png"');
	    $image = file_get_contents($path);
	    //  header('Content-Length: ' . strlen($image));
	    //  header("Content-Disposition: attachment; filename=\"$basename\"");
	    //  readfile($file);
	    $dir = __DIR__.'/carteiras/';
	    $name = $obAluno->matricula.'.png';
	    file_put_contents($dir.$name, $image);
	    
	    //     $type = pathinfo($path, PATHINFO_EXTENSION);
	    //    $data = file_get_contents($path);
	    //   $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
	    //Decode the string
	    //   $unencodedData=base64_decode($base64);
	    //     file_put_contents(__DIR__.'/carteiras/imgQrcode.png', $path);
	    // echo '<p><img src="' . $oQRC->get(300) . '" alt="QR Code" /></p>'; // Generate and display the QR Code
	    //  $oQRC->display(300); // Set size and display QR Code default 150px
	    $reload = rand();
	    //Conteúdo do Formulário
	    
	    $content = View::render('pages/carteira',[
	        'title'=>'Alunos > Carteira de Estudante',
	        'foto' => $obAluno->foto.'?var='.$reload,
	        'matricula'=> $obAluno->matricula,
	        'nome' => strtoupper($obAluno->nome),
	        'turma' => strtoupper(EntityTurma::getTurmaById($obAluno->turma)->nome),
	        'mae' => strtoupper($obAluno->mae),
	        'cpf' => Funcoes::mask($obAluno->cpf, '###.###.###-##'),
	        'dataNasc' => date('d/m/Y', strtotime($obAluno->dataNasc)),
	        'dataCad'=>date('d/m/Y', strtotime($obAluno->dataCad)),
	        'qrcode' => $name,
	        'status' => EntityStatus::getStatusById($obAluno->status)->nome,
	        'hiddenBtnAlterar' => $hiddenAlterar,
	        'hiddenBtnSairUpdate' => $hiddenBtnSairUpdate,
	        'hiddenBtnSair' => $hiddenBtnSair,
	        
	        
	    ]);
	    
	    //Retorna a página completa
	    
	        return parent::getPage('Carteira do Aluno > Cursinho', $content,'alunos', self::$hidden);
	   
	}
	
	//MÉTODO RESPONSÁVEL POR GERAR O ARQUIVO DE IMAGEM E DOWNLOAD DA CARTEIRA DE ALUNO
	public static function setCarteiraAluno($request,$id){
	    
	    
	    //Get the base-64 string from data
	    $filteredData=substr($_POST['img_val'], strpos($_POST['img_val'], ",")+1);
	    
	    //Decode the string
	    $unencodedData=base64_decode($filteredData);
	    
	    $name=$_POST['matricula'].$_POST['nome'].'.png';
	    
	    //   var_dump(__DIR__.'/carteiras/img.png');exit;
	    //Save the image
	    file_put_contents(__DIR__.'/carteiras/'.$name, $unencodedData);
	    
	    $imagem = __DIR__.'/carteiras/'.$name;
	    
	    
	    
	    $filename = $imagem;
	    $rotang = -90; // Rotation angle
	    $source = imagecreatefrompng($filename) or die('Error opening file '.$filename);
	    imagealphablending($source, false);
	    imagesavealpha($source, true);
	    
	    $rotation = imagerotate($source, $rotang, imageColorAllocateAlpha($source, 0, 0, 0, 127));
	    imagealphablending($rotation, false);
	    imagesavealpha($rotation, true);
	    
	    //download da imagem
	    if($_POST['opcao'] == 'down'){
	        header('Content-Disposition: Attachment;filename='.$name.'');
	    }
	    
	    header('Content-type: image/png');
	    imagepng($rotation);
	    imagedestroy($source);
	    imagedestroy($rotation);
	    unlink($filename);
	    
	    //   header("Content-Disposition: attachment; filename=\"$filename\"");
	    //  readfile($filename);
	    
	    /*
	     header("Expires: 0");
	     header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	     header("Cache-Control: no-store, no-cache, must-revalidate");
	     header("Cache-Control: post-check=0, pre-check=0", false);
	     header("Pragma: no-cache");
	     
	     $ext = pathinfo($file, PATHINFO_EXTENSION);
	     $basename = pathinfo($file, PATHINFO_BASENAME);
	     
	     header("Content-type: application/".$ext);
	     // tell file size
	     header('Content-length: '.filesize($file));
	     // set file name
	     header("Content-Disposition: attachment; filename=\"$basename\"");
	     readfile($file);
	     */
	    // Exit script. So that no useless data is output.
	    exit;
	    
	    
	}
	
}