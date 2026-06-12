<?php

namespace App\Controller\Painel;

use \App\Utils\View;
use \App\Model\Entity\Bairro as EntityBairro;
use \App\Utils\Funcoes;
use \App\Model\Entity\Professor as EntityProfessor;
use \App\Model\Entity\Disciplina as EntityDisciplina;
use \App\Model\Entity\Status as EntityStatus;
use \App\Model\Entity\DisciplinaProfessor as EntityDisciplinaProfessor;
use Bissolli\ValidadorCpfCnpj\CPF;
use \App\Controller\File\Upload as Upload;
use \App\Controller\Painel\Resize;


class Professor extends Page{
	
	//Armazena quantidade total de registros listados
	private static $qtdTotal ;
	//esconde busca rápida de prontuário no navBar (''->exibe  'hidden'->esconde)
	private static $buscaRapidaPront = 'hidden';

	private static function formatDate($date){
		$timestamp = strlen((string)$date) ? strtotime($date) : false;

		return $timestamp ? date('d/m/Y', $timestamp) : '';
	}

	private static function formatPhone($phone){
		$digits = preg_replace('/\D+/', '', (string)$phone);

		if(strlen($digits) === 11){
			return Funcoes::mask($digits, '(##) #####-####');
		}

		return trim((string)$phone);
	}

	private static function getProfessorEndereco($obProfessor){
		$obBairro = (int)$obProfessor->bairro > 0 ? EntityBairro::getBairroById((int)$obProfessor->bairro) : null;
		$endereco = trim((string)$obProfessor->endereco);
		$bairro = $obBairro ? trim((string)$obBairro->nome) : '';
		$cidadeUf = trim(trim((string)$obProfessor->cidade).' / '.trim((string)$obProfessor->uf), ' /');
		$partes = [];

		if($endereco !== ''){
			$partes[] = $endereco;
		}

		if($bairro !== ''){
			$partes[] = $bairro;
		}

		if($cidadeUf !== ''){
			$partes[] = $cidadeUf;
		}

		return implode(' - ', $partes);
	}

	private static function getProfessorDisciplinas($idProfessor){
		$disciplinas = [];
		$resultsDisciplina = EntityDisciplinaProfessor::getDisciplinasProfessor('idProfessor = '.(int)$idProfessor);

		while ($obDisciplina = $resultsDisciplina -> fetchObject(EntityDisciplinaProfessor::class)) {
			$obDisciplinaAtual = EntityDisciplina::getDisciplinaById($obDisciplina->idDisciplina);
			if ($obDisciplinaAtual instanceof EntityDisciplina) {
				$disciplinas[] = $obDisciplinaAtual->nome;
			}
		}

		return implode(', ', $disciplinas);
	}
	
	//Método responsavel por obter a rendereizacao dos professores para a página
	private static function getProfessoresItems($request){
		$resultados = '';

		self::$qtdTotal = EntityProfessor::getProfessores(null, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;
		$order = 'id DESC';
		$results = EntityProfessor::getProfessores(null, $order);

		//Renderiza
		while ($obProfessor = $results -> fetchObject(EntityProfessor::class)) {
			 
		    $reload = rand();
		    $obStatus = $obProfessor->status !== null && $obProfessor->status !== '' ? EntityStatus::getStatusById((int)$obProfessor->status) : null;
		    $cor = $obProfessor->status == 1 ? 'bg-gradient-success' : 'bg-gradient-danger';
		    $statusToken = $obProfessor->status == 1 ? 'ativo' : 'inativo';
		    $foto = strlen((string)$obProfessor->foto) ? $obProfessor->foto : 'profile.png';

			//View de professores
			$resultados .= View::render('painel/modules/professores/item',[
			    'nome' => $obProfessor->nome,
			    'cpf' =>Funcoes::mask($obProfessor->cpf, '###.###.###-##') ,
			    'status' => $obStatus ? $obStatus->nome : 'Sem status',
			    'id' => $obProfessor->id,
			    'cor' => $cor,
			    'statusToken' => $statusToken,
			    'email' => $obProfessor->email,
			    'fone' => self::formatPhone($obProfessor->fone),
			    'endereco' => self::getProfessorEndereco($obProfessor),
			    'funcao' => $obProfessor->funcao,
			    'dataNasc' => self::formatDate($obProfessor->dataNasc),
			    'dataCad' => self::formatDate($obProfessor->dataCad ?? ''),
			    'foto' => $foto.'?var='.$reload,
			    'disciplinas' => self::getProfessorDisciplinas($obProfessor->id),
			    'visivelDeleteProfessor' => permissaoExcluirProfessor,
			]);
		}
	
		//Grava o Log do usuário
//		if(!empty($queryParams)) Logs::setNewLog('pacientes', 'Pesquisa' , implode(", ", $condicoes));

		//Retorna os pacientes
		return $resultados;
		
		
		
	}
	
	
	//Método responsavel por renderizar a view de Listagem de Pacientes
	public static function getProfessores($request){
		//Conteúdo da Home
		$content = View::render('painel/modules/professores/index',[
				'title' => 'Professores > Pesquisa',
				'itens' => self::getProfessoresItems($request),
				'statusMessage' => Funcoes::getStatus($request),
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
	    $old = Funcoes::pullOldInput('professor.novo');
	    $statusMessage = $queryParams['statusMessage'] ?? '';
	    $cpfProfessor = $queryParams['cpfProfessor'] ?? ($old['cpf'] ?? '');
	    
	    //instancia classe pra verificar CPF
	    $validaCpf = new CPF($cpfProfessor);
	    
	    //verifica se é válido o cpf
	    if (!$validaCpf->isValid()){
	        
	        $request->getRouter()->redirect('/professores/?statusMessage=cpfInvalid');
	    }
	    
	    
	    //busca usuário pelo CPF sem a maskara
	    $ob = EntityProfessor::getProfessorByCPF($validaCpf->getValue());
	    //verifica se o cpf já está cadastrado
	    if($ob instanceof EntityProfessor && !in_array($statusMessage, ['cpfDuplicated', 'cpfduplicated'])){
	        $request->getRouter()->redirect('/professores?statusMessage=duplicad');
	    }
	    

	    //Conteúdo do Formulário
	    $content = View::render('painel/modules/professores/form',[
	        
	        'title' => 'Professor > Novo',
	        'id' => '',
	        'nome' => $old['nome'] ?? '',
	        'cep' => $old['cep'] ??'',
	        'endereco' => $old['endereco'] ?? '',
	        'statusMessage' => Funcoes::getStatus($request),
	        'fone' => ($old['fone'] ?? '') ?: '(00)00000-0000',
	        'cidade' => $old['cidade'] ??'Santana',
	        'uf' => $old['uf'] ??'AP',
	        'cpf' => $old['cpf'] ?? $validaCpf->getValue(),
	        'funcao' => $old['funcao'] ??'Professor',
	        'dataNasc' => $old['dataNasc'] ??'',
	        'optionBairros' => EntityBairro::getSelectBairros($old['bairro'] ?? null),
	        'email' => $old['email'] ??'',
	        'adicionarDisciplina' => 'hidden',
	        'optionStatus' => EntityStatus::getSelectStatus($old['status'] ?? null),
	        'foto' => 'profile.png',
	        'labelId' => 'hidden',
	        'ponteiro' => 'pointer-events: none;',
	        'hiddenFoto' => '',
	        'optionDisciplinas' => '',
	        'itensDisciplina' => ''
	        
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
	        $request->getRouter()->redirect('/professores/new?'.http_build_query([
	            'cpfProfessor' => $validaCpf->getValue(),
	            'statusMessage' => 'cpfDuplicated'
	        ]));
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
	    $obProfessor->fone = preg_replace('/\D+/', '', $postVars['fone'] ?? '');
	    $obProfessor->status = $postVars['status'];
	    $obProfessor->email = $postVars['email'];
	    $obProfessor->cadastrar();
	    
	    //encerra sessão com os dados do form
	    EntityProfessor::getFinalizaSessaoDados();
	    
	    //	Logs::setNewLog($request);
	    
	    //Redireciona o usuário
	    $request->getRouter()->redirect('/professores/'.$obProfessor->id.'/edit?statusMessage=created');
	    
	}
	
	//Metodo responsávelpor retornar o formulário de Edição de um Profissional
	public static function getEditProfessor($request,$id){
	    
	    //obtém o Profissional do banco de dados
	    $obProfessor = EntityProfessor::getProfessorById($id);
	    
	    //Valida a instancia
	    if(!$obProfessor instanceof EntityProfessor){
	        $request->getRouter()->redirect('/professores');
	    }
	    
	    self::setDisciplinaAdd($request,$id);
	    self::setDisciplinaRemove($request,$id);
	    
	    $reload = rand();
	    //Conteúdo do Formulário
	    $content = View::render('painel/modules/professores/form',[
	       
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
	        'itensDisciplina' => self::getProfessorDisciplinaItems($obProfessor->id),
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
	        $request->getRouter()->redirect('/professores');
	    }
	    
	    //Post Vars
	    $postVars = $request->getPostVars();
	    
	    //instancia classe pra verificar CPF
	    $validaCpf = new CPF($postVars['cpf']);
	    
	    //obtém o funcionário pelo CPF (apenas números)
	    $obProfessorCPF = EntityProfessor::getProfessorByCPF($validaCpf->getValue());
	    
	    //verifica se o CPF já está sendo usado por outro PRofessor
	    if($obProfessorCPF instanceof EntityProfessor && $obProfessorCPF->id != $id){
	        $request->getRouter()->redirect('/professores/'.$id.'/edit?statusMessage=cpfDuplicated');
	    }
	    
	    //Valida o email do usuário
	    $obProfessorEmail = EntityProfessor::getProfessorByEmail($postVars['email']);
	    
	    
	    //verifica se o E-MAIL já está sendo usado por outro usuário
	    if($obProfessorEmail instanceof EntityProfessor && $obProfessorEmail->id != $id){
	        $request->getRouter()->redirect('/professores/'.$id.'/edit?statusMessage=emailDuplicated');
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
	    $obProfessor->fone = preg_replace('/\D+/', '', $postVars['fone'] ?? '') ?: $obProfessor->fone;
	    $obProfessor->status = $postVars['status'] ?? $obProfessor->status;
	    $obProfessor->email = $postVars['email'] ?? $obProfessor->email;
	    $obProfessor->atualizar();
	    
	    //	Logs::setNewLog($request);
	    
	    //Redireciona o usuário
	    $request->getRouter()->redirect('/professores/'.$obProfessor->id.'/edit?statusMessage=updated');
	    
	}
	


	
	
	
	

	
	
	
	//Método responsavel por obter a rendereizacao as Disciplinas do professor
	private static function getProfessorDisciplinaItems($id){
	    $resultados = '';
	    $where = 'idProfessor = '.(int)$id;
	    $order = 'id';
	    $results = EntityDisciplinaProfessor::getDisciplinasProfessor($where, $order);
	   
	    //Renderiza
	    while ($obProfessorDisciplina = $results -> fetchObject(EntityDisciplinaProfessor::class)) {
	        $obDisciplina = EntityDisciplina::getDisciplinaById((int)$obProfessorDisciplina->idDisciplina);
	        if (!$obDisciplina instanceof EntityDisciplina) continue;

	        $resultados .= View::render('painel/modules/professores/itemDisciplina',[
	            'nome' => $obDisciplina->nome,
	            'idDisciplina' => $obProfessorDisciplina->idDisciplina,
	            'idVinculo' => $obProfessorDisciplina->id,
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
			$request->getRouter()->redirect('/professores');
		}
		
		
		//Conteúdo do Formulário
		$content = View::render('painel/modules/professores/delete',[
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
			$request->getRouter()->redirect('/professores');
		}
		
		//Exclui o professor
		$obProfessor->excluir();
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/professores?statusMessage=deleted');
		
		
	}
	
	
	
	//Metodo responsávelpor retornar o formulário de Captura de foto do aluno
	public static function getPhotoProfessor($request,$id){
	    
	    $obProfessor = EntityProfessor::getProfessorById($id);
	    
	    //Conteúdo do Formulário
	    $content = View::render('painel/modules/alunos/formPhoto',[
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
	        $request->getRouter()->redirect('/professores/'.$obProfessor->id.'/edit?statusMessage=updated');
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
	        $request->getRouter()->redirect('/professores/'.$obProfessor->id.'/edit?statusMessage=updated');
	    }
	    
	    $request->getRouter()->redirect('/professores/'.$obProfessor->id.'/edit?statusMessage=semfoto');
	    
	    
	}
	
	//Método responsavel por Adicionar disciplina ao professor
	private static function setDisciplinaAdd($request,$id){
	    //Query PArams
	    $queryParams = $request->getQueryParams();
	    
	    //Status
	    if(!isset($queryParams['disciplina'])) return '';

	    $id = (int)$id;
	    $idDisciplina = (int)$queryParams['disciplina'];
	    if($idDisciplina <= 0){
	        $request->getRouter()->redirect('/professores/'.$id.'/edit');
	    }
	    
	    //obtém o Disciplina do banco de dados
	    $obDisciplina = EntityDisciplina::getDisciplinaById($idDisciplina);
	    if(!$obDisciplina instanceof EntityDisciplina){
	        $request->getRouter()->redirect('/professores/'.$id.'/edit');
	    }

	    $where = 'idProfessor = '.$id.' AND idDisciplina = '.$idDisciplina;
	    $obDisciplinaExistente = EntityDisciplinaProfessor::getDisciplinasProfessor($where, null, 1)->fetchObject(EntityDisciplinaProfessor::class);

	    if($obDisciplinaExistente instanceof EntityDisciplinaProfessor){
	        $request->getRouter()->redirect('/professores/'.$id.'/edit');
	    }
	    
	    $obDisciplinaProfessor = new EntityDisciplinaProfessor();
	    $obDisciplinaProfessor->idProfessor = $id;
	    $obDisciplinaProfessor->idDisciplina = $idDisciplina;
	    $obDisciplinaProfessor->cadastrar();
	    $request->getRouter()->redirect('/professores/'.$id.'/edit');
	}
	
	//Método responsavel por remover disciplina do professor
	private static function setDisciplinaRemove($request,$id){
	    //Query PArams
	    $queryParams = $request->getQueryParams();
	    
	    //Status
	    if(!isset($queryParams['removeDisciplina'])) return '';
	    
	    //obtém o Disciplina do banco de dados
	    $obDisciplinaProfessor = EntityDisciplinaProfessor::getDisciplinaProfessorById((int)$queryParams['removeDisciplina']);
	    if($obDisciplinaProfessor instanceof EntityDisciplinaProfessor && (int)$obDisciplinaProfessor->idProfessor === (int)$id){
	        $obDisciplinaProfessor->excluir();
	    }
	    $request->getRouter()->redirect('/professores/'.$id.'/edit');
	}
	
}
