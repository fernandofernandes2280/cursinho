<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;

class Configuracao extends Generica {
	const KEY_RELATORIO_TITULO = 'relatorio_titulo';
	const KEY_RELATORIO_LOGO = 'relatorio_logo';
	const DEFAULT_RELATORIO_TITULO = 'CURSO PREPARATÓRIO GRATUITO PREPARA SANTANA';
	const DEFAULT_RELATORIO_LOGO = 'resources/assets/img/preparasantana.png';

	public $chave;
	public $valor;
	public $created_at;
	public $updated_at;

	private static $tableChecked = false;

	private static function ensureTable(){
		if(self::$tableChecked){
			return true;
		}

		(new Database())->execute(
			'CREATE TABLE IF NOT EXISTS configuracoes (
				id INT AUTO_INCREMENT PRIMARY KEY,
				chave VARCHAR(100) NOT NULL UNIQUE,
				valor TEXT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
		);

		self::$tableChecked = true;
		return true;
	}

	public static function getConfiguracoes($where = null, $order = null, $limit = null, $fields = '*') {
		self::ensureTable();
		return (new Database('configuracoes'))->select($where, $order, $limit, $fields);
	}

	public static function getByChave($chave){
		self::ensureTable();

		return (new Database())
			->execute('SELECT * FROM configuracoes WHERE chave = ? LIMIT 1', [$chave])
			->fetchObject(self::class);
	}

	public static function getValor($chave, $default = ''){
		$obConfiguracao = self::getByChave($chave);

		if(!$obConfiguracao instanceof self){
			return $default;
		}

		$valor = trim((string)$obConfiguracao->valor);
		return $valor !== '' ? $valor : $default;
	}

	public static function setValor($chave, $valor){
		self::ensureTable();

		$valor = trim((string)$valor);
		$obConfiguracao = self::getByChave($chave);

		if($obConfiguracao instanceof self){
			return (new Database('configuracoes'))->update('id = '.$obConfiguracao->id, [
				'valor' => $valor,
			]);
		}

		(new Database('configuracoes'))->insert([
			'chave' => $chave,
			'valor' => $valor,
		]);

		return true;
	}

	public static function getTituloRelatorio(){
		return self::getValor(self::KEY_RELATORIO_TITULO, self::DEFAULT_RELATORIO_TITULO);
	}

	public static function getTituloRelatorioLinhas(){
		$linhas = array_filter(array_map('trim', explode('*', self::getTituloRelatorio())));
		$linhas = array_values($linhas);

		return count($linhas) ? $linhas : [self::DEFAULT_RELATORIO_TITULO];
	}

	public static function setTituloRelatorio($titulo){
		$titulo = trim((string)$titulo);
		return self::setValor(self::KEY_RELATORIO_TITULO, $titulo ?: self::DEFAULT_RELATORIO_TITULO);
	}

	public static function getLogoRelatorio(){
		$logo = self::getValor(self::KEY_RELATORIO_LOGO, self::DEFAULT_RELATORIO_LOGO);
		$logo = trim((string)$logo);

		if($logo === ''){
			return self::DEFAULT_RELATORIO_LOGO;
		}

		$rootPath = dirname(__DIR__, 3).'/'.ltrim($logo, '/');

		return is_file($rootPath) ? $logo : self::DEFAULT_RELATORIO_LOGO;
	}

	public static function getLogoRelatorioUrl(){
		$logo = self::getLogoRelatorio();
		$baseUrl = defined('URL') ? URL : '';

		return rtrim($baseUrl, '/').'/'.ltrim($logo, '/');
	}

	public static function setLogoRelatorio($logo){
		$logo = trim((string)$logo);
		return self::setValor(self::KEY_RELATORIO_LOGO, $logo ?: self::DEFAULT_RELATORIO_LOGO);
	}
}
