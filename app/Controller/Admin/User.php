<?php

namespace App\Controller\Admin;

use \App\Utils\View;
use \App\Model\Entity\User as EntityUser;
use \WilliamCosta\DatabaseManager\Pagination;
use Bissolli\ValidadorCpfCnpj\CPF;
use \App\Utils\Funcoes;
use \App\Model\Entity\Profissional as EntityProfissional;
use App\Controller\File\Upload;
use App\Session\Admin;
use App\Session\Admin\Login;

class User extends Page{
	
	//esconde busca rápida de prontuário no navBar
	private static $hidden = 'hidden';
	
	//Método responsavel por obter a renderização dos itens de usuários para a página
	private static function getUserItems($request, &$obPagination){
		//Usuários
		$itens = '';
		
		//Quantidade total de registros
		$quantidadetotal =  EntityUser::getUsers(null, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;
		
		//Página atual
		$queryParams = $request->getQueryParams();
		$paginaAtual = $queryParams['page'] ?? 1;
		
		//Instancia de paginacao
		$obPagination = new Pagination($quantidadetotal,$paginaAtual,5);
		
		//Mostra todos os usuarios para o Admin e apenas o Operador para ele mesmo
		if($_SESSION['usuario']['tipo'] == 'Admin'){
		    $where = null;
		    $paginacao = $obPagination->getLimit();
		}else{
		    $where = 'id = '.$_SESSION['usuario']['id'];
		    $paginacao = 1;
		} 
		
		//Resultados da Página
		$results = EntityUser::getUsers($where, 'id DESC',$paginacao);
		
		$reload = rand();
		//Renderiza o item
		while ($obUser = $results->fetchObject(EntityUser::class)) {
		
			//View de depoimentos
			$itens.= View::render('admin/modules/users/item',[
					'id' => $obUser->id,
					'nome' => $obUser->nome,
					'email' => $obUser->email,
					'cpf' => Funcoes::mask($obUser->cpf, '###.###.###-##') ,
					'tipo' => $obUser->tipo,
    			    'foto' => $obUser->foto.'?var='.$reload,
			    'excluirUsuarioChecado' => permissaoExcluirUsuario
			]);
		}
		
		
		//Retorna os depoimentos
		return $itens;
		
	}
	
	
	//Método responsavel por renderizar a view de Listagem de USuários
	public static function getUsers($request){
		
		//Conteúdo da Home
		$content = View::render('admin/modules/users/index',[
				'itens' => self::getUserItems($request, $obPagination),
				'pagination' => parent::getPagination($request, $obPagination),
				'statusMessage' => Funcoes::getStatus($request),
				'navBar'=>View::render('admin/navBar',[]),
				'footer'=>View::render('admin/modules/pacientes/footer',[]),
				
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Usuários > SISCAPS', $content,'users', self::$hidden);
		
	}
	
	//Metodo responsávelpor retornar o formulário de cadastro de um novo usuário
	public static function getNewUser($request,$id){
	    
	    //Inicia sessão
	    Funcoes::init();
	    
	    //QUERY PARAMS
	    $queryParams = $request->getQueryParams();
	    
	    //instancia classe pra verificar CPF
	    $validaCpf = new CPF($queryParams['cpfUser']);
	    
	    //verifica se é válido o cpf
	    if (!$validaCpf->isValid()){
	        
	        $request->getRouter()->redirect('/admin/users?statusMessage=cpfInvalid');
	    }
	    
	    
	    //busca usuário pelo CPF sem a maskara
	    $ob = EntityUser::getUserByCPF($validaCpf->getValue());
	    //verifica se o cpf já está cadastrado
	    if($ob instanceof EntityUser){
	        $request->getRouter()->redirect('/admin/users?statusMessage=duplicated');
	    }
	    
	    
		//Conteúdo do Formulário
		$content = View::render('admin/modules/users/form',[
				'title' => 'Usuários > Novo',
		         'nome' => @$_SESSION['usuario']['novo']['nome'] ?? '',
		         'email' => @$_SESSION['usuario']['novo']['email'] ?? '',
		    'cpf' => @$_SESSION['usuario']['novo']['cpf'] ?? @$validaCpf->getValue(),
		         'senha' => @$_SESSION['usuario']['novo']['senha'] ?? '',
				'statusMessage' => Funcoes::getStatus($request),
		        'selectedVisitante'=> 'selected',
		        'foto' => 'profile.png',  
		        'required' => 'required',
		         'ponteiro' => 'pointer-events: none;'
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Usuário > Cursinho', $content,'users', self::$hidden);
		
	}
	
	
	//Metodo responsávelpor por cadastrar um usuário no banco
	public static function setNewUser($request){
		//Post vars
		$postVars = $request->getPostVars();
		
		
		
		$nome = $postVars['nome'] ?? '';
		$email = $postVars['email'] ?? '';
		$senha = $postVars['senha'] ?? '';
		$cpf = $postVars['cpf'] ?? '';
		$tipo = $postVars['tipo'] ?? '';
		$excluirAluno = $postVars['checkExcluirAluno'] ?? '0';
		$excluirProfessor = $postVars['checkExcluirProfessor'] ?? '0';
		$excluirDisciplina = $postVars['checkDisciplina'] ?? '0';
		$excluirUsuario = $postVars['checkExcluirUsuario'] ?? '0';
		$menuAlunos = $postVars['checkMenuAlunos'] ?? '0';
		$menuProfessores = $postVars['checkMenuProfessores'] ?? '0';
		$menuAulas = $postVars['checkMenuAulas'] ?? '0';
		$menuFrequencias = $postVars['checkMenuFrequencias'] ?? '0';
		$btnNovoUsuario = $postVars['checkBtnNovoUsuario'] ?? '0';
		$menuPresenca = $postVars['checkMenuPresenca'] ?? '0';
		$menuDisciplinas = $postVars['checkMenuDisciplinas'] ?? '0';
		
		//Cria sessão com os dados do form
		EntityUser::getSessaoDados($postVars);
		
		//instancia classe pra verificar CPF
		$validaCpf = new CPF($cpf);
		
		//busca usuário pelo CPF sem a maskara
		$obUser = EntityUser::getUserByCPF($validaCpf->getValue());
		
		if($obUser instanceof EntityUser){
		 
		        $request->getRouter()->redirect('/admin/users/new?statusMessage=cpfDuplicated');
		}
		
		//Valida o email do usuário
		$obUserEmail = EntityUser::getUserByEmail($email);
		
		if($obUserEmail instanceof EntityUser ){
		    $request->getRouter()->redirect('/admin/users/new?statusMessage=emailDuplicated');
		}
				
		//Nova instancia de Usuário
		$obUser = new EntityUser;
		$obUser->nome = $nome;
		$obUser->email = $email;
		$obUser->cpf = $validaCpf->getValue(); //cpf sem formatação
		$obUser->tipo = $tipo;
		//$obUser->senha = password_hash($senha,PASSWORD_DEFAULT);
		$obUser->senha = $senha;
		
		//campos para permissão
		$obUser->excluirAluno = $excluirAluno;
		$obUser->excluirProfessor = $excluirProfessor;
		$obUser->excluirDisciplina = $excluirDisciplina;
		$obUser->excluirUsuario = $excluirUsuario;
		$obUser->menuAlunos = $menuAlunos;
		$obUser->menuProfessores = $menuProfessores;
		$obUser->menuAulas = $menuAulas;
		$obUser->menuFrequencias = $menuFrequencias;
		$obUser->btnNovoUsuario = $btnNovoUsuario;
		$obUser->menuPresenca = $menuPresenca;
		$obUser->menuDisciplinas = $menuDisciplinas;
		
		//grava as informações
		$obUser->cadastrar();
		
		//Atualiza a sessão de usuário
		Login::login($obUser);
		
		
		//encerra sessão com os dados do form
		EntityUser::getFinalizaSessaoDados();
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/admin/users/'.$obUser->id.'/edit?statusMessage=created');
		
	}
	

	
	
	//Metodo responsávelpor retornar o formulário de Edição de um Usuário
	public static function getEditUser($request,$id){
		
				
		//obtém o usuário do banco de dados
		$obUser = EntityUser::getUserById($id);
		
		//Valida a instancia
		if(!$obUser instanceof EntityUser){
			$request->getRouter()->redirect('/admin/users');
		}
		
		$obUser->tipo == 'Admin' ? $selectedAdmin = 'selected' : $selectedAdmin = '' ;
		$obUser->tipo == 'Visitante' ? $selectedVisitante = 'selected' : $selectedVisitante = '' ;
		$obUser->tipo == 'Operador' ? $selectedOperador = 'selected' : $selectedOperador = '' ;
		$obUser->excluirAluno == 1 ? $alunoChecado = 'checked' : $alunoChecado = '';
		$obUser->excluirProfessor == 1 ? $professorChecado = 'checked' : $professorChecado = '';
		$obUser->excluirUsuario == 1 ? $excluirUsuarioChecado = 'checked' : $excluirUsuarioChecado = '';
		$obUser->menuAlunos == 1 ? $menuAlunoChecado = 'checked' : $menuAlunoChecado = '';
		$obUser->menuProfessores == 1 ? $menuProfessorChecado = 'checked' : $menuProfessorChecado = '';
		$obUser->menuAulas == 1 ? $menuAulasChecado = 'checked' : $menuAulasChecado = '';
		$obUser->menuFrequencias == 1 ? $menuFrequenciasChecado = 'checked' : $menuFrequenciasChecado = '';
		$obUser->btnNovoUsuario == 1 ? $btnNovoUsuarioChecado = 'checked' : $btnNovoUsuarioChecado = '';
		$obUser->menuPresenca == 1 ? $menuPresencaChecado = 'checked' : $menuPresencaChecado = '';
		$obUser->menuDisciplinas == 1 ? $menuDisciplinasChecado = 'checked' : $menuDisciplinasChecado = '';
		$obUser->excluirDisciplina == 1 ? $excluirDisciplinaChecado = 'checked' : $excluirDisciplinaChecado = '';
		
		$reload = rand();
		//Conteúdo do Formulário
		$content = View::render('admin/modules/users/form',[
		       'title' => 'Usuários > Editar',
				'nome' => $obUser->nome,
		        'id' => $obUser->id,
				'email' => $obUser->email,
				'senha' => $obUser->senha,
				'cpf' => Funcoes::mask($obUser->cpf, '###.###.###-##'), 
				'selectedAdmin'=> $selectedAdmin,
				'selectedVisitante'=> $selectedVisitante,
				'selectedOperador'=> $selectedOperador,
				'statusMessage' => Funcoes::getStatus($request),
				'navBar'=>View::render('admin/navBar',[]),
			 	'footer'=>View::render('admin/modules/pacientes/footer',[]),
		          'foto' => $obUser->foto.'?var='.$reload,
		        'required' => '',
		         'ponteiro' => '',
		    'alunoChecado' => $alunoChecado,
		    'professorChecado' => $professorChecado,
		    'menuAlunosChecado' => $menuAlunoChecado,
		    'menuProfessorChecado' => $menuProfessorChecado,
		    'menuAulasChecado' => $menuAulasChecado,
		    'menuFrequenciasChecado' => $menuFrequenciasChecado,
		    'btnNovoUsuarioChecado' => $btnNovoUsuarioChecado,
		    'btnNovoUsuarioVisivel' => permissaoBtnNovoUsuario,
		    'menuPresencaChecado' => $menuPresencaChecado,
		    'menuDisciplinasChecado' => $menuDisciplinasChecado,
		    'permissoesVisivel' => permissoes,
		    'habilitado' => habilitaCPFTIPO,
		    'excluirDisciplinaChecado' => $excluirDisciplinaChecado,
		    'excluirUsuarioChecado' => $excluirUsuarioChecado,
		         
				
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Editar Usuário > SISCAPS', $content,'users', self::$hidden);
		
	}
	
	//Metodo responsável por gravar a atualizacao de um usuário
	public static function setEditUser($request,$id){
		//Post Vars
		$postVars = $request->getPostVars();
		
				
		$nome = $postVars['nome'] ?? '';
		$email = $postVars['email'] ?? '';
		$senha = $postVars['senha'] ?? '';
		$tipo = $postVars['tipo'] ?? '';
		$cpf = $postVars['cpf'] ?? '';
		//campos para permissão
		$excluirAluno = $postVars['checkExcluirAluno'] ?? '0';
		$excluirProfessor = $postVars['checkExcluirProfessor'] ?? '0';
		$excluirDisciplina = $postVars['checkDisciplina'] ?? '0';
		$excluirUsuario = $postVars['checkExcluirUsuario'] ?? '0';
		$menuAlunos = $postVars['checkMenuAlunos'] ?? '0';
		$menuProfessores = $postVars['checkMenuProfessores'] ?? '0';
		$menuAulas = $postVars['checkMenuAulas'] ?? '0';
		$menuFrequencias = $postVars['checkMenuFrequencias'] ?? '0';
		$btnNovoUsuario = $postVars['checkBtnNovoUsuario'] ?? '0';
		$menuPresenca = $postVars['checkMenuPresenca'] ?? '0';
		$menuDisciplinas = $postVars['checkMenuDisciplinas'] ?? '0';
		
				//obtém o usuário do banco de dados
		$obUser = EntityUser::getUserById($id);
		
		//Valida a instancia
		if(!$obUser instanceof EntityUser){
			$request->getRouter()->redirect('/admin/users');
		}
		
		//instancia classe pra verificar CPF
		$validaCpf = new CPF($cpf);
		
		//busca usuário pelo CPF sem a maskara
		$obUserCPF = EntityUser::getUserByCPF($validaCpf->getValue());

		//verifica se o CPF já está sendo usado por outro usuário
		if($obUserCPF instanceof EntityUser && $obUserCPF->id != $id){
			$request->getRouter()->redirect('/admin/users/'.$id.'/edit?statusMessage=cpfDuplicated');
		}
		
		
		//Valida o email do usuário
		$obUserEmail = EntityUser::getUserByEmail($email);
		
		//verifica se o E-MAIL já está sendo usado por outro usuário
		if($obUserEmail instanceof EntityUser && $obUserEmail->id != $id){
			$request->getRouter()->redirect('/admin/users/'.$id.'/edit?statusMessage=emailDuplicated');
		}
		
		//Atualiza a instância
		$obUser->nome = $nome;
		$obUser->email = $email;
		$obUser->tipo = $tipo;
		$obUser->cpf = $validaCpf->getValue(); //cpf sem formatação
		$obUser->senha = $senha;
		
		//campos para permissão
		$obUser->excluirAluno = $excluirAluno;
		$obUser->excluirProfessor = $excluirProfessor;
		$obUser->excluirDisciplina = $excluirDisciplina;
		$obUser->excluirUsuario = $excluirUsuario;
		$obUser->menuAlunos = $menuAlunos;
		$obUser->menuProfessores = $menuProfessores;
		$obUser->menuAulas = $menuAulas;
		$obUser->menuFrequencias = $menuFrequencias;
		$obUser->btnNovoUsuario = $btnNovoUsuario;
		$obUser->menuPresenca = $menuPresenca;
		$obUser->menuDisciplinas = $menuDisciplinas;
		
		//grava as informações
		$obUser->atualizar();
		
		
		//Atualiza a sessão de usuário
		Funcoes::getSessaoPermissoes($obUser);
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/admin/users/'.$obUser->id.'/edit?statusMessage=updated');
		
		
	}
	
	
	//Metodo responsávelpor retornar o formulário de Exclusão de um usuário
	public static function getDeleteUser($request,$id){
		//obtém o usuário do banco de dados
		$obUser = EntityUser::getUserById($id);
		
		//Valida a instancia
		if(!$obUser instanceof EntityUser){
			$request->getRouter()->redirect('/admin/users');
		}
		
		
		
		//Conteúdo do Formulário
		$content = View::render('admin/modules/users/delete',[
				'nome' => $obUser->nome,
				'email' => $obUser->email
				
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Excluir Usuário > SISCAPS', $content,'users', self::$hidden);
		
	}
	
	//Metodo responsável por Excluir um usuário
	public static function setDeleteUser($request,$id){
		//obtém o usuário do banco de dados
		$obUser = EntityUser::getUserById($id);
		
		//Valida a instancia
		if(!$obUser instanceof EntityUser){
			$request->getRouter()->redirect('/admin/users');
		}
		
			
		//Exclui o usuário
		$obUser->excluir($id);
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/admin/users?statusMessage=deleted');
		
		
	}
	
	
	//Metodo responsávelpor retornar o formulário de Captura de foto do User
	public static function getPhoto($request,$id){
	    
	    $obUser = EntityUser::getUserById($id);
	    
	    //Conteúdo do Formulário
	    $content = View::render('admin/modules/alunos/formPhoto',[
	        'title' => 'Usuários > Capturar foto',
	        'aluno' => $obUser->id.' '.$obUser->nome,
	        'id' => $obUser->id
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPanel('Editar User > Cursinho', $content,'users', self::$hidden);
	    
	}
	
	
	//Metodo responsávelpor retornar o formulário de Captura de foto do User
	public static function setPhoto($request){
	    
	    //Post Vars
	    $postVars = $request->getPostVars();
	    $fileVars = $request->getFileVars();
	    
	    
	    $obUser = EntityUser::getUserById($postVars['id']);
	    
	    if(!empty($fileVars['fImage']['name'] != '')){
	        $postVars['image'] = '';
	        
	        Upload::setUploadImagesUser($request);
	        //Redireciona o usuário
	        $request->getRouter()->redirect('/admin/users/'.$obUser->id.'/edit?statusMessage=updated');
	    }
	    
	    if ($postVars['image'] != ''){
	        
	        //MÉTODO RESPONSÁVEL POR FAZER O UPLOADO DA IMAGE VINDA DA WEB CAM DO PROFESSOR
	        Upload::setUploadImagesWebCamUser($request);
	        
	        //Redireciona o usuário
	        $request->getRouter()->redirect('/admin/users/'.$obUser->id.'/edit?statusMessage=updated');
	    }
	    
	    $request->getRouter()->redirect('/admin/users/'.$obUser->id.'/edit?statusMessage=semfoto');
	    
	    
	}
	
	
}