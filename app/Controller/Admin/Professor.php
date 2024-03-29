<?php

namespace App\Controller\Admin;

use \App\Utils\View;
use \App\Model\Entity\Bairro as EntityBairro;
use \App\Utils\Funcoes;
use \App\Model\Entity\Professor as EntityProfessor;
use \App\Model\Entity\Disciplina as EntityDisciplina;
use \App\Model\Entity\Status as EntityStatus;
use \App\Model\Entity\DisciplinaProfessor as EntityDisciplinaProfessor;
use \WilliamCosta\DatabaseManager\Pagination;
use Dompdf\Dompdf;
use Bissolli\ValidadorCpfCnpj\CPF;
use \App\Controller\File\Upload as Upload;
use \App\Controller\Admin\Resize;


class Professor extends Page{
	
	//Armazena quantidade total de registros listados
	private static $qtdTotal ;
	//esconde busca rápida de prontuário no navBar (''->exibe  'hidden'->esconde)
	private static $buscaRapidaPront = 'hidden';
	
	//Método responsavel por obter a rendereizacao dos pacientes para a página
	private static function getProfessoresItems($request, &$obPagination){
		
		
		
		$resultados = '';
		
		//Pagina Atual
		$queryParams = $request->getQueryParams();
		$paginaAtual = $queryParams['page'] ?? 1;
		
		
		//Armazena valor busca pelo nome do paciente
		$nome = $queryParams['nome'] ?? '';
		
		//Filtro Status
		$filtroStatus = $queryParams['status'] ?? '';
		//Filtro recebe apenas os valores possíveis(s ou n) caso contrário recebe vazio.
		$filtroStatus = in_array($filtroStatus, ['1','0']) ? $filtroStatus : '';
		
		//Condições SQL
		$condicoes = [
				
				strlen($nome) ? 'nome LIKE "%'.str_replace(' ', '%', $nome).'%"' : null,
				strlen($filtroStatus) ? 'status = "'.$filtroStatus.'" ' : null,
		];
		
		//Remove posições vazias
		$condicoes = array_filter($condicoes);
		
		//cláusula where
		$where = implode(' AND ', $condicoes);
		
	
		//Quantidade total de registros
		// $quantidadeTotal = EntityPaciente::getPacientes($where, null,null,'COUNT(*) as qtd')->fetchObject()->qtd;
		
		self::$qtdTotal = EntityProfessor::getProfessores($where, null,null,'COUNT(*) as qtd')->fetchObject()->qtd;
		
		//Instancia de paginação
		$obPagination = new Pagination(self::$qtdTotal,$paginaAtual,5);
		#############################################
		
		
		//Verifica se existe pesquisa, se sim, ordena pelo ulltimo pac cadastrado, se nao, ordena pelo Prontuário
		$order = 'id' ;
		
		
		
		//Obtem os pacientes
		$results = EntityProfessor::getProfessores($where, $order, $obPagination->getLimit());
		
		
		
		$resultadoDisciplinas = '';
		//Renderiza
		while ($obProfessor = $results -> fetchObject(EntityProfessor::class)) {
		    
		    $resultsDisciplina = EntityDisciplinaProfessor::getDisciplinasProfessor('idProfessor = '.$obProfessor->id);
		    while ($obDisciplina = $resultsDisciplina -> fetchObject(EntityDisciplinaProfessor::class)) {
		        $resultadoDisciplinas .= ''.@EntityDisciplina::getDisciplinaById($obDisciplina->idDisciplina)->nome.', ';
		    }
			 
		    $reload = rand();
			//View de pacientes
			$resultados .= View::render('admin/modules/professores/item',[
			
			//muda cor do texto do status para azul(ativo) ou vermelho(inativo)
			    $obProfessor->status == 1 ? $cor = 'bg-gradient-success' : $cor = 'bg-gradient-danger',

			    'nome' => $obProfessor->nome,
			    'cpf' =>Funcoes::mask($obProfessor->cpf, '###.###.###-##') ,
			    'status' =>EntityStatus::getStatusById($obProfessor->status)->nome,
			    'id' => $obProfessor->id,
			    'cor' => $cor,
			    'email' => $obProfessor->email,
			    'foto' => $obProfessor->foto.'?var='.$reload,
			    'disciplinas' => rtrim($resultadoDisciplinas,', '),
			    'visivelDeleteProfessor' => permissaoExcluirProfessor,
			]);
			$resultadoDisciplinas = '';
		}
	
		//Grava o Log do usuário
//		if(!empty($queryParams)) Logs::setNewLog('pacientes', 'Pesquisa' , implode(", ", $condicoes));

		//Retorna os pacientes
		return $resultados;
		
		
		
	}
	
	
	//Método responsavel por renderizar a view de Listagem de Pacientes
	public static function getProfessores($request){
		$selectedAtivo = '';
		$selectedInativo = '';
		$selectedAtIn = '';
		$selectedAd = '';
		$selectedTm = '';
		$selectedAdTm = '';
		//Recebe os parâmetros da requisição
		$queryParams = $request->getQueryParams();

	//	var_dump('ola');exit;

		if (isset($queryParams['tipo'])) {
			if($queryParams['tipo'] == 'TM')$selectedTm = 'selected';
					else if($queryParams['tipo'] == 'AD') $selectedAd = 'selected';
					else $selectedAdTm = 'selected';
		}
		
		if (isset($queryParams['status'])) {
			if($queryParams['status'] == '1')$selectedAtivo = 'selected';
			else if($queryParams['status'] == '0') $selectedInativo = 'selected';
			else $selectedAtIn = 'selected';
		}
		
		//esconde busca rápida de prontuário no navBar
		$hidden = '';
		//Conteúdo da Home
		$content = View::render('admin/modules/professores/index',[
				'title' => 'Professores > Pesquisa',
				'itens' => self::getProfessoresItems($request,$obPagination),
				'pagination' => parent::getPagination($request, $obPagination),
				'statusMessage' => Funcoes::getStatus($request),
				'nome' =>  $queryParams['nome'] ?? '',
				'pront' =>  $queryParams['pront'] ?? '',
				'totalPacientes' => self::$qtdTotal,
				'selectedAtivo' =>  $selectedAtivo,
				'selectedInativo' =>  $selectedInativo,
				'selectedAdTm' => $selectedAdTm,
				'selectedAd' =>  $selectedAd,
				'selectedTm' =>  $selectedTm,
				'selectedAtIn' => $selectedAtIn,
				
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Professores > Cursinho', $content,'professores', self::$buscaRapidaPront);
		
	}
	
	
	//Metodo responsávelpor retornar o formulário de Cadastro de um novo Profissional
	public static function getNewProfessor($request){

	    //Inicia sessão
	    Funcoes::init();
	    
	    //QUERY PARAMS
	    $queryParams = $request->getQueryParams();
	    
	    //instancia classe pra verificar CPF
	    $validaCpf = new CPF($queryParams['cpfProfessor']);
	    
	    //verifica se é válido o cpf
	    if (!$validaCpf->isValid()){
	        
	        $request->getRouter()->redirect('/admin/professores/?statusMessage=cpfInvalid');
	    }
	    
	    
	    //busca usuário pelo CPF sem a maskara
	    $ob = EntityProfessor::getProfessorByCPF($validaCpf->getValue());
	    //verifica se o cpf já está cadastrado
	    if($ob instanceof EntityProfessor){
	        $request->getRouter()->redirect('/admin/professores?statusMessage=duplicad');
	    }
	    

	    //Conteúdo do Formulário
	    $content = View::render('admin/modules/professores/form',[
	        
	        'title' => 'Professor > Novo',
	        'nome' => @$_SESSION['professor']['novo']['nome'] ?? '',
	        'cep' => @$_SESSION['professor']['novo']['cep'] ??'',
	        'endereco' => @$_SESSION['professor']['novo']['endereco'] ?? '',
	        'statusMessage' => Funcoes::getStatus($request),
	        'fone' => @$_SESSION['professor']['novo']['fone'] ??'',
	        'cidade' => @$_SESSION['professor']['novo']['cidade'] ??'Santana',
	        'uf' => @$_SESSION['professor']['novo']['uf'] ??'AP',
	        'cpf' => @$_SESSION['professor']['novo']['cpf'] ?? @$validaCpf->getValue(),
	        'funcao' => @$_SESSION['professor']['novo']['funcao'] ??'Professor',
	        'dataNasc' => @$_SESSION['professor']['novo']['dataNasc'] ??'',
	        'optionBairros' =>@$_SESSION['professor']['novo']['bairro'] ? EntityBairro::getSelectBairros($_SESSION['professor']['novo']['dataNasc']) : EntityBairro::getSelectBairros(null),
	        'email' => @$_SESSION['professor']['novo']['email'] ??'',
	        'adicionarDisciplina' => 'hidden',
	        'optionStatus' => @$_SESSION['professor']['novo']['status'] ? EntityStatus::getSelectStatus($_SESSION['professor']['novo']['status']) : EntityStatus::getSelectStatus(null),
	        'foto' => 'profile.png',
	        'labelId' => 'hidden',
	        'ponteiro' => 'pointer-events: none;'
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Novo Professor > Cursinho', $content,'professores', self::$buscaRapidaPront);
	    
	}
	
	
	//Metodo responsável por gravar um Novo Professor
	public static function setNewProfessor($request){
	    
	    //Post Vars
	    $postVars = $request->getPostVars();
	   
	    //Cria sessão com os dados do form
	    EntityProfessor::getSessaoDados($postVars);
	 
	    //instancia classe pra verificar CPF
	    $validaCpf = new CPF($postVars['cpf']);
	    
	    //busca usuário pelo CPF sem a maskara
	    $obProfessor = EntityProfessor::getProfessorByCPF($validaCpf->getValue());
	    
	    if($obProfessor instanceof EntityProfessor){
	        $request->getRouter()->redirect('/admin/professores/new?statusMessage=cpfDuplicated');
	    }
	    
	    
	    //Nova instancia de Usuário
	    $obProfessor = new EntityProfessor();
	    $obProfessor->nome = Funcoes::convertePriMaiuscula($postVars['nome']);
	    $obProfessor->cep = $postVars['cep'] ?? '';
	    $obProfessor->endereco = $postVars['endereco'] ?? '';
	    $obProfessor->bairro =  $postVars['bairro'];
	    $obProfessor->cidade = Funcoes::convertePriMaiuscula($postVars['cidade']) ?? '';
	    $obProfessor->uf = Funcoes::convertePriMaiuscula($postVars['uf']) ?? '';
	    $obProfessor->funcao = $postVars['funcao'];
	    $obProfessor->dataNasc = implode("-",array_reverse(explode("/",$postVars['dataNasc'])));
	    $obProfessor->cpf = $validaCpf->getValue(); //cpf sem formatação
	    $obProfessor->fone = $postVars['fone'] ?? '';
	    $obProfessor->status = $postVars['status'];
	    $obProfessor->email = $postVars['email'];
	    $obProfessor->cadastrar();
	    
	    //encerra sessão com os dados do form
	    EntityProfessor::getFinalizaSessaoDados();
	    
	    //	Logs::setNewLog($request);
	    
	    //Redireciona o usuário
	    $request->getRouter()->redirect('/admin/professores/'.$obProfessor->id.'/edit?statusMessage=created');
	    
	}
	
	//Metodo responsávelpor retornar o formulário de Edição de um Profissional
	public static function getEditProfessor($request,$id){
	    
	    //obtém o Profissional do banco de dados
	    $obProfessor = EntityProfessor::getProfessorById($id);
	    
	    //Valida a instancia
	    if(!$obProfessor instanceof EntityProfessor){
	        $request->getRouter()->redirect('/admin/professores');
	    }
	    
	    self::setDisciplinaAdd($request,$id);
	    self::setDisciplinaRemove($request,$id);
	    
	    $reload = rand();
	    //Conteúdo do Formulário
	    $content = View::render('admin/modules/professores/form',[
	       
	        'id' => $obProfessor->id,
	        'title' => 'Professor > Editar',
	        'nome' => $obProfessor->nome,
	        'cep' => $obProfessor->cep,
	        'endereco' => $obProfessor->endereco,
	        'statusMessage' => Funcoes::getStatus($request),
	        'fone' => $obProfessor->fone,
	        'cidade' => $obProfessor->cidade,
	        'uf' => $obProfessor->uf,
	        'cpf' => Funcoes::mask($obProfessor->cpf, '###.###.###-##') ,
	        'funcao' => $obProfessor->funcao,
	        'dataNasc' => date('Y-m-d', strtotime($obProfessor->dataNasc)),
	        'selectedStatusA' => $obProfessor->status == 1 ? 'selected' : '',
	        'selectedStatusI' => $obProfessor->status == 0 ? 'selected' : '',
	        'optionBairros' => EntityBairro::getSelectBairros($obProfessor->bairro),
	        'optionDisciplinas' => EntityDisciplina::getSelectDisciplinas(null),
	        'email' => $obProfessor->email,
	        'escondeBotaoAcesso' => '',
	        'readonly' => '',
	        'itensDisciplina' => self::getProfessorDisciplinaItems($request, $obPagination, $obProfessor->id),
	        'adicionarDisciplina' => '',
	        'optionStatus' => EntityStatus::getSelectStatus($obProfessor->status),
	        'foto' => $obProfessor->foto.'?var='.$reload,
	        'ponteiro' => ''
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Editar Professor > Cursinho', $content,'professores', self::$buscaRapidaPront);
	    
	}
	
	//Metodo responsável por gravar a atualização de um Funcionário
	public static function setEditProfessor($request,$id){
	    
	    //obtém o funcionário do banco de dados
	    $obProfessor = EntityProfessor::getProfessorById($id);
	    
	    //Valida a instancia
	    if(!$obProfessor instanceof EntityProfessor){
	        $request->getRouter()->redirect('/admin/professores');
	    }
	    
	    //Post Vars
	    $postVars = $request->getPostVars();
	    
	    //instancia classe pra verificar CPF
	    $validaCpf = new CPF($postVars['cpf']);
	    
	    //obtém o funcionário pelo CPF (apenas números)
	    $obProfessorCPF = EntityProfessor::getProfessorByCPF($validaCpf->getValue());
	    
	    //verifica se o CPF já está sendo usado por outro PRofessor
	    if($obProfessorCPF instanceof EntityProfessor && $obProfessorCPF->id != $id){
	        $request->getRouter()->redirect('/admin/professores/'.$id.'/edit?statusMessage=cpfDuplicated');
	    }
	    
	    //Valida o email do usuário
	    $obProfessorEmail = EntityProfessor::getProfessorByEmail($postVars['email']);
	    
	    
	    //verifica se o E-MAIL já está sendo usado por outro usuário
	    if($obProfessorEmail instanceof EntityProfessor && $obProfessorEmail->id != $id){
	        $request->getRouter()->redirect('/admin/professores/'.$id.'/edit?statusMessage=emailDuplicated');
	    }
	    
	    
	    
	    //Atualiza a instância
	    $obProfessor->nome = Funcoes::convertePriMaiuscula($postVars['nome']) ?? $obProfessor->nome;
	    $obProfessor->cep = $postVars['cep'] ?? $obProfessor->cep;
	    $obProfessor->endereco = $postVars['endereco'] ?? $obProfessor->endereco;
	    $obProfessor->bairro =  $postVars['bairro'] ?? $obProfessor->bairro;
	    $obProfessor->cidade = Funcoes::convertePriMaiuscula($postVars['cidade']) ?? $obProfessor->cidade;
	    $obProfessor->uf = Funcoes::convertePriMaiuscula($postVars['uf']) ?? $obProfessor->uf;
	    $obProfessor->funcao = Funcoes::convertePriMaiuscula($postVars['funcao']) ?? $obProfessor->funcao;
	    $obProfessor->dataNasc = implode("-",array_reverse(explode("/",$postVars['dataNasc'])));
	    $obProfessor->cpf = $validaCpf->getValue(); //cpf sem formatação
	    $obProfessor->fone = str_replace('-', '', $postVars['fone']) ?? $obProfessor->fone;
	    $obProfessor->status = $postVars['status'] ?? $obProfessor->status;
	    $obProfessor->email = $postVars['email'] ?? $obProfessor->email;
	    $obProfessor->atualizar();
	    
	    //	Logs::setNewLog($request);
	    
	    //Redireciona o usuário
	    $request->getRouter()->redirect('/admin/professores/'.$obProfessor->id.'/edit?statusMessage=updated');
	    
	}
	


	
	
	
	

	
	
	
	//Método responsavel por obter a rendereizacao as Disciplinas do professor
	private static function getProfessorDisciplinaItems($request, &$obPagination, $id){
	    $resultados = '';
	    //Pagina Atual
	    $queryParams = $request->getQueryParams();
	    $paginaAtual = $queryParams['page'] ?? 1;
	    
	    //Quantidade total de registros
	    // $quantidadeTotal = EntityPaciente::getPacientes($where, null,null,'COUNT(*) as qtd')->fetchObject()->qtd;
	    $where = 'idProfessor = '.$id;
	    self::$qtdTotal = EntityDisciplinaProfessor::getDisciplinasProfessor($where, null,null,'COUNT(*) as qtd')->fetchObject()->qtd;
	    
	    //Instancia de paginação
	    $obPagination = new Pagination(self::$qtdTotal,$paginaAtual,5);
	    #############################################
	    
	    //Verifica se existe pesquisa, se sim, ordena pelo ulltimo pac cadastrado, se nao, ordena pelo Prontuário
	    $order = 'id' ;
	    //Obtem os pacientes
	    $results = EntityDisciplinaProfessor::getDisciplinasProfessor($where, $order, $obPagination->getLimit());
	   
	    //Renderiza
	    while ($obProfessorDisciplina = $results -> fetchObject(EntityDisciplinaProfessor::class)) {
	        
	        //View de pacientes
	        $resultados .= View::render('admin/modules/professores/itemDisciplina',[
	            'nome' =>EntityDisciplina::getDisciplinaById($obProfessorDisciplina->idDisciplina)->nome,
	            'idDisciplina' => $obProfessorDisciplina->id,
	            'idProfessor' =>$obProfessorDisciplina->idProfessor
	        ]);
	    }
	    return $resultados;
	}
	
	
	
	//Metodo responsávelpor retornar o formulário de Exclusão de um Paciente
	public static function getDeleteProfessor($request,$id){
	    
		//obtém o profissional do banco de dados
	    $obProfessor = EntityProfessor::getProfessorById($id);
		
		//Valida a instancia
		if(!$obProfessor instanceof EntityProfessor){
			$request->getRouter()->redirect('/admin/professores');
		}
		
		
		//Conteúdo do Formulário
		$content = View::render('admin/modules/professores/delete',[
		    'nome' => $obProfessor->nome,
		    'title' => 'Professor > Excluir'
			
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Excluir Professor > Cursinho', $content,'professores', self::$buscaRapidaPront);
		
	}
	
	//Metodo responsável por Excluir um Paciente
	public static function setDeleteProfessor($request,$id){
		
		//obtém o paciente do banco de dados
	    $obProfessor = EntityProfessor::getProfessorById($id);
		
		//Valida a instancia
	    if(!$obProfessor instanceof EntityProfessor){
			$request->getRouter()->redirect('/admin/professores');
		}
		
		//Exclui o professor
		$obProfessor->excluir();
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/admin/professores?statusMessage=deleted');
		
		
	}
	
	
	
	//Metodo responsávelpor retornar o formulário de Captura de foto do aluno
	public static function getPhotoProfessor($request,$id){
	    
	    $obProfessor = EntityProfessor::getProfessorById($id);
	    
	    //Conteúdo do Formulário
	    $content = View::render('admin/modules/alunos/formPhoto',[
	        'title' => 'Professores > Capturar foto',
	        'aluno' => '',
	        'id' => $obProfessor->id
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Professores > Cursinho', $content,'professores', self::$buscaRapidaPront);
	    
	}
	
	
	//Metodo responsávelpor retornar o formulário de Captura de foto do aluno
	public static function setPhotoProfessor($request){
	    
	    //Post Vars
	    $postVars = $request->getPostVars();
	    $fileVars = $request->getFileVars();
	    
	    
	    $obProfessor = EntityProfessor::getProfessorById($postVars['id']);
	    
	    if(!empty($fileVars['fImage']['name'] != '')){
	        $postVars['image'] = '';
	        
	        Upload::setUploadImagesProfessor($request);
	        //Redireciona o usuário
	        $request->getRouter()->redirect('/admin/professores/'.$obProfessor->id.'/edit?statusMessage=updated');
	    }
	    
	    if ($postVars['image'] != ''){
	        
	        $img = $postVars['image'];
	        $folderPath = __DIR__."/File/files/fotos/";
	        $image_parts = explode(";base64,", $img);
	        $image_type_aux = explode("image/", $image_parts[0]);
	        $image_type = $image_type_aux[1];
	        $image_base64 = base64_decode($image_parts[1]);
	        $nome =  str_replace(' ', '',$obProfessor->nome);
	        $id = $obProfessor->id;
	        $fileName = $id.$nome . '.png';
	        $obProfessor->foto = $fileName;
	        $obProfessor->atualizar();
	        $file = $folderPath . $fileName;
	        file_put_contents($file, $image_base64);
	        chmod($file, 0777); //Corrige a permissão do arquivo.
	        
	        $img = new Resize();
	        $config = array();
	        $config['source_image'] = $file;
	        $config['width'] = 195;
	        $config['height'] = 230;
	        $img->initialize($config);
	        $img->crop();
	        
	        
	        
	        
	        
	        //Redireciona o usuário
	        $request->getRouter()->redirect('/admin/professores/'.$obProfessor->id.'/edit?statusMessage=updated');
	    }
	    
	    $request->getRouter()->redirect('/admin/professores/'.$obProfessor->id.'/edit?statusMessage=semfoto');
	    
	    
	}
	
	//Método responsavel por Adicionar disciplina ao professor
	private static function setDisciplinaAdd($request,$id){
	    //Query PArams
	    $queryParams = $request->getQueryParams();
	    
	    //Status
	    if(!isset($queryParams['disciplina'])) return '';
	    
	    //obtém o Disciplina do banco de dados
	    $obDisciplina = EntityDisciplina::getDisciplinaById($queryParams['disciplina']);
	    
	    $obDisciplinaProfessor = new EntityDisciplinaProfessor();
	    $obDisciplinaProfessor->idProfessor = $id;
	    $obDisciplinaProfessor->idDisciplina = $obDisciplina->id;
	    $obDisciplinaProfessor->cadastrar();
	    $request->getRouter()->redirect('/admin/professores/'.$id.'/edit');
	}
	
	//Método responsavel por remover disciplina do professor
	private static function setDisciplinaRemove($request,$id){
	    //Query PArams
	    $queryParams = $request->getQueryParams();
	    
	    //Status
	    if(!isset($queryParams['removeDisciplina'])) return '';
	    
	    //obtém o Disciplina do banco de dados
	    $obDisciplinaProfessor = EntityDisciplinaProfessor::getDisciplinaProfessorById($queryParams['removeDisciplina']);
	    $obDisciplinaProfessor->excluir();
	    $request->getRouter()->redirect('/admin/professores/'.$id.'/edit');
	}
	
}