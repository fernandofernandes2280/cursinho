<?php

namespace App\Controller\Admin;

use \App\Utils\View;
use \WilliamCosta\DatabaseManager\Pagination;

use \App\Model\Entity\Aula as EntityAula;
use \App\Model\Entity\Turma as EntityTurma;
use \App\Model\Entity\Professor as EntityProfessor;
use \App\Model\Entity\Disciplina as EntityDisciplina;
use \App\Model\Entity\Aluno as EntityAluno;
use \App\Model\Entity\Frequencia as EntityFrequencia;
use \App\Model\Entity\Status as EntityStatus;
use \App\Utils\Funcoes;

class Frequencia extends Page{
	
	//Armazena quantidade total de pacientes listados
	private static $qtdTotal ;
	private static $totalGeralBpac = 0;
	//esconde busca rápida de prontuário no navBar
	private static $hidden = '';
	
	
	//Método responsavel por retornar a mensagem de status
	private static function getStatus($request){
	    //Query PArams
	    $queryParams = $request->getQueryParams();
	    
	    //Status
	    if(!isset($queryParams['statusMessage'])) return '';
	    
	    //Mensagens de status
	    switch ($queryParams['statusMessage']) {
	        case 'confirmed':
	            return Alert::getSuccess('Presença Registrada com sucesso!');
	            break;
	        case 'jaconfirmed':
	            return Alert::getWarning('Presença Já Registrada!');
	            break;
	        case 'updated':
	            return Alert::getSuccess('Aula atualizada com sucesso!');
	            break;
	        case 'deleted':
	            return Alert::getSuccess('Aula excluída com sucesso!');
	            break;
	        case 'error':
	            return Alert::getError('Turma do aluno diferente!');
	            break;
	        case 'errorInativo':
	            return Alert::getError('Aluno Inativo! Presença não confirmada!');
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
				
		//		strlen($profissional) ? 'idProfissional = '.$profissional.' ' : null,
				strlen($filtroStatus) ? 'status = "'.$filtroStatus.'" ' : null,
				strlen($filtroData) ? 'data = "'.$filtroData.'" ' : null
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
			$resultados .= View::render('admin/modules/aulas/item',[

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
		
		//Recebe os parâmetros da requisição
		$queryParams = $request->getQueryParams();
		
		$results = EntityAula::getAulas('status = 1','data DESC');
		$resultados = '';
		//Renderiza
		while ($obAula = $results -> fetchObject(EntityAula::class)) {
		      
		    $resultados .= View::render('admin/modules/frequencias/item',[
		        
                 'idAula' => $obAula->id,
		        'data' => date('d/m/Y',strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.EntityTurma::getTurmaById($obAula->turma)->nome
		    ]);
		}
		
		
		//Conteúdo da Home
		$content = View::render('admin/modules/frequencias/index',[
				 'title'=> 'Frequências > Aulas Abertas',
		         'aulas' => $resultados
				
				 
		    
		    
		    
		]);
		
		//Retorna a página completa
		return parent::getPanel('Frequências > Cursinho', $content,'frequencias', 'hidden');
	}
	
	
	//Método responsavel por renderizar a view de Nova Aula
	public static function getFrequenciaEdit($request,$id){
	   
	    $obAula = EntityAula::getAulaById($id);
	    
	    
	    //Conteúdo da Home
	    $content = View::render('admin/modules/frequencias/form',[
	        'title' => 'Frequências > Editar',
	        'aula' =>'Aula do dia: ' .date('d/m/Y',strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.EntityTurma::getTurmaById($obAula->turma)->nome,
	        'idAula' => $obAula->id,
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Frequências > Cursinho', $content,'frequencias', 'hidden');
	}
	
	//Método responsavel por renderizar a view de Nova Aula
	public static function getFrequenciaEditIndividual($request,$id){
	    
	    $obAula = EntityAula::getAulaById($id);
	    
	    
	    //Conteúdo da Home
	    $content = View::render('admin/modules/frequencias/formIndividual',[
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
	        'idAula' => $obAula->id,
	        'statusMessage'=> '',
	        'foto' => 'profile.png'
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Frequências > Cursinho', $content,'frequencias', 'hidden');
	}
	
	//Método responsavel por renderizar a view de Nova Aula
	public static function getFrequenciaEditIndividualSelect($request,$id,$idAluno){
	    
	    $obAula = EntityAula::getAulaById($id);
	    if(!$obAula instanceof EntityAula){
	        //Redireciona
	        $request->getRouter()->redirect('/admin/frequencias');
	    }

	    $obAluno = EntityAluno::getAlunoById($idAluno);
	    if(!$obAluno instanceof EntityAluno){
	        //Redireciona
	        $request->getRouter()->redirect('/admin/frequencias');
	    }
	    
	    
	    //Conteúdo da Home
	    $content = View::render('admin/modules/frequencias/formIndividual',[
	        'title' => 'Frequências > Editar',
	        'aula' =>'Aula do dia: ' .date('d/m/Y',strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.EntityTurma::getTurmaById($obAula->turma)->nome,
	        'id' => $obAula->id,
	        'matricula' => EntityAluno::getAlunoById($idAluno)->matricula,
	        'nome' => EntityAluno::getAlunoById($idAluno)->nome,
	        'idAluno' => $idAluno,
	        'turma' => EntityTurma::getTurmaById(EntityAluno::getAlunoById($idAluno)->turma)->nome, 
	        'escondeBotaoConfirmar' => '',
	        'statusMessage' => self::getStatus($request),
	        'status' =>EntityStatus::getStatusById(EntityAluno::getAlunoById($idAluno)->status)->nome,
	        'foto' => $obAluno->foto,
	        'idAula' => $obAula->id
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Frequências > Cursinho', $content,'frequencias', 'hidden');
	}
	
	//Método responsavel por renderizar a view de Nova Aula
	public static function getFrequenciaEditIndividualSelectPresenca($request,$id,$idAluno){
	    
	    //Verifica se o aluno está inativo
	    if(EntityAluno::getAlunoById($idAluno)->status == 2){
	        $request->getRouter()->redirect('/admin/frequencias/'.$id.'/edit/individual/'.$idAluno.'?statusMessage=errorInativo');
	    }
	    
	    //obtém a aula
	    $obAula = EntityAula::getAulaById($id);
	    //obtem a frequencia
	    $obFreq = EntityFrequencia::getFrequencias('idAula = '.$id.' AND idAluno = '.$idAluno) -> fetchObject(EntityFrequencia::class);
	   
	    //REGRA : O aluno pode frequentas as aulas em qualquer dia e turma
	    
	        //se nao for, verifica se o aluno é da mesma turma da a aula 
	        if($obFreq instanceof EntityFrequencia){
	            if($obFreq->status == 'P'){
	                $request->getRouter()->redirect('/admin/frequencias/'.$id.'/edit/individual/'.$idAluno.'?statusMessage=jaconfirmed');
	            }

	            $obFreq->status = 'P';
	            $obFreq->autor = 2; //id temporario do usuario logado para testes
	            $obFreq->atualizar();
	        
	    }else{
	        //se for sáb ou dom, cria nova instancia de frequencia e registra a presença do aluno
	        $frequencia = new EntityFrequencia();
	        $frequencia->idAluno = $idAluno;
	        $frequencia->idAula = $id;
	        $frequencia->status = 'P';
	        $frequencia->autor = 3; //id temporario do usuario logado para testes
	        $frequencia->cadastrar();
	    }
	    
	    
	    
	    $request->getRouter()->redirect('/admin/frequencias/'.$id.'/edit/individual/'.$idAluno.'?statusMessage=confirmed');
	    
	   
	}
	
	
	//Método responsavel por obter a rendereizacao dos Alunos para a página
	private static function getAlunoItems($request, &$obPagination, $idAula){
	    
	    $postVars = $request->getPostVars();
	    
	    $resultados = '';
	    
	    //Pagina Atual
	    $queryParams = $request->getQueryParams();
	    $paginaAtual = $queryParams['page'] ?? 1;
	    
	    
	    //Armazena valor busca pelo nome do paciente
	    $nome = $queryParams['nome'] ?? '';
	    
	    $id = $queryParams['id'] ?? '';
	    
	    $turma = $queryParams['turma'] ?? '';
	    
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
	        
	        //View de pacientes
	        $resultados .= View::render('admin/modules/frequencias/itemPesquisa',[
	            
	            'nome' => $obAluno->nome,
	            'status' =>EntityStatus::getStatusById($obAluno->status)->nome,
	            'cpf' => Funcoes::mask($obAluno->cpf, '###.###.###-##') ,
	            'idAluno' => $obAluno->id,
	            'matricula' => $obAluno->matricula,
	            'turma' =>EntityTurma::getTurmaById($obAluno->turma)->nome,
	            'idAula' => $idAula
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
	    
	    
	    
	    //oculta o botão excluir para usuário Operador
	    //	($_SESSION['admin']['usuario']['tipo'] == 'Operador' ? $botãoExcluir = 'hidden' : $botãoExcluir =  '' );
	    //Conteúdo da Home
	    $content = View::render('admin/modules/frequencias/indexPesquisa',[
	        'title' => 'Alunos > Pesquisa ',
	        'itens' => self::getAlunoItems($request,$obPagination,$idAula),
	        'pagination' => parent::getPagination($request, $obPagination),
	        'statusMessage' => self::getStatus($request),
	        'nome' =>  $queryParams['nome'] ?? '',
	        'matricula' =>  $queryParams['matricula'] ?? '',
	        'id' =>  $queryParams['id'] ?? '',
	        'matricula' =>  $queryParams['matricula'] ?? '',
	        'cpf' =>  $queryParams['cpf'] ?? '',
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
	
	
	//Método responsavel por renderizar a view de Nova Aula
	public static function getFrequenciaGeral($request){
	    //Post vars
	    $postVars = $request->getPostVars();
	    
	    
	    $obAula = EntityAula::getAulaById($postVars['idAula']);
	    if(!$obAula instanceof EntityAula){
	        //Redireciona
	        $request->getRouter()->redirect('/admin/frequencias');
	    }
	    
			//Conteúdo da Home
			$content = View::render('admin/modules/frequencias/geral/index',[
			  
			   'title'=> 'Frequência Geral',
			    'aula' =>'Aula do dia: ' .date('d/m/Y',strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.EntityTurma::getTurmaById($obAula->turma)->nome,
			    'idAula' => $obAula->id
					
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
	        $request->getRouter()->redirect('/admin/frequencias');
	    }
	    
	    //Conteúdo da Home
	    $content = View::render('admin/modules/frequencias/geral/indexMobile',[
	        
	        'title'=> 'Frequência Geral',
	        'aula' =>'Aula do dia: ' .date('d/m/Y',strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.EntityTurma::getTurmaById($obAula->turma)->nome,
	        'idAula' => $obAula->id
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPage('Frêquencias > Cursinho', $content,'frequencias', 'hidden');
	}
	
}

