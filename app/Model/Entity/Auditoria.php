<?php

namespace App\Model\Entity;

use PDO;
use PDOException;

class Auditoria{
	private const TABLE = 'auditoria_logs';

	private static function getConnection(){
		$port = getenv('DB_PORT') ?: 3306;
		$dsn = 'mysql:host='.getenv('DB_HOST').';dbname='.getenv('DB_NAME').';port='.$port.';charset=utf8mb4';

		$connection = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'));
		$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $connection;
	}

	public static function ensureTable(){
		try{
			$sql = 'CREATE TABLE IF NOT EXISTS '.self::TABLE.' (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				usuario_id INT NULL,
				usuario_nome VARCHAR(190) NULL,
				usuario_tipo VARCHAR(60) NULL,
				acao VARCHAR(80) NOT NULL,
				modulo VARCHAR(80) NOT NULL,
				entidade VARCHAR(120) NULL,
				entidade_id VARCHAR(80) NULL,
				descricao VARCHAR(500) NOT NULL,
				dados_antes LONGTEXT NULL,
				dados_depois LONGTEXT NULL,
				ip VARCHAR(80) NULL,
				user_agent VARCHAR(500) NULL,
				criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				INDEX idx_auditoria_criado_em (criado_em),
				INDEX idx_auditoria_usuario_id (usuario_id),
				INDEX idx_auditoria_modulo (modulo),
				INDEX idx_auditoria_acao (acao),
				INDEX idx_auditoria_entidade (entidade(60), entidade_id(40))
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

			self::getConnection()->exec($sql);
			return true;
		}catch(PDOException $e){
			return false;
		}
	}

	public static function registrar($values){
		if(!self::ensureTable()){
			return false;
		}

		try{
			$connection = self::getConnection();
			$sql = 'INSERT INTO '.self::TABLE.' (
				usuario_id, usuario_nome, usuario_tipo, acao, modulo, entidade, entidade_id,
				descricao, dados_antes, dados_depois, ip, user_agent, criado_em
			) VALUES (
				:usuario_id, :usuario_nome, :usuario_tipo, :acao, :modulo, :entidade, :entidade_id,
				:descricao, :dados_antes, :dados_depois, :ip, :user_agent, :criado_em
			)';

			$statement = $connection->prepare($sql);
			$statement->execute([
				':usuario_id' => $values['usuario_id'] ?? null,
				':usuario_nome' => $values['usuario_nome'] ?? null,
				':usuario_tipo' => $values['usuario_tipo'] ?? null,
				':acao' => $values['acao'] ?? '',
				':modulo' => $values['modulo'] ?? '',
				':entidade' => $values['entidade'] ?? null,
				':entidade_id' => $values['entidade_id'] ?? null,
				':descricao' => $values['descricao'] ?? '',
				':dados_antes' => $values['dados_antes'] ?? null,
				':dados_depois' => $values['dados_depois'] ?? null,
				':ip' => $values['ip'] ?? null,
				':user_agent' => $values['user_agent'] ?? null,
				':criado_em' => $values['criado_em'] ?? date('Y-m-d H:i:s'),
			]);

			return true;
		}catch(PDOException $e){
			return false;
		}
	}

	private static function buildWhere($filters, &$params){
		$where = [];

		if(!empty($filters['dataInicial'])){
			$where[] = 'criado_em >= :dataInicial';
			$params[':dataInicial'] = $filters['dataInicial'].' 00:00:00';
		}

		if(!empty($filters['dataFinal'])){
			$where[] = 'criado_em <= :dataFinal';
			$params[':dataFinal'] = $filters['dataFinal'].' 23:59:59';
		}

		if(!empty($filters['usuario'])){
			$where[] = 'usuario_id = :usuario';
			$params[':usuario'] = (int)$filters['usuario'];
		}

		if(!empty($filters['modulo'])){
			$where[] = 'modulo = :modulo';
			$params[':modulo'] = $filters['modulo'];
		}

		if(!empty($filters['acao'])){
			$where[] = 'acao = :acao';
			$params[':acao'] = $filters['acao'];
		}

		if(!empty($filters['busca'])){
			$where[] = '(descricao LIKE :busca OR entidade LIKE :busca OR entidade_id LIKE :busca OR usuario_nome LIKE :busca)';
			$params[':busca'] = '%'.$filters['busca'].'%';
		}

		return count($where) ? 'WHERE '.implode(' AND ', $where) : '';
	}

	public static function contar($filters = []){
		if(!self::ensureTable()){
			return 0;
		}

		try{
			$params = [];
			$where = self::buildWhere($filters, $params);
			$statement = self::getConnection()->prepare('SELECT COUNT(*) FROM '.self::TABLE.' '.$where);
			$statement->execute($params);

			return (int)$statement->fetchColumn();
		}catch(PDOException $e){
			return 0;
		}
	}

	public static function getRegistros($filters = [], $limit = '0,20'){
		if(!self::ensureTable()){
			return [];
		}

		try{
			$params = [];
			$where = self::buildWhere($filters, $params);
			$limit = preg_match('/^\d+\s*,\s*\d+$/', (string)$limit) ? $limit : '0,20';
			$sql = 'SELECT * FROM '.self::TABLE.' '.$where.' ORDER BY criado_em DESC, id DESC LIMIT '.$limit;
			$statement = self::getConnection()->prepare($sql);
			$statement->execute($params);

			return $statement->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			return [];
		}
	}

	public static function getRegistroPorId($id){
		if(!self::ensureTable()){
			return null;
		}

		try{
			$statement = self::getConnection()->prepare('SELECT * FROM '.self::TABLE.' WHERE id = :id LIMIT 1');
			$statement->execute([':id' => (int)$id]);
			$row = $statement->fetch(PDO::FETCH_ASSOC);

			return $row ?: null;
		}catch(PDOException $e){
			return null;
		}
	}

	public static function getValoresDistintos($column){
		$allowed = ['modulo', 'acao'];
		if(!in_array($column, $allowed, true) || !self::ensureTable()){
			return [];
		}

		try{
			$statement = self::getConnection()->query('SELECT DISTINCT '.$column.' FROM '.self::TABLE.' WHERE '.$column.' <> "" ORDER BY '.$column);

			return $statement->fetchAll(PDO::FETCH_COLUMN) ?: [];
		}catch(PDOException $e){
			return [];
		}
	}
}
