<?php

namespace App\Controller\Painel;

use \App\Utils\View;
use \App\Model\Entity\Aula as EntityAula;
use \App\Model\Entity\Turma as EntityTurma;
use \App\Model\Entity\Professor as EntityProfessor;
use \App\Model\Entity\Disciplina as EntityDisciplina;
use \App\Model\Entity\DisciplinaProfessor as EntityDisciplinaProfessor;
use \App\Model\Entity\Aluno as EntityAluno;
use \App\Model\Entity\Bairro as EntityBairro;
use \App\Model\Entity\Frequencia as EntityFrequencia;
use \App\Model\Entity\StatusAula as EntityStatusAula;
use \App\Model\Entity\User as EntityUser;
use \App\Session\User\Login as SessionUserLogin;
use App\Utils\Funcoes;
use \WilliamCosta\DatabaseManager\Database;

class Aula extends Page{
	
	//Armazena quantidade total de pacientes listados
	private static $qtdTotal ;
	private static $totalGeralBpac = 0;
	//esconde busca rápida de prontuário no navBar
	private static $hidden = '';

	private static function formatPhone($phone){
	    $digits = preg_replace('/\D+/', '', (string)$phone);

	    if(strlen($digits) === 11){
	        return Funcoes::mask($digits, '(##) #####-####');
	    }

	    return trim((string)$phone);
	}

	private static function getAlunoEndereco($obAluno){
	    $obBairro = (int)$obAluno->bairro > 0 ? EntityBairro::getBairroById((int)$obAluno->bairro) : null;
	    $endereco = trim((string)$obAluno->endereco);
	    $numero = trim((string)($obAluno->numero ?? ''));
	    $bairro = $obBairro ? trim((string)$obBairro->nome) : '';
	    $cidadeUf = trim(trim((string)$obAluno->cidade).' / '.trim((string)$obAluno->uf), ' /');
	    $partes = [];

	    if($endereco !== ''){
	        $partes[] = $numero !== '' ? $endereco.', '.$numero : $endereco;
	    }

	    if($bairro !== ''){
	        $partes[] = $bairro;
	    }

	    if($cidadeUf !== ''){
	        $partes[] = $cidadeUf;
	    }

	    return implode(' - ', $partes);
	}

	//Método responsavel por obter a rendereizacao das aulas para a página
	private static function getAulasItems($request){
		$resultados = '';
		$visivelDeleteAula = SessionUserLogin::isAdmin() && SessionUserLogin::can('aulas.delete') ? '' : 'hidden';

		self::$qtdTotal = EntityAula::getAulas(null, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;
		$results = EntityAula::getAulas(null, 'data DESC, id DESC');
		
		//Renderiza
		while ($obAula = $results -> fetchObject(EntityAula::class)) {
		    
		    $cor = 'btn-default';
		    if($obAula->status == 1) $cor = 'bg-gradient-success';
		    if($obAula->status == 2) $cor = 'bg-gradient-danger';
		    if($obAula->status == 3) $cor = 'bg-gradient-warning';

		    $obStatus = EntityAula::getStatusAulaById((int)$obAula->status);
		    $obTurma = (int)$obAula->turma > 0 ? EntityTurma::getTurmaById((int)$obAula->turma) : null;
		    $obAutor = (int)$obAula->autor > 0 ? EntityUser::getUserById((int)$obAula->autor) : null;
		    $obProfessor1 = (int)$obAula->professor1 > 0 ? EntityProfessor::getProfessorById((int)$obAula->professor1) : null;
		    $obDisciplina1 = (int)$obAula->disciplina1 > 0 ? EntityDisciplina::getDisciplinaById((int)$obAula->disciplina1) : null;
		    $obProfessor2 = (int)$obAula->professor2 > 0 ? EntityProfessor::getProfessorById((int)$obAula->professor2) : null;
		    $obDisciplina2 = (int)$obAula->disciplina2 > 0 ? EntityDisciplina::getDisciplinaById((int)$obAula->disciplina2) : null;
		    
			//View de Agendas
			$resultados .= View::render('painel/modules/aulas/item',[
			    
			    'id' => $obAula->id,
			    'data' =>  date('d/m/Y', strtotime($obAula->data)).' ( '.$obAula->diaSemana.' )',
			    'status' => $obStatus ? $obStatus->nome : 'Sem status',
			    'turma' => $obTurma ? $obTurma->nome : 'Sem turma',
			    'presencas' => EntityFrequencia::getFrequencias('idAula = '.$obAula->id.' AND status = "P"', null,null,'COUNT(*) as qtd')->fetchObject()->qtd,
			    'faltas' => EntityFrequencia::getFrequencias('idAula = '.$obAula->id.' AND status = "F"', null,null,'COUNT(*) as qtd')->fetchObject()->qtd,
			    'cor' => $cor,
			    'autor' => $obAutor ? $obAutor->nome : 'Sem autor',
			    'professor1' => $obProfessor1 ? $obProfessor1->nome : 'Sem professor',
			    'disciplina1' => $obDisciplina1 ? $obDisciplina1->nome : 'Sem disciplina',
			    'professor2' => $obProfessor2 ? $obProfessor2->nome : '',
			    'disciplina2' => $obDisciplina2 ? $obDisciplina2->nome : '',
			    'visivelDeleteAula' => $visivelDeleteAula,
			    
			]);
		}
		//Retorna as agendas
		return $resultados;

	}
	
	

	//Método responsavel por renderizar a view de Listagem de Atendimentos
	public static function getAulas($request){
		//Conteúdo da Home
		$content = View::render('painel/modules/aulas/index',[
				 'title'=> 'Aulas',
				'itens' => self::getAulasItems($request),
				'statusMessage' => Funcoes::getStatus($request),
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Aulas > Cursinho', $content,'aulas', 'hidden');
	}
	
	
	//Método responsavel por renderizar a view de Nova Aula
	public static function getAulasNew($request){
		
			//Conteúdo da Home
			$content = View::render('painel/modules/aulas/form',[
			         'title' => 'Aula > Nova',
					'statusMessage' => Funcoes::getStatus($request),
					'optionTurmas' => EntityTurma::getSelectTurmas(null),
					'optionProfessores1' => EntityProfessor::getSelectProfessores(null),
			        'optionProfessores2' => EntityProfessor::getSelectProfessores(null),
			        'optionStatus' => EntityAula::getSelectStatusAula(null),
			        'optionDisciplina1' => '',
			        'optionDisciplina2' => '',
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
			$request->getRouter()->redirect('/aulas?statusMessage=duplicad');
		}

		//verifica se a sessao não está ativa
		if(session_status() != PHP_SESSION_ACTIVE ){
		    session_start();
		}
		$user = $_SESSION['usuario']['id'];

		$database = new Database('aulas');
		$database->beginTransaction();

		try{
		    $obAula->cadastrar($database);

		    //Preenche a Aula com faltas em lote para alunos ativos da turma, exceto SÁB e DOM.
		    $diaSemana = array("SÁB", "DOM");
		    if(!in_array($obAula->diaSemana,$diaSemana)){
		        EntityFrequencia::cadastrarFaltasDaAula($obAula->id, $obAula->turma, $user, $database);
		    }

		    $database->commit();
		}catch(\Throwable $e){
		    $database->rollBack();
		    throw $e;
		}

		//Redireciona o usuário
		$request->getRouter()->redirect('/aulas?statusMessage=created');
	}
	
	

	//Método responsavel por renderizar a view de Edição de Aula
	public static function getAulaEdit($request, $id){
			
		//obtém o Aula  do banco de dados
		$obAula = EntityAula::getAulaById($id);
		
		//Valida a instancia
		if(!$obAula instanceof EntityAula){
				$request->getRouter()->redirect('/aulas');
		}
				
				//Renderiza o conteúdo
				$content = View::render('painel/modules/aulas/form',[
				    'title' => 'Aula > Editar',
					'statusMessage' => Funcoes::getStatus($request),
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
	        $request->getRouter()->redirect('/aulas');
	    }
	    
	
	    //verifica se aula já existe
	    $obVerifica = EntityAula::getAulaDuplicada($data, $turma);
	    //Valida a instancia
	    if($obVerifica instanceof EntityAula){
	        //verifica se a aula encontrada é a mesma da aula que está sendo editada
	        if($obVerifica->id != $id){
	            $request->getRouter()->redirect('/aulas/'.$obAula->id.'/edit?statusMessage=duplicad');
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
			$request->getRouter()->redirect('/aulas/'.$obAula->id.'/edit?statusMessage=updated');
		
		
		
	}
	
	




	

	
	//Metodo responsávelpor retornar o formulário de Exclusão de um Paciente
	public static function getAulaDelete($request,$id){
		
		
		
		//obtém o deopimento do banco de dados
		$obAula = EntityAula::getAulaById($id);
		
		//Valida a instancia
		if(!$obAula instanceof EntityAula){
			$request->getRouter()->redirect('/aulas');
		}
		
		
		//Conteúdo do Formulário
		$content = View::render('painel/modules/aulas/delete',[
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
			$request->getRouter()->redirect('/aulas');
		}
		
		$database = new Database('aulas');
		$database->beginTransaction();

		try{
			$database->execute('DELETE FROM frequencia WHERE idAula = ?', [(int)$obAula->id]);
			$database->delete('id = '.(int)$obAula->id);
			$database->commit();
		}catch(\Throwable $e){
			$database->rollBack();
			throw $e;
		}
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/aulas?statusMessage=deleted');
		
		
	}
	

	//Metodo responsávelpor retornar os alunos presentes na aula
	public static function getAulaPresentes($request,$id){
	    
	    
	    //obtém o deopimento do banco de dados
	    $obAula = EntityAula::getAulaById($id);
	    $queryParams = $request->getQueryParams();
	    $voltarUrl = ($queryParams['origem'] ?? '') === 'dashboard' ? URL.'/dashboard' : URL.'/aulas';
	    
	    //Valida a instancia
	    if(!$obAula instanceof EntityAula){
	        $request->getRouter()->redirect('/aulas');
	    }
	    //Conteúdo do Formulário
	    $content = View::render('pages/detalheAula/presentes',[
	        'title' => 'Aula do dia: ' .date('d/m/Y',strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.EntityTurma::getTurmaById($obAula->turma)->nome,
	        'subtitle' => 'Frequência',
	        'itens' => self::getAulasPresentesItems($id),
	        'voltarUrl' => $voltarUrl,
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Presentes na Aula > Cursinho', $content,'aulas', 'hidden');
	    
	}

	private static function getFotoAuditoriaPresenca($fotoAuditoria, $nomeAluno){
	    $fotoAuditoria = basename((string)$fotoAuditoria);

	    if($fotoAuditoria === ''){
	        return '<span class="attendance-photo-empty">Sem captura</span>';
	    }

	    $fotoPath = dirname(__DIR__).'/File/files/frequencias-auditoria/'.$fotoAuditoria;
	    if(!is_file($fotoPath)){
	        return '<span class="attendance-photo-empty">Sem captura</span>';
	    }

	    return View::render('pages/detalheAula/fotoAuditoriaPresenca',[
	        'fotoAuditoria' => $fotoAuditoria,
	        'nomeFoto' => htmlspecialchars((string)$nomeAluno, ENT_QUOTES, 'UTF-8'),
	        'tituloFoto' => htmlspecialchars('Foto capturada - '.(string)$nomeAluno, ENT_QUOTES, 'UTF-8')
	    ]);
	}

	//Método responsavel por obter a rendereizacao os alunos presentes 
	private static function getAulasPresentesItems($idAula){
	    $resultados = '';
	    $table = 'frequencia AS F INNER JOIN alunos AS A ON A.id = F.idAluno';
	    
	    //cláusula where
	    $where = 'F.idAula = '.$idAula;
	    
	    $order = 'A.nome ASC';
	    
	    self::$qtdTotal = EntityFrequencia::getFrequenciasSQL($where, $order, null,'COUNT(*) as qtd',$table)->fetchObject()->qtd;

	    $fields = 'F.*';

	    $results = EntityFrequencia::getFrequenciasSQL($where,$order,null,$fields,$table);
	   // var_dump($results);exit;
	    //Renderiza
	    while ($obFrequencia = $results -> fetchObject(EntityFrequencia::class)) {
	        $obAluno = EntityAluno::getAlunoById($obFrequencia->idAluno);
	        if(!$obAluno instanceof EntityAluno){
	            continue;
	        }
	        $obTurma = EntityTurma::getTurmaById($obAluno->turma);
	        $status = (string)$obFrequencia->status === 'P' ? 'Presente' : 'Falta';
	        $statusCor = (string)$obFrequencia->status === 'P' ? 'bg-gradient-success' : 'bg-gradient-danger';
	
	       
	        $resultados .= View::render('pages/detalheAula/itemPresentes',[

	            'matricula' => $obAluno->matricula,
	            'nome' => $obAluno->nome,
	            'turma' => $obTurma ? $obTurma->nome : 'Sem turma',
	            'fone' => self::formatPhone($obAluno->fone),
	            'endereco' => self::getAlunoEndereco($obAluno),
	            'status' => $status,
	            'statusCor' => $statusCor,
	            'hora' =>  date('H:i:s', strtotime($obFrequencia->dataReg)),
	            'foto' => $obAluno->getFoto(),
	            'idAluno' => $obFrequencia->idAluno,
	            'fotoAuditoria' => self::getFotoAuditoriaPresenca($obFrequencia->fotoAuditoria ?? '', $obAluno->nome)
	        ]);
	    }
	    //Retorna as agendas
	    return $resultados;
	    
	}
	

	
}
