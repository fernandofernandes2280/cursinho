<?php

namespace App\Auth;

class Permission{
	public const ALL = '*';

	public const DASHBOARD_VIEW = 'dashboard.view';

	public const ALUNOS_VIEW = 'alunos.view';
	public const ALUNOS_CREATE = 'alunos.create';
	public const ALUNOS_UPDATE = 'alunos.update';
	public const ALUNOS_DELETE = 'alunos.delete';
	public const ALUNOS_PHOTO = 'alunos.photo';
	public const ALUNOS_CARTEIRA = 'alunos.carteira';
	public const ALUNOS_INATIVAR = 'alunos.inativar';

	public const PROFESSORES_VIEW = 'professores.view';
	public const PROFESSORES_CREATE = 'professores.create';
	public const PROFESSORES_UPDATE = 'professores.update';
	public const PROFESSORES_DELETE = 'professores.delete';
	public const PROFESSORES_PHOTO = 'professores.photo';

	public const DISCIPLINAS_VIEW = 'disciplinas.view';
	public const DISCIPLINAS_CREATE = 'disciplinas.create';
	public const DISCIPLINAS_UPDATE = 'disciplinas.update';
	public const DISCIPLINAS_DELETE = 'disciplinas.delete';
	public const DISCIPLINAS_LINK_PROFESSOR = 'disciplinas.link-professor';

	public const AULAS_VIEW = 'aulas.view';
	public const AULAS_CREATE = 'aulas.create';
	public const AULAS_UPDATE = 'aulas.update';
	public const AULAS_DELETE = 'aulas.delete';
	public const AULAS_PRESENTES = 'aulas.presentes';

	public const FREQUENCIAS_VIEW = 'frequencias.view';
	public const FREQUENCIAS_UPDATE = 'frequencias.update';
	public const FREQUENCIAS_CONFIRM = 'frequencias.confirm';
	public const FREQUENCIAS_QRCODE = 'frequencias.qrcode';

	public const USUARIOS_VIEW = 'usuarios.view';
	public const USUARIOS_CREATE = 'usuarios.create';
	public const USUARIOS_UPDATE = 'usuarios.update';
	public const USUARIOS_DELETE = 'usuarios.delete';
	public const USUARIOS_PHOTO = 'usuarios.photo';
	public const USUARIOS_PASSWORD = 'usuarios.password';

	public const RELATORIOS_VIEW = 'relatorios.view';
	public const CONFIGURACOES_VIEW = 'configuracoes.view';
	public const CONFIGURACOES_UPDATE = 'configuracoes.update';

	private const GROUPS = [
		'Geral' => [
			self::DASHBOARD_VIEW => 'Acessar dashboard',
		],
		'Alunos' => [
			self::ALUNOS_VIEW => 'Visualizar alunos',
			self::ALUNOS_CREATE => 'Cadastrar alunos',
			self::ALUNOS_UPDATE => 'Editar alunos',
			self::ALUNOS_DELETE => 'Excluir alunos',
			self::ALUNOS_PHOTO => 'Capturar foto',
			self::ALUNOS_CARTEIRA => 'Emitir carteira',
			self::ALUNOS_INATIVAR => 'Inativar alunos',
		],
		'Professores' => [
			self::PROFESSORES_VIEW => 'Visualizar professores',
			self::PROFESSORES_CREATE => 'Cadastrar professores',
			self::PROFESSORES_UPDATE => 'Editar professores',
			self::PROFESSORES_DELETE => 'Excluir professores',
			self::PROFESSORES_PHOTO => 'Capturar foto',
		],
		'Disciplinas' => [
			self::DISCIPLINAS_VIEW => 'Visualizar disciplinas',
			self::DISCIPLINAS_CREATE => 'Cadastrar disciplinas',
			self::DISCIPLINAS_UPDATE => 'Editar disciplinas',
			self::DISCIPLINAS_DELETE => 'Excluir disciplinas',
			self::DISCIPLINAS_LINK_PROFESSOR => 'Vincular ao professor',
		],
		'Aulas' => [
			self::AULAS_VIEW => 'Visualizar aulas',
			self::AULAS_CREATE => 'Cadastrar aulas',
			self::AULAS_UPDATE => 'Editar aulas',
			self::AULAS_DELETE => 'Excluir aulas',
			self::AULAS_PRESENTES => 'Listar frequencia da aula',
		],
		'Frequencias' => [
			self::FREQUENCIAS_VIEW => 'Visualizar frequencias',
			self::FREQUENCIAS_UPDATE => 'Editar frequencias',
			self::FREQUENCIAS_CONFIRM => 'Confirmar presenca',
			self::FREQUENCIAS_QRCODE => 'Ler QR Code',
		],
		'Usuarios' => [
			self::USUARIOS_VIEW => 'Visualizar usuarios',
			self::USUARIOS_CREATE => 'Cadastrar usuarios',
			self::USUARIOS_UPDATE => 'Editar usuarios',
			self::USUARIOS_DELETE => 'Excluir usuarios',
			self::USUARIOS_PHOTO => 'Capturar foto',
		],
		'Relatorios' => [
			self::RELATORIOS_VIEW => 'Visualizar relatorios',
		],
		'Configuracoes' => [
			self::CONFIGURACOES_VIEW => 'Visualizar configuracoes',
			self::CONFIGURACOES_UPDATE => 'Editar configuracoes',
		],
	];

	private const LEGACY_ALIASES = [
		'menuAlunos' => self::ALUNOS_VIEW,
		'menuProfessores' => self::PROFESSORES_VIEW,
		'menuAulas' => self::AULAS_VIEW,
		'menuFrequencias' => self::FREQUENCIAS_VIEW,
		'menuDisciplinas' => self::DISCIPLINAS_VIEW,
		'menuUsuarios' => self::USUARIOS_VIEW,
		'menuConfiguracoes' => self::CONFIGURACOES_VIEW,
		'btnNovoUsuario' => self::USUARIOS_CREATE,
		'excluirAluno' => self::ALUNOS_DELETE,
		'excluirProfessor' => self::PROFESSORES_DELETE,
		'excluirDisciplina' => self::DISCIPLINAS_DELETE,
		'excluirUsuario' => self::USUARIOS_DELETE,
	];

	private const LEGACY_FIELDS = [
		self::ALUNOS_VIEW => 'menuAlunos',
		self::ALUNOS_CREATE => 'menuAlunos',
		self::ALUNOS_UPDATE => 'menuAlunos',
		self::ALUNOS_PHOTO => 'menuAlunos',
		self::ALUNOS_CARTEIRA => 'menuAlunos',
		self::ALUNOS_INATIVAR => 'menuAlunos',
		self::ALUNOS_DELETE => 'excluirAluno',

		self::PROFESSORES_VIEW => 'menuProfessores',
		self::PROFESSORES_CREATE => 'menuProfessores',
		self::PROFESSORES_UPDATE => 'menuProfessores',
		self::PROFESSORES_PHOTO => 'menuProfessores',
		self::PROFESSORES_DELETE => 'excluirProfessor',

		self::DISCIPLINAS_VIEW => 'menuDisciplinas',
		self::DISCIPLINAS_CREATE => 'menuDisciplinas',
		self::DISCIPLINAS_UPDATE => 'menuDisciplinas',
		self::DISCIPLINAS_LINK_PROFESSOR => 'menuDisciplinas',
		self::DISCIPLINAS_DELETE => 'excluirDisciplina',

		self::AULAS_VIEW => 'menuAulas',
		self::AULAS_CREATE => 'menuAulas',
		self::AULAS_UPDATE => 'menuAulas',
		self::AULAS_DELETE => 'menuAulas',
		self::AULAS_PRESENTES => 'menuAulas',

		self::FREQUENCIAS_VIEW => 'menuFrequencias',
		self::FREQUENCIAS_UPDATE => 'menuFrequencias',
		self::FREQUENCIAS_CONFIRM => 'menuFrequencias',
		self::FREQUENCIAS_QRCODE => 'menuFrequencias',

		self::USUARIOS_VIEW => 'menuUsuarios',
		self::USUARIOS_UPDATE => 'menuUsuarios',
		self::USUARIOS_PHOTO => 'menuUsuarios',
		self::USUARIOS_CREATE => 'btnNovoUsuario',
		self::USUARIOS_DELETE => 'excluirUsuario',

		self::RELATORIOS_VIEW => 'menuAlunos',
		self::CONFIGURACOES_VIEW => 'menuConfiguracoes',
		self::CONFIGURACOES_UPDATE => 'menuConfiguracoes',
	];

	private const ROLE_DEFAULTS = [
		'Admin' => [
			self::ALL,
		],
		'Operador' => [
			self::DASHBOARD_VIEW,
			self::ALUNOS_VIEW,
			self::ALUNOS_CREATE,
			self::ALUNOS_UPDATE,
			self::ALUNOS_DELETE,
			self::ALUNOS_PHOTO,
			self::ALUNOS_CARTEIRA,
			self::ALUNOS_INATIVAR,
			self::PROFESSORES_VIEW,
			self::PROFESSORES_CREATE,
			self::PROFESSORES_UPDATE,
			self::PROFESSORES_DELETE,
			self::PROFESSORES_PHOTO,
			self::DISCIPLINAS_VIEW,
			self::DISCIPLINAS_CREATE,
			self::DISCIPLINAS_UPDATE,
			self::DISCIPLINAS_DELETE,
			self::DISCIPLINAS_LINK_PROFESSOR,
			self::AULAS_VIEW,
			self::AULAS_CREATE,
			self::AULAS_UPDATE,
			self::AULAS_PRESENTES,
			self::FREQUENCIAS_VIEW,
			self::FREQUENCIAS_UPDATE,
			self::FREQUENCIAS_CONFIRM,
			self::FREQUENCIAS_QRCODE,
			self::RELATORIOS_VIEW,
			self::CONFIGURACOES_VIEW,
			self::CONFIGURACOES_UPDATE,
		],
	];

	private const ROLE_DENIES = [
		'Operador' => [
			self::USUARIOS_VIEW,
			self::USUARIOS_CREATE,
			self::USUARIOS_UPDATE,
			self::USUARIOS_DELETE,
			self::USUARIOS_PHOTO,
			self::AULAS_DELETE,
		],
	];

	public static function normalize($permission){
		return self::LEGACY_ALIASES[$permission] ?? $permission;
	}

	public static function legacyField($permission){
		$permission = self::normalize($permission);

		return self::LEGACY_FIELDS[$permission] ?? null;
	}

	public static function isAllowed($user, $permission){
		$permission = self::normalize($permission);
		$tipo = $user->tipo ?? '';

		if(self::roleDenies($tipo, $permission)){
			return false;
		}

		if(self::roleAllows($tipo, $permission)){
			return true;
		}

		$explicitPermissions = self::getExplicitPermissions($user);
		if($explicitPermissions !== null){
			return in_array($permission, $explicitPermissions, true);
		}

		$field = self::legacyField($permission);
		if($field === null){
			return false;
		}

		return (int)($user->{$field} ?? 0) === 1;
	}

	public static function groups(){
		return self::GROUPS;
	}

	public static function listPermissions(){
		$permissions = [];

		foreach(self::GROUPS as $items){
			foreach($items as $permission => $label){
				$permissions[$permission] = $label;
			}
		}

		return $permissions;
	}

	public static function getExplicitPermissions($user){
		if(!isset($user->permissoes) || $user->permissoes === null || $user->permissoes === ''){
			return null;
		}

		$permissions = json_decode($user->permissoes, true);
		if(!is_array($permissions)){
			return null;
		}

		return array_values(array_unique(array_map([self::class, 'normalize'], $permissions)));
	}

	public static function encodePermissions($permissions){
		$allowed = array_keys(self::listPermissions());
		$permissions = array_map([self::class, 'normalize'], (array)$permissions);
		$permissions = array_values(array_intersect($allowed, array_unique($permissions)));

		return json_encode($permissions);
	}

	public static function roleAllows($tipo, $permission){
		$permission = self::normalize($permission);
		$permissions = self::ROLE_DEFAULTS[$tipo] ?? [];

		return in_array(self::ALL, $permissions, true) || in_array($permission, $permissions, true);
	}

	public static function roleDenies($tipo, $permission){
		$permission = self::normalize($permission);

		return in_array($permission, self::ROLE_DENIES[$tipo] ?? [], true);
	}
}
