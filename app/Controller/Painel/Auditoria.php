<?php

namespace App\Controller\Painel;

use App\Model\Entity\Auditoria as EntityAuditoria;
use App\Model\Entity\User as EntityUser;
use App\Utils\View;
use WilliamCosta\DatabaseManager\Pagination;

class Auditoria extends Page{
	private static $hidden = 'hidden';

	private const ACTION_LABELS = [
		'criar' => 'Criação',
		'atualizar' => 'Atualização',
		'excluir' => 'Exclusão',
		'atualizar_foto' => 'Foto',
		'inativar_lote' => 'Inativação em lote',
	];

	private const MODULE_LABELS = [
		'alunos' => 'Alunos',
		'usuarios' => 'Usuários',
		'inativar' => 'Inativar alunos',
		'precadastro' => 'Pré-cadastro',
		'aulas' => 'Aulas',
		'professores' => 'Professores',
		'disciplinas' => 'Disciplinas',
	];

	private static function escape($value){
		return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
	}

	private static function getFilters($request){
		$queryParams = $request->getQueryParams();

		return [
			'dataInicial' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($queryParams['dataInicial'] ?? '')) ? $queryParams['dataInicial'] : '',
			'dataFinal' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($queryParams['dataFinal'] ?? '')) ? $queryParams['dataFinal'] : '',
			'usuario' => preg_match('/^\d+$/', (string)($queryParams['usuario'] ?? '')) ? $queryParams['usuario'] : '',
			'modulo' => trim((string)($queryParams['modulo'] ?? '')),
			'acao' => trim((string)($queryParams['acao'] ?? '')),
			'busca' => trim((string)($queryParams['busca'] ?? '')),
		];
	}

	private static function getOptions($items, $selected, $labels = []){
		$html = '<option value="">Todos</option>';

		foreach($items as $value){
			$value = (string)$value;
			$label = $labels[$value] ?? $value;
			$html .= '<option value="'.self::escape($value).'" '.($selected === $value ? 'selected' : '').'>'.self::escape($label).'</option>';
		}

		return $html;
	}

	private static function getUsuarioOptions($selected){
		$html = '<option value="">Todos</option>';
		$results = EntityUser::getUsers(null, 'nome ASC', null, 'id,nome');

		while($obUser = $results->fetchObject(EntityUser::class)){
			$id = (string)$obUser->id;
			$html .= '<option value="'.self::escape($id).'" '.($selected === $id ? 'selected' : '').'>'.self::escape($obUser->nome).'</option>';
		}

		return $html;
	}

	private static function mergeKnownAndStored($known, $stored){
		return array_values(array_unique(array_merge(array_keys($known), $stored)));
	}

	private static function formatDateTime($date){
		$timestamp = strtotime((string)$date);

		return $timestamp ? date('d/m/Y H:i:s', $timestamp) : '';
	}

	private static function formatJson($json){
		if($json === null || $json === ''){
			return 'Sem dados.';
		}

		$decoded = json_decode($json, true);
		if($decoded === null){
			return self::escape($json);
		}

		return self::escape(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	}

	private static function getItems($rows){
		$items = '';

		foreach($rows as $row){
			$action = $row['acao'] ?? '';
			$module = $row['modulo'] ?? '';

			$items .= View::render('painel/modules/auditoria/item', [
				'id' => self::escape($row['id'] ?? ''),
				'criadoEm' => self::escape(self::formatDateTime($row['criado_em'] ?? '')),
				'usuario' => self::escape($row['usuario_nome'] ?? 'Sistema'),
				'tipoUsuario' => self::escape($row['usuario_tipo'] ?? ''),
				'modulo' => self::escape(self::MODULE_LABELS[$module] ?? $module),
				'acao' => self::escape(self::ACTION_LABELS[$action] ?? $action),
				'entidade' => self::escape(trim(($row['entidade'] ?? '').' #'.($row['entidade_id'] ?? ''), ' #')),
				'descricao' => self::escape($row['descricao'] ?? ''),
			]);
		}

		if($items === ''){
			return '<tr><td colspan="8" class="text-center">Nenhum registro de auditoria encontrado.</td></tr>';
		}

		return $items;
	}

	public static function getAuditoria($request){
		$filters = self::getFilters($request);
		$queryParams = $request->getQueryParams();
		$paginaAtual = $queryParams['page'] ?? 1;
		$total = EntityAuditoria::contar($filters);
		$obPagination = new Pagination($total, $paginaAtual, 20);
		$rows = EntityAuditoria::getRegistros($filters, $obPagination->getLimit());

		$modulos = self::mergeKnownAndStored(self::MODULE_LABELS, EntityAuditoria::getValoresDistintos('modulo'));
		$acoes = self::mergeKnownAndStored(self::ACTION_LABELS, EntityAuditoria::getValoresDistintos('acao'));

		$content = View::render('painel/modules/auditoria/index', [
			'itens' => self::getItems($rows),
			'pagination' => parent::getPagination($request, $obPagination),
			'dataInicial' => self::escape($filters['dataInicial']),
			'dataFinal' => self::escape($filters['dataFinal']),
			'busca' => self::escape($filters['busca']),
			'usuarioOptions' => self::getUsuarioOptions($filters['usuario']),
			'moduloOptions' => self::getOptions($modulos, $filters['modulo'], self::MODULE_LABELS),
			'acaoOptions' => self::getOptions($acoes, $filters['acao'], self::ACTION_LABELS),
			'totalRegistros' => self::escape($total),
		]);

		return parent::getPanel('Auditoria > Cursinho', $content, 'auditoria', self::$hidden);
	}

	public static function getDetalhe($request, $id){
		$row = EntityAuditoria::getRegistroPorId($id);
		if(!$row){
			$request->getRouter()->redirect('/auditoria');
		}

		$action = $row['acao'] ?? '';
		$module = $row['modulo'] ?? '';

		$content = View::render('painel/modules/auditoria/detail', [
			'id' => self::escape($row['id'] ?? ''),
			'criadoEm' => self::escape(self::formatDateTime($row['criado_em'] ?? '')),
			'usuario' => self::escape($row['usuario_nome'] ?? 'Sistema'),
			'tipoUsuario' => self::escape($row['usuario_tipo'] ?? ''),
			'modulo' => self::escape(self::MODULE_LABELS[$module] ?? $module),
			'acao' => self::escape(self::ACTION_LABELS[$action] ?? $action),
			'entidade' => self::escape(trim(($row['entidade'] ?? '').' #'.($row['entidade_id'] ?? ''), ' #')),
			'descricao' => self::escape($row['descricao'] ?? ''),
			'ip' => self::escape($row['ip'] ?? ''),
			'userAgent' => self::escape($row['user_agent'] ?? ''),
			'dadosAntes' => self::formatJson($row['dados_antes'] ?? null),
			'dadosDepois' => self::formatJson($row['dados_depois'] ?? null),
		]);

		return parent::getPanel('Detalhe da Auditoria > Cursinho', $content, 'auditoria', self::$hidden);
	}
}
