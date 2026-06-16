<?php

namespace App\Controller\Painel;

use \App\Utils\View;
use \WilliamCosta\DatabaseManager\Pagination;

use \App\Model\Entity\Aula as EntityAula;
use \App\Model\Entity\Turma as EntityTurma;
use \App\Model\Entity\Professor as EntityProfessor;
use \App\Model\Entity\Disciplina as EntityDisciplina;
use \App\Model\Entity\Aluno as EntityAluno;
use \App\Model\Entity\Frequencia as EntityFrequencia;
use \App\Model\Entity\InativacaoAluno as EntityInativacaoAluno;
use \App\Model\Entity\Status as EntityStatus;
use \App\Utils\Funcoes;
use Bissolli\ValidadorCpfCnpj\CPF;

class Frequencia extends Page{
	
	//Armazena quantidade total de pacientes listados
	private static $qtdTotal ;
	private static $totalGeralBpac = 0;
	//esconde busca rápida de prontuário no navBar
	private static $hidden = '';

	private static function getOrigemAlunoId($request, $idAluno = null){
	    $queryParams = $request->getQueryParams();
	    $origemAluno = (int)($queryParams['origemAluno'] ?? 0);

	    if($origemAluno <= 0){
	        return 0;
	    }

	    if(!is_null($idAluno) && $origemAluno !== (int)$idAluno){
	        return 0;
	    }

	    $obAluno = EntityAluno::getAlunoById($origemAluno);

	    return $obAluno instanceof EntityAluno ? $origemAluno : 0;
	}

	private static function getVoltarUrlIndividual($idAula, $origemAluno = 0){
	    if((int)$origemAluno > 0){
	        return URL.'/alunos/'.(int)$origemAluno.'/edit';
	    }

	    return URL.'/frequencias/'.(int)$idAula.'/edit';
	}

	private static function getFormActionIndividual($idAula, $idAluno, $origemAluno = 0){
	    $url = URL.'/frequencias/'.(int)$idAula.'/edit/individual/'.(int)$idAluno;

	    if((int)$origemAluno > 0){
	        $url .= '?origemAluno='.(int)$origemAluno;
	    }

	    return $url;
	}

	private static function getRedirectIndividual($idAula, $idAluno, $params = []){
	    $queryString = http_build_query(array_filter($params, function($value){
	        return $value !== null && $value !== '';
	    }));
	    $route = '/frequencias/'.(int)$idAula.'/edit/individual/'.(int)$idAluno;

	    return $queryString !== '' ? $route.'?'.$queryString : $route;
	}

	private static function getAlunoFrequenciaContext($request){
	    $queryParams = $request->getQueryParams();
	    $idAluno = (int)($queryParams['frequenciaAluno'] ?? 0);

	    if($idAluno <= 0){
	        return null;
	    }

	    $obAluno = EntityAluno::getAlunoById($idAluno);

	    return $obAluno instanceof EntityAluno ? $obAluno : null;
	}

	private static function reativarAlunoSeNecessario($idAluno){
	    $obAluno = EntityAluno::getAlunoById($idAluno);

	    if(!$obAluno instanceof EntityAluno || (int)$obAluno->status === 1){
	        return false;
	    }

	    $obAluno->status = 1;
	    $obAluno->atualizar();

	    return true;
	}

	private static function getLinkAulaAberta($idAula, $obAluno = null){
	    if($obAluno instanceof EntityAluno){
	        return URL.'/frequencias/'.(int)$idAula.'/edit/individual/'.(int)$obAluno->id.'?origemAluno='.(int)$obAluno->id;
	    }

	    return URL.'/frequencias/'.(int)$idAula.'/edit';
	}
	
	
	//Método responsavel por retornar a mensagem de status
	private static function getStatus($request){
	    //Query PArams
	    $queryParams = $request->getQueryParams();

	    if(!isset($queryParams['statusMessage'])){
	        $flashStatus = Funcoes::pullStatus();

	        if(!empty($flashStatus['statusMessage'])){
	            $queryParams = array_merge(
	                ['statusMessage' => $flashStatus['statusMessage']],
	                is_array($flashStatus['context'] ?? null) ? $flashStatus['context'] : []
	            );
	        }
	    }
	    
	    //Status
	    if(!isset($queryParams['statusMessage'])) return '';
	    
	    //Mensagens de status
	    switch ($queryParams['statusMessage']) {
	        case 'confirmed':
	            $attributes = [];
	            if(!empty($queryParams['toastRedirect'])){
	                $attributes['data-toast-redirect'] = $queryParams['toastRedirect'];
	            }
	            return Alert::getSuccess('Presença Registrada com sucesso!', $attributes);
	            break;
	        case 'jaconfirmed':
	            $attributes = [];
	            if(!empty($queryParams['toastRedirect'])){
	                $attributes['data-toast-redirect'] = $queryParams['toastRedirect'];
	            }
	            return Alert::getWarning('Presença Já Registrada!', $attributes);
	            break;
	        case 'updated':
	            return Alert::getSuccess('Aula atualizada com sucesso!');
	            break;
	        case 'deleted':
	            return Alert::getSuccess('Aula excluída com sucesso!');
	            break;
	        case 'error':
	            return Alert::getWarning('Aluno vinculado à aula selecionada.');
	            break;
	        case 'errorInativo':
	            return Alert::getWarning('Aluno reativado e vinculado à aula selecionada.');
	            break;
	
	    }
	}
	
	
	
	//Método responsavel por obter a rendereizacao dos pacientes para a página
	private static function getAulasItems($request, &$obPagination){
		$resultados = '';
		
		//Pagina Atual
		$queryParams = $request->getQueryParams();
		$paginaAtual = $queryParams['page'] ?? 1;

		//Filtro Status
		$filtroStatus = $queryParams['status'] ?? '';

		if (isset($queryParams['data']) && $queryParams['data'] != '' ){
			$filtroData = date('Y-m-d',strtotime($queryParams['data']));
		}else{
			$filtroData = '';
		}
		
		
		//Condições SQL
		$condicoes = [
				
		//		strlen((string)$profissional) ? 'idProfissional = '.$profissional.' ' : null,
				strlen((string)$filtroStatus) ? 'status = "'.$filtroStatus.'" ' : null,
				strlen((string)$filtroData) ? 'data = "'.$filtroData.'" ' : null
		];
		
		//Remove posições vazias
		$condicoes = array_filter($condicoes);
		
		//cláusula where
		$where = implode(' AND ', $condicoes);
	//	$where = 'id = 2 ';
	//var_dump($where);exit;
		//Quantidade total de registros
		// $quantidadeTotal = EntityPaciente::getPacientes($where, null,null,'COUNT(*) as qtd')->fetchObject()->qtd;
		
		self::$qtdTotal = EntityAula::getAulas($where, 'data DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
		
		//Instancia de paginação
		$obPagination = new Pagination(self::$qtdTotal,$paginaAtual,5);
		#############################################
		
		
		//Obtem os pacientes
		$results = EntityAula::getAulas($where, 'data DESC', $obPagination->getLimit());
		
		//Renderiza
		while ($obAula = $results -> fetchObject(EntityAula::class)) {
			
			//retorna a qtd de pacientes de cada agenda
		//	$qtdPacAgenda = EntityAgendaItems::getAgendaItems('idAgenda = '.$obAgenda->id.' ', 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;

			//View de Agendas
			$resultados .= View::render('painel/modules/aulas/item',[

			    'id' => $obAula->id,
			    'data' =>  date('d/m/Y', strtotime($obAula->data)),
			    'status' => EntityAula::getStatusAulaById($obAula->status)->nome,
			    'turma' => EntityTurma::getTurmaById($obAula->turma)->nome,
			    'presencas' => EntityFrequencia::getFrequencias('idAula = '.$obAula->id.' AND status = "P"', null,null,'COUNT(*) as qtd')->fetchObject()->qtd,
			    'faltas' => EntityFrequencia::getFrequencias('idAula = '.$obAula->id.' AND status = "F"', null,null,'COUNT(*) as qtd')->fetchObject()->qtd,
			    
			]);
		}
		//Retorna as agendas
		return $resultados;

	}
	
	

	//Método responsavel por renderizar a view de Listagem de Frequencias Abertas
	public static function getFrequencias($request){
		EntityInativacaoAluno::aplicarCriteriosAutomaticos();
		
		//Recebe os parâmetros da requisição
		$queryParams = $request->getQueryParams();
		$obAlunoFrequencia = self::getAlunoFrequenciaContext($request);
		$title = 'Frequências';
		$statusMessage = '';
		$voltarUrl = URL.'/dashboard';

		if($obAlunoFrequencia instanceof EntityAluno){
		    $title = 'Frequências > Selecionar aula aberta';
		    $statusMessage = Alert::getWarning('Selecione uma aula aberta para registrar a frequência de '.$obAlunoFrequencia->matricula.' - '.$obAlunoFrequencia->nome.'.');
		    $voltarUrl = URL.'/alunos/'.(int)$obAlunoFrequencia->id.'/edit';
		}
		
		$results = EntityAula::getAulas('status = 1','data DESC');
		$resultados = '';
		//Renderiza
		while ($obAula = $results -> fetchObject(EntityAula::class)) {
		    $obsAula = (string)($obAula->obs ?? '');
		    $aulaFrequenciaGeral = stripos($obsAula, 'frequência geral') !== false || stripos($obsAula, 'frequencia geral') !== false;
		      
		    $resultados .= View::render('painel/modules/frequencias/item',[
		        
                 'idAula' => $obAula->id,
		        'data' => date('d/m/Y',strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.EntityTurma::getTurmaById($obAula->turma)->nome,
		        'professoresInfo' => $aulaFrequenciaGeral ? 'Professores não definidos' : '',
		        'linkAula' => self::getLinkAulaAberta($obAula->id, $obAlunoFrequencia),
		        'tituloAula' => $obAlunoFrequencia instanceof EntityAluno ? 'Selecionar aula para frequência do aluno' : 'Clique para selecionar'
		    ]);
		}
		
		
		//Conteúdo da Home
		$content = View::render('painel/modules/frequencias/index',[
				 'title'=> $title,
		         'aulas' => $resultados,
		         'statusMessage' => $statusMessage,
		         'voltarUrl' => $voltarUrl,
		         'botaoFrequenciaGeral' => $obAlunoFrequencia instanceof EntityAluno ? 'hidden' : '',
		         'frequenciaLayoutClass' => $obAlunoFrequencia instanceof EntityAluno ? 'frequencias-open-grid-single' : ''
				
				 
		    
		    
		    
		]);
		
		//Retorna a página completa
		return parent::getPanel('Frequências > Cursinho', $content,'frequencias', 'hidden');
	}

	public static function getFrequenciaGenerica($request){
	    $content = View::render('/pages/frequenciaqrcode/index',[

	        'title'=> 'Frequência Geral',
	        'aula' => 'Frequência Geral',
	        'idAula' => 0,
	        'aulaGenerica' => 1

	    ]);

	    return parent::getPage('Frêquencias > Cursinho', $content,'frequencias', 'hidden');
	}
	
	
	//Método responsavel por renderizar a view de Nova Aula
	public static function getFrequenciaEdit($request,$id){
	   
	    $obAula = EntityAula::getAulaById($id);
	    
	    Funcoes::init();
	    $_SESSION['idAula'] = $id;
	    
	    //Conteúdo da Home
	    $content = View::render('painel/modules/frequencias/form',[
	        'title' => 'Frequências > Editar',
	        'aula' =>'Aula do dia: ' .date('d/m/Y',strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.EntityTurma::getTurmaById($obAula->turma)->nome,
	        'idAula' => $obAula->id,
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Frequências > Cursinho', $content,'frequencias', 'hidden');
	}
	
	
	//Método RESPONSÁVEL POR REATIVAR O ALUNO NA FREQUÊNCIA DESKTOP
	public static function setFrequenciaReactiveAluno($request,$id, $idMatricula){
	  
	    //BUSCA O ALUNO E O REATIVA
	    $aluno = EntityAluno::getAlunoByMatricula($idMatricula);
	    $aluno -> status = 1;
	    $aluno -> atualizar();
	    
	    //GERA NOVAMENTE A FREQUENCIA GERAL DESKTOP
	    $obAula = EntityAula::getAulaById($id);
	    
	    Funcoes::init();
	    $_SESSION['idAula'] = $id;
	    $content = View::render('/pages/frequenciaqrcode/index',[
	        
	        'title'=> 'Frequência Geral',
	        'aula' =>'Aula do dia: ' .date('d/m/Y',strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.EntityTurma::getTurmaById($obAula->turma)->nome,
	        'idAula' => $obAula->id,
	        'aulaGenerica' => 0
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPage('Frêquencias > Cursinho', $content,'frequencias', 'hidden');
	}
	
	//Método RESPONSÁVEL POR REATIVAR O ALUNO NA FREQUÊNCIA MOBILE
	public static function setFrequenciaReactiveMobileAluno($request,$id, $idMatricula){
	    
	    //BUSCA O ALUNO E O REATIVA
	    $aluno = EntityAluno::getAlunoByMatricula($idMatricula);
	    $aluno -> status = 1;
	    $aluno -> atualizar();
	    
	    //GERA NOVAMENTE A FREQUENCIA GERAL DESKTOP
	    $obAula = EntityAula::getAulaById($id);
	    
	    Funcoes::init();
	    $_SESSION['idAula'] = $id;
	    //Conteúdo da Home
	    $content = View::render('/pages/frequenciaqrcode/indexMobile',[
	        
	        'title'=> 'Frequência Geral',
	        'aula' =>'Aula do dia: ' .date('d/m/Y',strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.EntityTurma::getTurmaById($obAula->turma)->nome,
	        'idAula' => $obAula->id,
	        'aulaGenerica' => 0
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPage('Frêquencias > Cursinho', $content,'frequencias', 'hidden');
	}
	
	//Método responsavel por renderizar a view de Nova Aula
	public static function getFrequenciaEditIndividual($request,$id){
	    
	    $obAula = EntityAula::getAulaById($id);
	    
	    
	    //Conteúdo da Home
	    $content = View::render('painel/modules/frequencias/formIndividual',[
	        'title' => 'Frequências > Editar',
	        'aula' =>'Aula do dia: ' .date('d/m/Y',strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.EntityTurma::getTurmaById($obAula->turma)->nome,
	        'id' => $obAula->id,
	        //'matricula' => EntityAluno::getAlunoById($idAluno)->matricula ?? '',
	        'matricula' => '',
	        'nome' => '',
	        'idAluno' => '',
	        'turma' => '',
	        'escondeBotaoConfirmar' => 'hidden',
	        'classebtn' => 'facebook',
	        'status' =>'',
	        'statusClasse' => 'bg-slate-500',
	        'idAula' => $obAula->id,
	        'statusMessage'=> '',
	        'foto' => EntityAluno::FOTO_PADRAO,
	        'voltarUrl' => self::getVoltarUrlIndividual($obAula->id),
	        'formAction' => URL.'/frequencias/'.$obAula->id.'/edit/individual'
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Frequências > Cursinho', $content,'frequencias', 'hidden');
	}
	
	//Método responsavel por renderizar a view de Nova Aula
	public static function getFrequenciaEditIndividualSelect($request,$id,$idAluno){
	    
	    $obAula = EntityAula::getAulaById($id);
	    if(!$obAula instanceof EntityAula){
	        //Redireciona
	        $request->getRouter()->redirect('/frequencias');
	    }

	    $obAluno = EntityAluno::getAlunoById($idAluno);
	    if(!$obAluno instanceof EntityAluno){
	        //Redireciona
	        $request->getRouter()->redirect('/frequencias');
	    }

	    $obTurma = EntityTurma::getTurmaById($obAluno->turma);
	    $obStatus = EntityStatus::getStatusById($obAluno->status);
	    $origemAluno = self::getOrigemAlunoId($request, $idAluno);
	    
	    
	    //Conteúdo da Home
	    $content = View::render('painel/modules/frequencias/formIndividual',[
	        'title' => 'Frequências > Editar',
	        'aula' =>'Aula do dia: ' .date('d/m/Y',strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.EntityTurma::getTurmaById($obAula->turma)->nome,
	        'id' => $obAula->id,
	        'matricula' => $obAluno->matricula,
	        'nome' => $obAluno->nome,
	        'idAluno' => $idAluno,
	        'turma' => $obTurma ? $obTurma->nome : '',
	        'escondeBotaoConfirmar' => '',
	        'statusMessage' => self::getStatus($request),
	        'status' => $obStatus ? $obStatus->nome : '',
	        'statusClasse' => (int)$obAluno->status === 1 ? 'bg-green-600' : 'bg-red-600',
	        'foto' => $obAluno->getFoto(),
	        'idAula' => $obAula->id,
	        'voltarUrl' => self::getVoltarUrlIndividual($obAula->id, $origemAluno),
	        'formAction' => self::getFormActionIndividual($obAula->id, $idAluno, $origemAluno)
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Frequências > Cursinho', $content,'frequencias', 'hidden');
	}
	
	//Método responsavel por renderizar a view de Nova Aula
	public static function getFrequenciaEditIndividualSelectPresenca($request,$id,$idAluno){
	    $origemAluno = self::getOrigemAlunoId($request, $idAluno);
	  
	    //verifica se a sessao não está ativa
	    if(session_status() != PHP_SESSION_ACTIVE ){
	        session_start();
	    }
	    $user = $_SESSION['usuario']['id'];
	    self::reativarAlunoSeNecessario($idAluno);
	    
	    //obtém a aula
	    $obAula = EntityAula::getAulaById($id);
	    //obtem a frequencia
	    $obFreq = EntityFrequencia::getFrequencias('idAula = '.$id.' AND idAluno = '.$idAluno) -> fetchObject(EntityFrequencia::class);
	    if(!$obFreq instanceof EntityFrequencia){
	        $obFreq = EntityFrequencia::garantirVinculoAlunoAula($id, $idAluno, $user);
	    }

	    if($obFreq instanceof EntityFrequencia){
	        if($obFreq->status == 'P'){
	            $params = [
	                'origemAluno' => $origemAluno,
	                'statusMessage' => 'jaconfirmed'
	            ];

	            if($origemAluno > 0){
	                $params['toastRedirect'] = self::getVoltarUrlIndividual($id, $origemAluno);
	            }

	            $request->getRouter()->redirect(self::getRedirectIndividual($id, $idAluno, $params));
	        }

	        $obFreq->status = 'P';
	        $obFreq->autor = $user;
	        $obFreq->atualizar();
	    }else{
	        $request->getRouter()->redirect(self::getRedirectIndividual($id, $idAluno, [
	            'origemAluno' => $origemAluno,
	            'statusMessage' => 'error'
	        ]));
	    }
	    
	    
	    
	    Funcoes::flashStatus('confirmed', [
	        'toastRedirect' => self::getVoltarUrlIndividual($id, $origemAluno)
	    ]);
	    $request->getRouter()->redirect(self::getRedirectIndividual($id, $idAluno, [
	        'origemAluno' => $origemAluno
	    ]));
	    
	   
	}
	
	
	//Método responsavel por obter a rendereizacao dos Alunos para a página
	private static function getAlunoItems($request, $idAula){
	    
	    $resultados = '';
	    
	    $queryParams = $request->getQueryParams();
	    
	    $busca = trim((string)($queryParams['busca'] ?? ''));
	    
	    //Armazena valor busca pelo nome do paciente
	    $nome = $queryParams['nome'] ?? '';
	    
	    $id = $queryParams['id'] ?? '';
	    
	    $turma = $queryParams['turma'] ?? '';
	    
	    //recebe a matrícula vindo do form de pesquisa ou da Navbar
	    $matricula = $queryParams['matricula'] ?? '';
	    
	    $status = $queryParams['status'] ?? '';
	    
	    If(@$queryParams['cpfPesq'] != ''){
	        
	        $cpf = $queryParams['cpfPesq'] ?? '';
	        
	        //instancia classe pra verificar CPF
	        $validaCpf = new CPF($cpf);
	        
	        //verifica se é válido o cpf
	        if (!$validaCpf->isValid()){
	            $request->getRouter()->redirect('/alunos?statusMessage=cpfInvalid');
	        }
	        //ARMAZENA O CPF (SOMENTE OS NÚMEROS)
	        $cpf= $validaCpf->getValue();
	    }else{$cpf = null;}
	    
	    //retira zeros à esquerda
	    //if($pront != '') $pront += 0;
	    
	    //Condições SQL
	    $buscaSql = addslashes(str_replace(' ', '%', $busca));
	    $nomeSql = addslashes(str_replace(' ', '%', $nome));
	    $matriculaSql = addslashes($matricula);

	    $condicoes = [
	        
	        strlen((string)$busca) ? '(matricula LIKE "%'.$buscaSql.'%" OR nome LIKE "%'.$buscaSql.'%")' : null,
	        strlen((string)$nome) ? 'nome LIKE "%'.$nomeSql.'%"' : null,
	        strlen((string)$id) ? 'id = "'.$id.'"' : null,
	        strlen((string)$turma) ? 'turma = "'.$turma.'"' : null,
	        strlen((string)$matricula) ? 'matricula = "'.$matriculaSql.'"' : null,
	        strlen((string)$status) ? 'status = "'.$status.'" ' : null,
	        strlen((string)$cpf) ? 'cpf = "'.$cpf.'" ' : null,
	    ];
	    
	    //Remove posições vazias
	    $condicoes = array_filter($condicoes);
	    
	    //cláusula where
	    $where = implode(' AND ', $condicoes);
	    
	    
	    //Quantidade total de registros
	    // $quantidadeTotal = EntityPaciente::getPacientes($where, null,null,'COUNT(*) as qtd')->fetchObject()->qtd;
	    
	    self::$qtdTotal = EntityAluno::getAlunos($where, null,null,'COUNT(*) as qtd')->fetchObject()->qtd;
	    
	    //Verifica se existe pesquisa, se sim, ordena pelo ulltimo pac cadastrado, se nao, ordena pelo Prontuário
	    $order = 'id DESC' ;
	    
	    //Obtem os pacientes
	    $results = EntityAluno::getAlunos($where, $order);
	    //Renderiza
	    while ($obAluno = $results -> fetchObject(EntityAluno::class)) {
	        $obStatus = EntityStatus::getStatusById((int)$obAluno->status);
	        $obTurma = EntityTurma::getTurmaById((int)$obAluno->turma);
	        $statusCor = (int)$obAluno->status === 1 ? 'bg-gradient-success' : 'bg-gradient-danger';
	        
	        //View de pacientes
	        $resultados .= View::render('painel/modules/frequencias/itemPesquisa',[
	            'nome' => $obAluno->nome,
	            'status' => $obStatus ? $obStatus->nome : 'Sem status',
	            'statusCor' => $statusCor,
	            'cpf' => Funcoes::mask($obAluno->cpf, '###.###.###-##') ,
	            'idAluno' => $obAluno->id,
	            'matricula' => $obAluno->matricula,
	            'turma' => $obTurma ? $obTurma->nome : 'Sem turma',
	            'idAula' => $idAula,
	            'foto' => $obAluno->getFoto(),
	        ]);
	        
	    }
	    
	    //Retorna os pacientes
	    return $resultados;
	}
	
	
	//Método responsavel por renderizar a view de Listagem de Pacientes
	public static function getFrequenciaEditPesquisa($request,$idAula){
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
		    //Conteúdo da Home
	    $content = View::render('painel/modules/frequencias/indexPesquisa',[
	        'title' => 'Alunos > Pesquisa para frequência individual',
	        'itens' => self::getAlunoItems($request,$idAula),
	        'statusMessage' => self::getStatus($request),
	        'busca' =>  $queryParams['busca'] ?? '',
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
	    return parent::getPanel('Frequências > Cursinho', $content,'frequencias', self::$hidden);
	    
	}
	
	
	
	
//FREQUENCIA GERAL
	
	
	////MÉTODO RESPONSÁVEL POR RENDERIZAR FREQUENCIA GERAL COM QRCODE NO DESKTOP
	public static function getFrequenciaGeral($request){
	    //Post vars
	    $postVars = $request->getPostVars();
	    
	    
	    $obAula = EntityAula::getAulaById($postVars['idAula']);
	    if(!$obAula instanceof EntityAula){
	        //Redireciona
	        $request->getRouter()->redirect('/frequencias');
	    }
	    
			//Conteúdo da Home ok
			$content = View::render('/pages/frequenciaqrcode/index',[
			  
			   'title'=> 'Frequência Geral',
			    'aula' =>'Aula do dia: ' .date('d/m/Y',strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.EntityTurma::getTurmaById($obAula->turma)->nome,
			    'idAula' => $obAula->id,
			    'aulaGenerica' => 0
					
			]);
			
			//Retorna a página completa
			return parent::getPage('Frêquencias > Cursinho', $content,'frequencias', 'hidden');
	}
	
	//MÉTODO RESPONSÁVEL POR RENDERIZAR FREQUENCIA GERAL COM QRCODE NO CELULAR
	public static function getFrequenciaGeralMobile($request,$id){
	    //Post vars
	  //  $postVars = $request->getPostVars();
	    
	    
	    $obAula = EntityAula::getAulaById($id);
	    if(!$obAula instanceof EntityAula){
	        //Redireciona
	        $request->getRouter()->redirect('/frequencias');
	    }
	    
	    //Conteúdo da Home
	    $content = View::render('/pages/frequenciaqrcode/indexMobile',[
	        
	        'title'=> 'Frequência Geral',
	        'aula' =>'Aula do dia: ' .date('d/m/Y',strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.EntityTurma::getTurmaById($obAula->turma)->nome,
	        'idAula' => $obAula->id,
	        'aulaGenerica' => 0
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPage('Frêquencias > Cursinho', $content,'frequencias', 'hidden');
	}
	
}
