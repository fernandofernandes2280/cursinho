<?php

namespace App\Controller\Admin;

use \App\Utils\View;
use \WilliamCosta\DatabaseManager\Pagination;
use \App\Model\Entity\Aula as EntityAula;
use \App\Model\Entity\Turma as EntityTurma;
use \App\Model\Entity\Professor as EntityProfessor;
use \App\Model\Entity\Disciplina as EntityDisciplina;
use \App\Model\Entity\DisciplinaProfessor as EntityDisciplinaProfessor;
use \App\Model\Entity\Aluno as EntityAluno;
use \App\Model\Entity\Frequencia as EntityFrequencia;
use \App\Model\Entity\StatusAula as EntityStatusAula;
use \App\Model\Entity\User as EntityUser;

class Aula extends Page{
	
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
	        case 'created':
	            return Alert::getSuccess('Aula criada com sucesso!');
	            break;
	        case 'updated':
	            return Alert::getSuccess('Aula atualizada com sucesso!');
	            break;
	        case 'deleted':
	            return Alert::getSuccess('Aula excluída com sucesso!');
	            break;
	        case 'duplicad':
	            return Alert::getError('Aula já Cadastrada!');
	            break;
	        case 'notFound':
	            return Alert::getError('Aula não encontrada!');
	            break;
	        case 'add':
	            return Alert::getSuccess('Paciente adicionado com sucesso!');
	            break;
	        case 'removed':
	            return Alert::getSuccess('Paciente removido com sucesso!');
	            break;
	        case 'alter':
	            return Alert::getSuccess('Alterações realizadas com sucesso!');
	            break;
	        case 'alterDuplo':
	            return Alert::getSuccess('Alterações realizadas com sucesso, exceto registros com mesmo atendimento!');
	            break;
	        case 'errorDate':
	            return Alert::getError('Aula não pode ser transferida para a mesma data!');
	            break;
	        case 'transfer':
	            return Alert::getSuccess('Aula transferida com sucesso!');
	            break;
	        case 'deletedfail':
	            return Alert::getError('Você não tem permissão para Excluir! Contate o administrador.');
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
		
		$turma = @$queryParams['turma'];
		
		//Condições SQL
		$condicoes = [
				
				strlen($turma) ? 'turma = '.$turma.' ' : null,
				strlen($filtroStatus) ? 'status = "'.$filtroStatus.'" ' : null,
				strlen($filtroData) ? 'data = "'.$filtroData.'" ' : null
		];
		
		//Remove posições vazias
		$condicoes = array_filter($condicoes);
		
		//cláusula where
		$where = implode(' AND ', $condicoes);

		
		self::$qtdTotal = EntityAula::getAulas($where, 'data DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
		
		//Instancia de paginação
		$obPagination = new Pagination(self::$qtdTotal,$paginaAtual,5);
		#############################################
		
		
		//Obtem os pacientes
		$results = EntityAula::getAulas($where, 'data DESC', $obPagination->getLimit());
		
		//Renderiza
		while ($obAula = $results -> fetchObject(EntityAula::class)) {
		    
		    if($obAula->status == 1) $cor = 'bg-gradient-success';
		    if($obAula->status == 2) $cor = 'bg-gradient-danger' ;
		    if($obAula->status == 3) $cor = 'bg-gradient-warning';
		    
			//View de Agendas
			$resultados .= View::render('admin/modules/aulas/item',[
			    
			    'id' => $obAula->id,
			    'data' =>  date('d/m/Y', strtotime($obAula->data)).' ( '.$obAula->diaSemana.' )',
			    'status' => EntityAula::getStatusAulaById($obAula->status)->nome,
			    'turma' => EntityTurma::getTurmaById($obAula->turma)->nome,
			    'presencas' => EntityFrequencia::getFrequencias('idAula = '.$obAula->id.' AND status = "P"', null,null,'COUNT(*) as qtd')->fetchObject()->qtd,
			    'faltas' => EntityFrequencia::getFrequencias('idAula = '.$obAula->id.' AND status = "F"', null,null,'COUNT(*) as qtd')->fetchObject()->qtd,
			    'cor' => $cor,
			    'autor' => EntityUser::getUserById($obAula->autor)->nome,
			    'professor1' => EntityProfessor::getProfessorById($obAula->professor1)->nome,
			    'disciplina1' => EntityDisciplina::getDisciplinaById($obAula->disciplina1)->nome,
			    'professor2' => EntityProfessor::getProfessorById($obAula->professor2)->nome,
			    'disciplina2' => EntityDisciplina::getDisciplinaById($obAula->disciplina2)->nome,
			    
			]);
		}
		//Retorna as agendas
		return $resultados;

	}
	
	

	//Método responsavel por renderizar a view de Listagem de Atendimentos
	public static function getAulas($request){
		
		//Recebe os parâmetros da requisição
		$queryParams = $request->getQueryParams();
		
		$idTurma = @$queryParams['turma'] ?? null; 
		$status = @$queryParams['status'] ?? null;
		$data = @$queryParams['data'];
		//Conteúdo da Home
		$content = View::render('admin/modules/aulas/index',[
				 'title'=> 'Aulas > Pesquisa',
				'itens' => self::getAulasItems($request,$obPagination),
				'pagination' => parent::getPagination($request, $obPagination),
				'totalAtendimentos' => self::$qtdTotal,
				'statusMessage' => self::getStatus($request),
		        'optionTurma' => EntityTurma::getSelectTurmas($idTurma),
		        'optionStatus' => EntityStatusAula::getSelectStatusAula($status),
				'acao' => 'Pesquisa',
				'data' => $data
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Aulas > Cursinho', $content,'aulas', 'hidden');
	}
	
	
	//Método responsavel por renderizar a view de Nova Aula
	public static function getAulasNew($request){
		
			//Conteúdo da Home
			$content = View::render('admin/modules/aulas/form',[
			         'title' => 'Aula > Nova',
					'statusMessage' => self::getStatus($request),
					'optionTurmas' => EntityTurma::getSelectTurmas(null),
			        'optionProfessores1' => EntityProfessor::getSelectProfessores(null),
			        'optionProfessores2' => EntityProfessor::getSelectProfessores(null),
			        'optionStatus' => EntityAula::getSelectStatusAula(null),
			    'obs' => '',
			    'dia' => ''
			   
					
			]);
			
			//Retorna a página completa
			return parent::getPanel('Aulas > Cursinho', $content,'aulas', 'hidden');
	}
	
	//Método responsável por salvar uma Aula no banco
	public static function setAulasNew($request){
		
		//Post vars
		$postVars = $request->getPostVars();
		
		$data = implode('-', array_reverse(explode('/', $postVars['data'])));
		
		//Nova instância de Agenda
		$obAula = new EntityAula();
		$obAula->data =$data;
		$obAula->professor1 = $postVars['professor1'];
		$obAula->disciplina1 = $postVars['disciplina1'];
		$obAula->professor2 = $postVars['professor2'];
		$obAula->disciplina2 = $postVars['disciplina2'];
		$obAula->status = $postVars['status'];
		$obAula->turma = $postVars['turma'];
		$obAula->obs = $postVars['obs'];
		$obAula->status = $postVars['status'];
		$obAula->diaSemana = $postVars['dia'];
		
		
		//Verifica se a agenda já está existe no banco de dados
		$duplicado = EntityAula::getAulaDuplicada(date('Y-m-d',strtotime($postVars['data'])), $postVars['turma']);
		
		
		if($duplicado instanceof EntityAula){
			//Redireciona o usuário em caso de existir
			$request->getRouter()->redirect('/admin/aulas?statusMessage=duplicad');
		}

		$obAula->cadastrar();
		
		
		//Preenche a Aulas com os alunos da turma correspondente, exceto se a aula for no Sáb ou Dom
		$diaSemana = array("SÁB", "DOM");
		if(!in_array($obAula->diaSemana,$diaSemana)){
		    $results = EntityAluno::getAlunos('turma  = '.$obAula->turma.' AND status = 1');
    		while ($obAlunos = $results -> fetchObject(EntityAluno::class)) {
    		    $frequencia = new EntityFrequencia();
    		    $frequencia->idAluno = $obAlunos->id;
    		    $frequencia->idAula = $obAula->id;
    		    $frequencia->status = 'F';
    		    $frequencia->autor = 1; //id temporario do usuario logado para testes
    		    $frequencia->cadastrar();
    		}
		}

		//Redireciona o usuário
		$request->getRouter()->redirect('/admin/aulas?statusMessage=created');
	}
	
	

	//Método responsavel por renderizar a view de Edição de Aula
	public static function getAulaEdit($request, $id){
			
		//obtém o Aula  do banco de dados
		$obAula = EntityAula::getAulaById($id);
		
		//Valida a instancia
		if(!$obAula instanceof EntityAula){
				$request->getRouter()->redirect('/admin/aulas');
		}
				
				//Renderiza o conteúdo
				$content = View::render('admin/modules/aulas/form',[
				    'title' => 'Aula > Editar',
					'statusMessage' => self::getStatus($request),
				    'data' => date('Y-m-d',strtotime($obAula->data)),
				    'optionTurmas' => EntityTurma::getSelectTurmas($obAula->turma),
				    'optionProfessores1' => EntityProfessor::getSelectProfessores($obAula->professor1),
				    'optionProfessores2' => EntityProfessor::getSelectProfessores($obAula->professor2),
				    'optionStatus' => EntityAula::getSelectStatusAula($obAula->status),
				    'optionDisciplina1' => EntityDisciplinaProfessor::getSelectDisciplinasProfessor($obAula->professor1,$obAula->id,$obAula->disciplina1),
				    'optionDisciplina2' => EntityDisciplinaProfessor::getSelectDisciplinasProfessor($obAula->professor2,$obAula->id,$obAula->disciplina2),
				    'obs' => $obAula->obs,
				 //   'desabilitaData' => 'readonly',
				  //  'desabilitaTurma' => 'disabled',
				    'dia' => $obAula->diaSemana
						
					
				]);
				
				//Retorna a página completa
				return parent::getPanel('Aulas > Cursinho', $content,'aulas', 'hidden');
	}
	
	
	//Metodo responsável por gravar a edição de uma agenda
	public static function setAulaEdit($request, $id){
	
	    //Post Vars
	    $postVars = $request->getPostVars();
	    
	    $data = $postVars['data'];
	    $turma = $postVars['turma'];
	    
	    
		//obtém a agenda do banco de dados
	    $obAula = EntityAula::getAulaById($id);
	    
	    //Valida a instancia
	    if(!$obAula instanceof EntityAula){
	        $request->getRouter()->redirect('/admin/aulas');
	    }
	    
	
	    //verifica se aula já existe
	    $obVerifica = EntityAula::getAulaDuplicada($data, $turma);
	    //Valida a instancia
	    if($obVerifica instanceof EntityAula){
	        //verifica se a aula encontrada é a mesma da aula que está sendo editada
	        if($obVerifica->id != $id){
	            $request->getRouter()->redirect('/admin/aulas/'.$obAula->id.'/edit?statusMessage=duplicad');
	        }
	    }
	    
	    
		
		
		$obAula->professor1 = $postVars['professor1'];
		$obAula->professor2 = $postVars['professor2'];
		$obAula->disciplina1 = $postVars['disciplina1'];
		$obAula->disciplina2 = $postVars['disciplina2'];
		$obAula->turma = $postVars['turma'];
		$obAula->status = $postVars['status'];
		$obAula->obs = $postVars['obs'];
		$obAula->diaSemana = $postVars['dia'];
		$obAula->data = implode('-', array_reverse(explode('/', $postVars['data'])));
			
			$obAula->atualizar();
			
			//Redireciona o usuário
			$request->getRouter()->redirect('/admin/aulas/'.$obAula->id.'/edit?statusMessage=updated');
		
		
		
	}
	
	




	

	
	//Metodo responsávelpor retornar o formulário de Exclusão de um Paciente
	public static function getAulaDelete($request,$id){
		
		
		
		//obtém o deopimento do banco de dados
		$obAula = EntityAula::getAulaById($id);
		
		//Valida a instancia
		if(!$obAula instanceof EntityAula){
			$request->getRouter()->redirect('/admin/aulas');
		}
		
		
		//Conteúdo do Formulário
		$content = View::render('admin/modules/aulas/delete',[
				'title' => 'Aulas > Excluir',
		        'data' => date('d/m/Y', strtotime($obAula->data)),
				'turma' => EntityTurma::getTurmaById($obAula->turma) -> nome,
		        'status' => EntityAula::getStatusAulaById($obAula->status)->nome,
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Excluir Aula > Cursinho', $content,'aulas', 'hidden');
		
	}
	
	//Metodo responsável por Excluir um Paciente
	public static function setAulaDelete($request,$id){
		
		//obtém o paciente do banco de dados
		$obAula = EntityAula::getAulaById($id);
		
		//Valida a instancia
		if(!$obAula instanceof EntityAula){
			$request->getRouter()->redirect('/admin/aulas');
		}
		
		//Exclui o depoimento
		$obAula->excluir();
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/admin/aulas?statusMessage=deleted');
		
		
	}
	

	//Metodo responsávelpor retornar os alunos presentes na aula
	public static function getAulaPresentes($request,$id){
	    
	    
	    //obtém o deopimento do banco de dados
	    $obAula = EntityAula::getAulaById($id);
	    
	    //Valida a instancia
	    if(!$obAula instanceof EntityAula){
	        $request->getRouter()->redirect('/admin/aulas');
	    }
	    
	    //Recebe os parâmetros da requisição
	    $queryParams = $request->getQueryParams();
	    

	    $matricula = @$queryParams['matricula'] ?? null;
	    $nome = @$queryParams['nome'] ?? null;
	    $cpf = @$queryParams['cpf'] ?? null;
	    
	    //Conteúdo do Formulário
	    $content = View::render('pages/detalheAula/presentes',[
	        'title' => 'Aula do dia: ' .date('d/m/Y',strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.EntityTurma::getTurmaById($obAula->turma)->nome,
	        'subtitle' => 'Alunos Presentes',  
	        'itens' => self::getAulasPresentesItems($request,$obPagination,$id),
	        'pagination' => parent::getPagination($request, $obPagination),
	        'total' => self::$qtdTotal,
	        'matricula' => $matricula,
	        'nome' => $nome,
	        'cpf' => $cpf,

	        
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Presentes na Aula > Cursinho', $content,'aulas', 'hidden');
	    
	}
	//Método responsavel por obter a rendereizacao os alunos presentes 
	private static function getAulasPresentesItems($request, &$obPagination, $idAula){
	    $resultados = '';
	    
	    //Pagina Atual
	    $queryParams = $request->getQueryParams();
	    $paginaAtual = $queryParams['page'] ?? 1;
	    
	    //Filtro Status
	    $matricula = $queryParams['matricula'] ?? '';
	    
	    $turma = @$queryParams['turma'];
	    
	    //Condições SQL
	    $condicoes = [
	        
	        strlen($matricula) ? 'matricula = "'.$matricula.'" ' : null,
	    ];
	    
	    //Remove posições vazias
	    $condicoes = array_filter($condicoes);
	    
	    //cláusula where
	    $where = 'idAula = '.$idAula.' AND F.status = "P" '.implode(' AND ', $condicoes);
	    
	    $order = 'dataReg';
	    
	    self::$qtdTotal = EntityFrequencia::getFrequenciasSQL($where, 'dataReg', null,'COUNT(*) as qtd','frequencia AS F')->fetchObject()->qtd;
	    
	    //Instancia de paginação
	    $obPagination = new Pagination(self::$qtdTotal,$paginaAtual,5);
	    #############################################
	   
	    
	    

	    $fields = '*';
	    
	    $table = 'frequencia AS F INNER JOIN alunos AS A ON A.id = F.idAluno';

	    $results = EntityFrequencia::getFrequenciasSQL($where,$order,$obPagination->getLimit(),$fields,$table);
	   // var_dump($results);exit;
	    //Renderiza
	    while ($obFrequencia = $results -> fetchObject(EntityFrequencia::class)) {
	
	       
	        $resultados .= View::render('pages/detalheAula/itemPresentes',[

	            'matricula' => EntityAluno::getAlunoById($obFrequencia->idAluno)->matricula,
	            'nome' => EntityAluno::getAlunoById($obFrequencia->idAluno)->nome,
	            'turma' => EntityTurma::getTurmaById(EntityAluno::getAlunoById($obFrequencia->idAluno)->turma)->nome,
	            'status' => $obFrequencia->status,
	            'hora' =>  date('H:i:s', strtotime($obFrequencia->dataReg)),
	            'foto' => EntityAluno::getAlunoById($obFrequencia->idAluno)->foto.'?var='.rand(),
	            'idAluno' => $obFrequencia->idAluno
	        ]);
	    }
	    //Retorna as agendas
	    return $resultados;
	    
	}
	

	
}

