<?php

namespace App\Controller\Painel;

use App\Auth\Permission;
use \App\Utils\View;
use \App\Model\Entity\User as EntityUser;
use Bissolli\ValidadorCpfCnpj\CPF;
use \App\Utils\Funcoes;
use App\Controller\File\Upload;
use App\Session\User\Login;

class User extends Page{
	
	//esconde busca rápida de prontuário no navBar
	private static $hidden = 'hidden';

	private static function canManageUsers(){
		return Login::isAdmin();
	}

	private static function checkUserAccess($request, $id){
		$loggedUserId = (int)($_SESSION['usuario']['id'] ?? 0);

		if(self::canManageUsers() || $loggedUserId == (int)$id){
			return;
		}

		$request->getRouter()->redirect('/users');
	}

	private static function checkedPermission($obUser, $permission){
		return Login::getPermissionValueForUser($obUser, $permission) == 1 ? 'checked' : '';
	}

	private static function normalizePermissionsForRole($tipo, $permissions){
		$permissions = array_map([Permission::class, 'normalize'], (array)$permissions);

		foreach(array_keys(Permission::listPermissions()) as $permission){
			if(Permission::roleAllows($tipo, $permission)){
				$permissions[] = $permission;
			}
		}

		$permissions = array_filter(array_unique($permissions), function($permission) use ($tipo){
			return !Permission::roleDenies($tipo, $permission);
		});

		return array_values($permissions);
	}

	private static function legacyPermissionsFromGranular($permissions){
		$hasAny = function($prefix) use ($permissions){
			foreach($permissions as $permission){
				if(strpos($permission, $prefix.'.') === 0){
					return '1';
				}
			}

			return '0';
		};

		return [
			'excluirAluno' => in_array(Permission::ALUNOS_DELETE, $permissions, true) ? '1' : '0',
			'excluirProfessor' => in_array(Permission::PROFESSORES_DELETE, $permissions, true) ? '1' : '0',
			'excluirDisciplina' => in_array(Permission::DISCIPLINAS_DELETE, $permissions, true) ? '1' : '0',
			'excluirUsuario' => in_array(Permission::USUARIOS_DELETE, $permissions, true) ? '1' : '0',
			'menuAlunos' => $hasAny('alunos'),
			'menuProfessores' => $hasAny('professores'),
			'menuAulas' => $hasAny('aulas'),
			'menuFrequencias' => $hasAny('frequencias'),
			'btnNovoUsuario' => in_array(Permission::USUARIOS_CREATE, $permissions, true) ? '1' : '0',
			'menuDisciplinas' => $hasAny('disciplinas'),
		];
	}

	private static function applyPermissionValues($obUser, $permissions){
		$legacy = self::legacyPermissionsFromGranular($permissions);

		foreach($legacy as $field => $value){
			$obUser->{$field} = $value;
		}

		$obUser->permissoes = Permission::encodePermissions($permissions);
	}

	private static function getOldPermissions($old){
		$permissions = $old['permissoes'] ?? [];
		if(is_string($permissions)){
			$permissions = json_decode($permissions, true) ?: [];
		}

		return $permissions;
	}

	private static function renderPermissionGroups($obUser = null, $tipo = '', $oldPermissions = []){
		$oldPermissions = self::normalizePermissionsForRole($tipo, $oldPermissions);
		$html = '';

		foreach(Permission::groups() as $group => $permissions){
			$html .= '<section class="user-permission-group">';
			$html .= '<h6>'.htmlspecialchars($group, ENT_QUOTES, 'UTF-8').'</h6>';
			$html .= '<div class="user-permissions-grid">';

			foreach($permissions as $permission => $label){
				$isChecked = $obUser instanceof EntityUser
					? Login::getPermissionValueForUser($obUser, $permission) == 1
					: in_array($permission, $oldPermissions, true);

				$checked = $isChecked ? 'checked' : '';
				$escapedPermission = htmlspecialchars($permission, ENT_QUOTES, 'UTF-8');
				$html .= '<label><input type="checkbox" '.$checked.' name="permissions[]" value="'.$escapedPermission.'" data-permission="'.$escapedPermission.'"> '.self::formatPermissionLabel($label).'</label>';
			}

			$html .= '</div>';
			$html .= '</section>';
		}

		return $html;
	}

	private static function formatPermissionLabel($label){
		$parts = explode(' ', $label, 2);
		$action = $parts[0] ?? $label;
		$target = $parts[1] ?? '';

		return '<span class="user-permission-action">'.htmlspecialchars($action, ENT_QUOTES, 'UTF-8').'</span><span>'.htmlspecialchars($target, ENT_QUOTES, 'UTF-8').'</span>';
	}

	private static function getRolePermissionRules(){
		$permissions = array_keys(Permission::listPermissions());
		$rules = [
			'Admin' => [
				'default' => $permissions,
				'denied' => [],
			],
			'Operador' => [
				'default' => [],
				'denied' => [],
			],
			'Visitante' => [
				'default' => [],
				'denied' => [],
			],
		];

		foreach($permissions as $permission){
			if(Permission::roleAllows('Operador', $permission)){
				$rules['Operador']['default'][] = $permission;
			}

			if(Permission::roleDenies('Operador', $permission)){
				$rules['Operador']['denied'][] = $permission;
			}
		}

		return json_encode($rules);
	}
	
	//Método responsavel por obter a renderização dos itens de usuários para a página
	private static function getUserItems($request){
		//Usuários
		$itens = '';
		
		//Mostra todos os usuários para quem pode gerenciar usuários e apenas o próprio cadastro para os demais.
		if(self::canManageUsers()){
		    $where = null;
		}else{
		    $where = 'id = '.$_SESSION['usuario']['id'];
		} 
		
		//Resultados da Página
		$results = EntityUser::getUsers($where, 'id DESC');
		$reload = rand();
		//Renderiza o item
		while ($obUser = $results->fetchObject(EntityUser::class)) {
			$foto = strlen((string)$obUser->foto) ? $obUser->foto : 'profile.png';
		
			//View de depoimentos
			$itens.= View::render('painel/modules/users/item',[
					'id' => $obUser->id,
					'nome' => $obUser->nome,
					'email' => $obUser->email,
					'cpf' => Funcoes::mask($obUser->cpf, '###.###.###-##') ,
					'tipo' => $obUser->tipo,
					'foto' => $foto.'?var='.$reload,
					'excluirUsuarioVisivel' => self::canManageUsers() ? permissaoExcluirUsuario : 'hidden'
			]);
		}
		
		
		//Retorna os depoimentos
		return $itens;
		
	}
	
	
	//Método responsavel por renderizar a view de Listagem de USuários
	public static function getUsers($request){
		$itens = self::getUserItems($request);
		
		//Conteúdo da Home
		$content = View::render('painel/modules/users/index',[
				'itens' => $itens,
				'statusMessage' => Funcoes::getStatus($request),
				
				
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
	    $old = Funcoes::pullOldInput('usuario.novo');
	    $statusMessage = $queryParams['statusMessage'] ?? '';
	    $cpfUser = $queryParams['cpfUser'] ?? ($old['cpf'] ?? '');
	    
	    //instancia classe pra verificar CPF
	    $validaCpf = new CPF($cpfUser);
	    
	    //verifica se é válido o cpf
	    if (!$validaCpf->isValid()){
	        
	        $request->getRouter()->redirect('/users?statusMessage=cpfInvalid');
	    }
	    
	    
	    //busca usuário pelo CPF sem a maskara
	    $ob = EntityUser::getUserByCPF($validaCpf->getValue());
	    //verifica se o cpf já está cadastrado
	    if($ob instanceof EntityUser && !in_array($statusMessage, ['cpfDuplicated', 'cpfduplicated'])){
	        $request->getRouter()->redirect('/users?statusMessage=duplicated');
	    }
	    
	    $tipo = $old['tipo'] ?? 'Visitante';
	    $oldPermissions = self::getOldPermissions($old);
	    
		//Conteúdo do Formulário
		$content = View::render('painel/modules/users/form',[
				'title' => 'Usuários > Novo',
		        'id' => '',
		        'nome' => $old['nome'] ?? '',
		        'email' => $old['email'] ?? '',
		        'cpf' => $old['cpf'] ?? $validaCpf->getValue(),
		        'senha' => '',
				'statusMessage' => Funcoes::getStatus($request),
		        'selectedAdmin'=> $tipo === 'Admin' ? 'selected' : '',
		        'selectedOperador'=> $tipo === 'Operador' ? 'selected' : '',
		        'selectedVisitante'=> $tipo === 'Visitante' ? 'selected' : '',
		        'foto' => 'profile.png',  
		        'required' => 'required',
		        'ponteiro' => 'pointer-events: none;',
		        'btnNovoUsuarioVisivel' => permissaoBtnNovoUsuario,
		        'permissionGroups' => self::renderPermissionGroups(null, $tipo, $oldPermissions),
		        'rolePermissionRules' => self::getRolePermissionRules(),
		        'permissoesVisivel' => permissoes,
		        'habilitado' => ''
				
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
		$permissions = self::normalizePermissionsForRole($tipo, $postVars['permissions'] ?? []);
		$legacyPermissions = self::legacyPermissionsFromGranular($permissions);

		//Cria sessão temporária com os dados do form
		EntityUser::getSessaoDados(array_merge($postVars, [
			'permissoes' => $permissions,
			'excluirAluno' => $legacyPermissions['excluirAluno'],
			'excluirProfessor' => $legacyPermissions['excluirProfessor'],
			'excluirDisciplina' => $legacyPermissions['excluirDisciplina'],
			'excluirUsuario' => $legacyPermissions['excluirUsuario'],
			'menuAlunos' => $legacyPermissions['menuAlunos'],
			'menuProfessores' => $legacyPermissions['menuProfessores'],
			'menuAulas' => $legacyPermissions['menuAulas'],
			'menuFrequencias' => $legacyPermissions['menuFrequencias'],
			'btnNovoUsuario' => $legacyPermissions['btnNovoUsuario'],
			'menuDisciplinas' => $legacyPermissions['menuDisciplinas'],
		]));
		
		//instancia classe pra verificar CPF
		$validaCpf = new CPF($cpf);
		
		//busca usuário pelo CPF sem a maskara
		$obUser = EntityUser::getUserByCPF($validaCpf->getValue());
		
		if($obUser instanceof EntityUser){
		 
		        $request->getRouter()->redirect('/users/new?'.http_build_query([
		            'cpfUser' => $validaCpf->getValue(),
		            'statusMessage' => 'cpfDuplicated'
		        ]));
		}
		
		//Valida o email do usuário
		$obUserEmail = EntityUser::getUserByEmail($email);
		
		if($obUserEmail instanceof EntityUser ){
		    $request->getRouter()->redirect('/users/new?'.http_build_query([
		        'cpfUser' => $validaCpf->getValue(),
		        'statusMessage' => 'emailDuplicated'
		    ]));
		}
				
		//Nova instancia de Usuário
		$obUser = new EntityUser;
		$obUser->nome = $nome;
		$obUser->email = $email;
		$obUser->cpf = $validaCpf->getValue(); //cpf sem formatação
		$obUser->tipo = $tipo;
		//$obUser->senha = password_hash($senha,PASSWORD_DEFAULT);
		$obUser->senha = $senha;
		
		self::applyPermissionValues($obUser, $permissions);
		
		//grava as informações
		$obUser->cadastrar();
		
		//Atualiza a sessão de usuário
		Login::login($obUser);
		
		
		//encerra sessão com os dados do form
		EntityUser::getFinalizaSessaoDados();
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/users/'.$obUser->id.'/edit?statusMessage=created');
		
	}
	

	
	
	//Metodo responsávelpor retornar o formulário de Edição de um Usuário
	public static function getEditUser($request,$id){
		
		self::checkUserAccess($request, $id);
		
				
		//obtém o usuário do banco de dados
		$obUser = EntityUser::getUserById($id);
		
		//Valida a instancia
		if(!$obUser instanceof EntityUser){
			$request->getRouter()->redirect('/users');
		}
		
		$obUser->tipo == 'Admin' ? $selectedAdmin = 'selected' : $selectedAdmin = '' ;
		$obUser->tipo == 'Visitante' ? $selectedVisitante = 'selected' : $selectedVisitante = '' ;
		$obUser->tipo == 'Operador' ? $selectedOperador = 'selected' : $selectedOperador = '' ;
		
		$reload = rand();
		//Conteúdo do Formulário
		$content = View::render('painel/modules/users/form',[
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
			          'foto' => $obUser->foto.'?var='.$reload,
		        'required' => '',
		         'ponteiro' => '',
			    'btnNovoUsuarioVisivel' => permissaoBtnNovoUsuario,
		    'permissionGroups' => self::renderPermissionGroups($obUser, $obUser->tipo),
		    'rolePermissionRules' => self::getRolePermissionRules(),
		    'permissoesVisivel' => permissoes,
		    'habilitado' => habilitaCPFTIPO,
		         
				
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Editar Usuário > SISCAPS', $content,'users', self::$hidden);
		
	}
	
	//Metodo responsável por gravar a atualizacao de um usuário
	public static function setEditUser($request,$id){
		self::checkUserAccess($request, $id);

		//Post Vars
		$postVars = $request->getPostVars();
		
				
		$nome = $postVars['nome'] ?? '';
		$email = $postVars['email'] ?? '';
		$senha = $postVars['senha'] ?? '';
		$tipo = $postVars['tipo'] ?? '';
		$cpf = $postVars['cpf'] ?? '';
		$permissions = self::normalizePermissionsForRole($tipo, $postVars['permissions'] ?? []);
		
				//obtém o usuário do banco de dados
		$obUser = EntityUser::getUserById($id);
		
		//Valida a instancia
		if(!$obUser instanceof EntityUser){
			$request->getRouter()->redirect('/users');
		}

		if(!self::canManageUsers()){
			$tipo = $obUser->tipo;
			$cpf = $obUser->cpf;
			$permissions = Permission::getExplicitPermissions($obUser);
			if($permissions === null){
				$permissions = array_filter(array_keys(Permission::listPermissions()), function($permission) use ($obUser){
					return Login::getPermissionValueForUser($obUser, $permission) == 1;
				});
			}
		}else{
			$permissions = self::normalizePermissionsForRole($tipo, $permissions);
		}
		
		//instancia classe pra verificar CPF
		$validaCpf = new CPF($cpf);
		
		//busca usuário pelo CPF sem a maskara
		$obUserCPF = EntityUser::getUserByCPF($validaCpf->getValue());

		//verifica se o CPF já está sendo usado por outro usuário
		if($obUserCPF instanceof EntityUser && $obUserCPF->id != $id){
			$request->getRouter()->redirect('/users/'.$id.'/edit?statusMessage=cpfDuplicated');
		}
		
		
		//Valida o email do usuário
		$obUserEmail = EntityUser::getUserByEmail($email);
		
		//verifica se o E-MAIL já está sendo usado por outro usuário
		if($obUserEmail instanceof EntityUser && $obUserEmail->id != $id){
			$request->getRouter()->redirect('/users/'.$id.'/edit?statusMessage=emailDuplicated');
		}
		
		//Atualiza a instância
		$obUser->nome = $nome;
		$obUser->email = $email;
		$obUser->tipo = $tipo;
		$obUser->cpf = $validaCpf->getValue(); //cpf sem formatação
		$obUser->senha = $senha;
		
		self::applyPermissionValues($obUser, $permissions);
		
		//grava as informações
		$obUser->atualizar();
		
		
		//Atualiza a sessão de usuário
		Funcoes::getSessaoPermissoes($obUser);
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/users/'.$obUser->id.'/edit?statusMessage=updated');
		
		
	}
	
	
	//Metodo responsávelpor retornar o formulário de Exclusão de um usuário
	public static function getDeleteUser($request,$id){
		//obtém o usuário do banco de dados
		$obUser = EntityUser::getUserById($id);
		
		//Valida a instancia
		if(!$obUser instanceof EntityUser){
			$request->getRouter()->redirect('/users');
		}
		
		
		
		//Conteúdo do Formulário
		$content = View::render('painel/modules/users/delete',[
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
			$request->getRouter()->redirect('/users');
		}
		
			
		//Exclui o usuário
		$obUser->excluir($id);
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/users?statusMessage=deleted');
		
		
	}
	
	
	//Metodo responsávelpor retornar o formulário de Captura de foto do User
	public static function getPhoto($request,$id){
	    self::checkUserAccess($request, $id);
	    
	    $obUser = EntityUser::getUserById($id);
	    
	    //Conteúdo do Formulário
	    $content = View::render('painel/modules/alunos/formPhoto',[
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

	    self::checkUserAccess($request, $postVars['id'] ?? 0);
	    
	    
	    $obUser = EntityUser::getUserById($postVars['id']);
	    
	    if(!empty($fileVars['fImage']['name'] != '')){
	        $postVars['image'] = '';
	        
	        Upload::setUploadImagesUser($request);
	        //Redireciona o usuário
	        $request->getRouter()->redirect('/users/'.$obUser->id.'/edit?statusMessage=updated');
	    }
	    
	    if ($postVars['image'] != ''){
	        
	        //MÉTODO RESPONSÁVEL POR FAZER O UPLOADO DA IMAGE VINDA DA WEB CAM DO PROFESSOR
	        Upload::setUploadImagesWebCamUser($request);
	        
	        //Redireciona o usuário
	        $request->getRouter()->redirect('/users/'.$obUser->id.'/edit?statusMessage=updated');
	    }
	    
	    $request->getRouter()->redirect('/users/'.$obUser->id.'/edit?statusMessage=semfoto');
	    
	    
	}
	
	
}
