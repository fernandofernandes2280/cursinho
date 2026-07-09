<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;

class Configuracao extends Generica {
	const KEY_RELATORIO_TITULO = 'relatorio_titulo';
	const KEY_RELATORIO_LOGO = 'relatorio_logo';
	const KEY_CARTEIRA_ALUNO_LOGO_1 = 'carteira_aluno_logo_1';
	const KEY_CARTEIRA_ALUNO_LOGO_2 = 'carteira_aluno_logo_2';
	const KEY_CARTEIRA_ALUNO_LOGOS_ESCALA = 'carteira_aluno_logos_escala';
	const KEY_CARTEIRA_ALUNO_LOGO_1_ESCALA = 'carteira_aluno_logo_1_escala';
	const KEY_CARTEIRA_ALUNO_LOGO_2_ESCALA = 'carteira_aluno_logo_2_escala';
	const KEY_CARTEIRA_ALUNO_CABECALHO = 'carteira_aluno_cabecalho';
	const KEY_CARTEIRA_ALUNO_CABECALHO_TAMANHO = 'carteira_aluno_cabecalho_tamanho';
	const KEY_CARTEIRA_ALUNO_CABECALHO_COR = 'carteira_aluno_cabecalho_cor';
	const KEY_CARTEIRA_ALUNO_SUBTITULO = 'carteira_aluno_subtitulo';
	const KEY_CARTEIRA_ALUNO_SUBTITULO_TAMANHO = 'carteira_aluno_subtitulo_tamanho';
	const KEY_CARTEIRA_ALUNO_SUBTITULO_COR = 'carteira_aluno_subtitulo_cor';
	const KEY_CARTEIRA_ALUNO_TEXTO_CENTRAL = 'carteira_aluno_texto_central';
	const KEY_CARTEIRA_ALUNO_TEXTO_CENTRAL_TAMANHO = 'carteira_aluno_texto_central_tamanho';
	const KEY_CARTEIRA_ALUNO_TEXTO_CENTRAL_COR = 'carteira_aluno_texto_central_cor';
	const KEY_CARTEIRA_ALUNO_RODAPE = 'carteira_aluno_rodape';
	const KEY_CARTEIRA_ALUNO_RODAPE_TAMANHO = 'carteira_aluno_rodape_tamanho';
	const KEY_CARTEIRA_ALUNO_RODAPE_COR = 'carteira_aluno_rodape_cor';
	const KEY_CARTEIRA_ALUNO_COR_FUNDO = 'carteira_aluno_cor_fundo';
	const KEY_CARTEIRA_ALUNO_USAR_MARCADAGUA = 'carteira_aluno_usar_marcadagua';
	const KEY_CARTEIRA_ALUNO_MARCADAGUA = 'carteira_aluno_marcadagua';
	const KEY_CARTEIRA_ALUNO_MARCADAGUA_OPACIDADE = 'carteira_aluno_marcadagua_opacidade';
	const KEY_CARTEIRA_ALUNO_ASSINATURA = 'carteira_aluno_assinatura';
	const KEY_FREQUENCIA_GERAL_MANHA_INICIO = 'frequencia_geral_manha_inicio';
	const KEY_FREQUENCIA_GERAL_MANHA_FIM = 'frequencia_geral_manha_fim';
	const KEY_FREQUENCIA_GERAL_TARDE_INICIO = 'frequencia_geral_tarde_inicio';
	const KEY_FREQUENCIA_GERAL_TARDE_FIM = 'frequencia_geral_tarde_fim';
	const KEY_FREQUENCIA_GERAL_NOITE_INICIO = 'frequencia_geral_noite_inicio';
	const KEY_FREQUENCIA_GERAL_NOITE_FIM = 'frequencia_geral_noite_fim';
	const KEY_INATIVACAO_FALTAS_INTERCALADAS_MES = 'inativacao_faltas_intercaladas_mes';
	const KEY_INATIVACAO_FALTAS_SEGUIDAS_MES = 'inativacao_faltas_seguidas_mes';
	const KEY_INATIVACAO_ULTIMA_EXECUCAO = 'inativacao_ultima_execucao';
	const DEFAULT_RELATORIO_TITULO = 'CURSO PREPARATÓRIO GRATUITO PREPARA SANTANA';
	const DEFAULT_RELATORIO_LOGO = 'resources/assets/img/preparasantana.png';
	const DEFAULT_CARTEIRA_ALUNO_LOGO_1 = 'resources/assets/img/preparasantana.png';
	const DEFAULT_CARTEIRA_ALUNO_LOGO_2 = 'resources/assets/img/logopms.png';
	const DEFAULT_CARTEIRA_ALUNO_LOGOS_ESCALA = 100;
	const DEFAULT_CARTEIRA_ALUNO_CABECALHO = 'Programa Municipal de Cursos Preparatório';
	const DEFAULT_CARTEIRA_ALUNO_CABECALHO_TAMANHO = 18;
	const DEFAULT_CARTEIRA_ALUNO_CABECALHO_COR = '#E7B538';
	const DEFAULT_CARTEIRA_ALUNO_SUBTITULO = 'Cursinho Preparatório Prof. Anunciação';
	const DEFAULT_CARTEIRA_ALUNO_SUBTITULO_TAMANHO = 20;
	const DEFAULT_CARTEIRA_ALUNO_SUBTITULO_COR = '#3b8c0c';
	const DEFAULT_CARTEIRA_ALUNO_TEXTO_CENTRAL = 'CARTEIRA DIGITAL DO ALUNO';
	const DEFAULT_CARTEIRA_ALUNO_TEXTO_CENTRAL_TAMANHO = 20;
	const DEFAULT_CARTEIRA_ALUNO_TEXTO_CENTRAL_COR = '#3b8c0c';
	const DEFAULT_CARTEIRA_ALUNO_RODAPE = 'A autenticidade do documento pode ser conferida em: www.preparasantana.com/cursinho/aluno';
	const DEFAULT_CARTEIRA_ALUNO_RODAPE_TAMANHO = 14;
	const DEFAULT_CARTEIRA_ALUNO_RODAPE_COR = '#1F2937';
	const DEFAULT_CARTEIRA_ALUNO_COR_FUNDO = '#F0E68C';
	const DEFAULT_CARTEIRA_ALUNO_USAR_MARCADAGUA = '1';
	const DEFAULT_CARTEIRA_ALUNO_MARCADAGUA = 'resources/assets/img/preparasantana_semfundo.png';
	const DEFAULT_CARTEIRA_ALUNO_MARCADAGUA_OPACIDADE = 50;
	const DEFAULT_CARTEIRA_ALUNO_ASSINATURA = 'resources/assets/img/AssinaturaRusso.png';
	const DEFAULT_FREQUENCIA_GERAL_MANHA_INICIO = '06:00';
	const DEFAULT_FREQUENCIA_GERAL_MANHA_FIM = '11:59';
	const DEFAULT_FREQUENCIA_GERAL_TARDE_INICIO = '12:00';
	const DEFAULT_FREQUENCIA_GERAL_TARDE_FIM = '17:59';
	const DEFAULT_FREQUENCIA_GERAL_NOITE_INICIO = '18:00';
	const DEFAULT_FREQUENCIA_GERAL_NOITE_FIM = '23:59';
	const DEFAULT_INATIVACAO_FALTAS_INTERCALADAS_MES = 5;
	const DEFAULT_INATIVACAO_FALTAS_SEGUIDAS_MES = 3;

	public $id;
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

	private static function normalizeColor($color, $default){
		$color = trim((string)$color);

		return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? strtoupper($color) : $default;
	}

	private static function normalizeSize($size, $default, $min = 8, $max = 60){
		$size = (int)$size;

		if($size < $min || $size > $max){
			return (int)$default;
		}

		return $size;
	}

	private static function normalizePercent($value, $default, $min = 0, $max = 100){
		$value = (int)$value;

		if($value < $min || $value > $max){
			return (int)$default;
		}

		return $value;
	}

	private static function normalizePositiveInt($value, $default, $min = 1, $max = 31){
		$value = (int)$value;

		if($value < $min || $value > $max){
			return (int)$default;
		}

		return $value;
	}

	private static function normalizeTime($time, $default){
		$time = trim((string)$time);

		if(preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)){
			return $time;
		}

		return $default;
	}

	private static function timeToMinutes($time){
		[$hora, $minuto] = array_map('intval', explode(':', $time));

		return ($hora * 60) + $minuto;
	}

	private static function isTimeBetween($time, $inicio, $fim){
		$time = self::timeToMinutes($time);
		$inicio = self::timeToMinutes($inicio);
		$fim = self::timeToMinutes($fim);

		if($inicio <= $fim){
			return $time >= $inicio && $time <= $fim;
		}

		return $time >= $inicio || $time <= $fim;
	}

	private static function normalizeTurmaNome($nome){
		$nome = function_exists('mb_strtolower')
			? mb_strtolower((string)$nome, 'UTF-8')
			: strtolower((string)$nome);
		$nome = strtr($nome, [
			'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
			'Á' => 'a', 'À' => 'a', 'Â' => 'a', 'Ã' => 'a', 'Ä' => 'a',
			'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
			'É' => 'e', 'È' => 'e', 'Ê' => 'e', 'Ë' => 'e',
			'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
			'Í' => 'i', 'Ì' => 'i', 'Î' => 'i', 'Ï' => 'i',
			'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
			'Ó' => 'o', 'Ò' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o',
			'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
			'Ú' => 'u', 'Ù' => 'u', 'Û' => 'u', 'Ü' => 'u',
			'ç' => 'c',
			'Ç' => 'c',
		]);

		return $nome;
	}

	private static function getTurmaIdPorPeriodo($periodo){
		$periodo = self::normalizeTurmaNome($periodo);
		$results = Turma::getTurmas(null, 'id ASC');

		while($obTurma = $results->fetchObject(Turma::class)){
			$nome = self::normalizeTurmaNome($obTurma->nome);

			if(strpos($nome, $periodo) !== false){
				return (int)$obTurma->id;
			}
		}

		return 0;
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

	private static function getLogoPath($chave, $default){
		$logo = self::getValor($chave, $default);
		$logo = trim((string)$logo);

		if($logo === ''){
			return $default;
		}

		$rootPath = dirname(__DIR__, 3).'/'.ltrim($logo, '/');

		return is_file($rootPath) ? $logo : $default;
	}

	private static function getLogoUrl($logo){
		$baseUrl = defined('URL') ? URL : '';

		return rtrim($baseUrl, '/').'/'.ltrim($logo, '/');
	}

	public static function getLogoCarteiraAluno1(){
		return self::getLogoPath(self::KEY_CARTEIRA_ALUNO_LOGO_1, self::DEFAULT_CARTEIRA_ALUNO_LOGO_1);
	}

	public static function getLogoCarteiraAluno1Url(){
		return self::getLogoUrl(self::getLogoCarteiraAluno1());
	}

	public static function setLogoCarteiraAluno1($logo){
		$logo = trim((string)$logo);
		return self::setValor(self::KEY_CARTEIRA_ALUNO_LOGO_1, $logo ?: self::DEFAULT_CARTEIRA_ALUNO_LOGO_1);
	}

	public static function getLogoCarteiraAluno2(){
		return self::getLogoPath(self::KEY_CARTEIRA_ALUNO_LOGO_2, self::DEFAULT_CARTEIRA_ALUNO_LOGO_2);
	}

	public static function getLogoCarteiraAluno2Url(){
		return self::getLogoUrl(self::getLogoCarteiraAluno2());
	}

	public static function setLogoCarteiraAluno2($logo){
		$logo = trim((string)$logo);
		return self::setValor(self::KEY_CARTEIRA_ALUNO_LOGO_2, $logo ?: self::DEFAULT_CARTEIRA_ALUNO_LOGO_2);
	}

	public static function getLogosCarteiraAlunoEscala(){
		return self::normalizePercent(
			self::getValor(self::KEY_CARTEIRA_ALUNO_LOGOS_ESCALA, self::DEFAULT_CARTEIRA_ALUNO_LOGOS_ESCALA),
			self::DEFAULT_CARTEIRA_ALUNO_LOGOS_ESCALA,
			50,
			150
		);
	}

	public static function setLogosCarteiraAlunoEscala($escala){
		return self::setValor(
			self::KEY_CARTEIRA_ALUNO_LOGOS_ESCALA,
			self::normalizePercent($escala, self::DEFAULT_CARTEIRA_ALUNO_LOGOS_ESCALA, 50, 150)
		);
	}

	public static function getLogoCarteiraAluno1Escala(){
		$fallback = self::getLogosCarteiraAlunoEscala();

		return self::normalizePercent(
			self::getValor(self::KEY_CARTEIRA_ALUNO_LOGO_1_ESCALA, $fallback),
			$fallback,
			50,
			150
		);
	}

	public static function setLogoCarteiraAluno1Escala($escala){
		return self::setValor(
			self::KEY_CARTEIRA_ALUNO_LOGO_1_ESCALA,
			self::normalizePercent($escala, self::getLogosCarteiraAlunoEscala(), 50, 150)
		);
	}

	public static function getLogoCarteiraAluno2Escala(){
		$fallback = self::getLogosCarteiraAlunoEscala();

		return self::normalizePercent(
			self::getValor(self::KEY_CARTEIRA_ALUNO_LOGO_2_ESCALA, $fallback),
			$fallback,
			50,
			150
		);
	}

	public static function setLogoCarteiraAluno2Escala($escala){
		return self::setValor(
			self::KEY_CARTEIRA_ALUNO_LOGO_2_ESCALA,
			self::normalizePercent($escala, self::getLogosCarteiraAlunoEscala(), 50, 150)
		);
	}

	public static function getCabecalhoCarteiraAluno(){
		$cabecalho = self::getValor(self::KEY_CARTEIRA_ALUNO_CABECALHO, self::DEFAULT_CARTEIRA_ALUNO_CABECALHO);

		return $cabecalho === 'Programa Municipal de Cursos Preparatório*Cursinho Preparatório Prof. Anunciação'
			? self::DEFAULT_CARTEIRA_ALUNO_CABECALHO
			: $cabecalho;
	}

	public static function getCabecalhoCarteiraAlunoLinhas(){
		$linhas = array_filter(array_map('trim', explode('*', self::getCabecalhoCarteiraAluno())));
		$linhas = array_values($linhas);

		return count($linhas) ? $linhas : [self::DEFAULT_CARTEIRA_ALUNO_CABECALHO];
	}

	public static function setCabecalhoCarteiraAluno($cabecalho){
		$cabecalho = trim((string)$cabecalho);
		return self::setValor(self::KEY_CARTEIRA_ALUNO_CABECALHO, $cabecalho ?: self::DEFAULT_CARTEIRA_ALUNO_CABECALHO);
	}

	public static function getCabecalhoCarteiraAlunoTamanho(){
		return self::normalizeSize(
			self::getValor(self::KEY_CARTEIRA_ALUNO_CABECALHO_TAMANHO, self::DEFAULT_CARTEIRA_ALUNO_CABECALHO_TAMANHO),
			self::DEFAULT_CARTEIRA_ALUNO_CABECALHO_TAMANHO
		);
	}

	public static function setCabecalhoCarteiraAlunoTamanho($tamanho){
		return self::setValor(
			self::KEY_CARTEIRA_ALUNO_CABECALHO_TAMANHO,
			self::normalizeSize($tamanho, self::DEFAULT_CARTEIRA_ALUNO_CABECALHO_TAMANHO)
		);
	}

	public static function getCabecalhoCarteiraAlunoCor(){
		return self::normalizeColor(
			self::getValor(self::KEY_CARTEIRA_ALUNO_CABECALHO_COR, self::DEFAULT_CARTEIRA_ALUNO_CABECALHO_COR),
			self::DEFAULT_CARTEIRA_ALUNO_CABECALHO_COR
		);
	}

	public static function setCabecalhoCarteiraAlunoCor($cor){
		return self::setValor(self::KEY_CARTEIRA_ALUNO_CABECALHO_COR, self::normalizeColor($cor, self::DEFAULT_CARTEIRA_ALUNO_CABECALHO_COR));
	}

	public static function getSubtituloCarteiraAluno(){
		$subtitulo = self::getValor(self::KEY_CARTEIRA_ALUNO_SUBTITULO, self::DEFAULT_CARTEIRA_ALUNO_SUBTITULO);

		return strtoupper($subtitulo) === 'CARTEIRA DIGITAL DO ALUNO'
			? self::DEFAULT_CARTEIRA_ALUNO_SUBTITULO
			: $subtitulo;
	}

	public static function setSubtituloCarteiraAluno($subtitulo){
		$subtitulo = trim((string)$subtitulo);
		return self::setValor(self::KEY_CARTEIRA_ALUNO_SUBTITULO, $subtitulo ?: self::DEFAULT_CARTEIRA_ALUNO_SUBTITULO);
	}

	public static function getSubtituloCarteiraAlunoTamanho(){
		return self::normalizeSize(
			self::getValor(self::KEY_CARTEIRA_ALUNO_SUBTITULO_TAMANHO, self::DEFAULT_CARTEIRA_ALUNO_SUBTITULO_TAMANHO),
			self::DEFAULT_CARTEIRA_ALUNO_SUBTITULO_TAMANHO
		);
	}

	public static function setSubtituloCarteiraAlunoTamanho($tamanho){
		return self::setValor(
			self::KEY_CARTEIRA_ALUNO_SUBTITULO_TAMANHO,
			self::normalizeSize($tamanho, self::DEFAULT_CARTEIRA_ALUNO_SUBTITULO_TAMANHO)
		);
	}

	public static function getSubtituloCarteiraAlunoCor(){
		return self::normalizeColor(
			self::getValor(self::KEY_CARTEIRA_ALUNO_SUBTITULO_COR, self::DEFAULT_CARTEIRA_ALUNO_SUBTITULO_COR),
			self::DEFAULT_CARTEIRA_ALUNO_SUBTITULO_COR
		);
	}

	public static function setSubtituloCarteiraAlunoCor($cor){
		return self::setValor(self::KEY_CARTEIRA_ALUNO_SUBTITULO_COR, self::normalizeColor($cor, self::DEFAULT_CARTEIRA_ALUNO_SUBTITULO_COR));
	}

	public static function getTextoCentralCarteiraAluno(){
		return self::getValor(self::KEY_CARTEIRA_ALUNO_TEXTO_CENTRAL, self::DEFAULT_CARTEIRA_ALUNO_TEXTO_CENTRAL);
	}

	public static function setTextoCentralCarteiraAluno($texto){
		$texto = trim((string)$texto);
		return self::setValor(self::KEY_CARTEIRA_ALUNO_TEXTO_CENTRAL, $texto ?: self::DEFAULT_CARTEIRA_ALUNO_TEXTO_CENTRAL);
	}

	public static function getTextoCentralCarteiraAlunoTamanho(){
		return self::normalizeSize(
			self::getValor(self::KEY_CARTEIRA_ALUNO_TEXTO_CENTRAL_TAMANHO, self::DEFAULT_CARTEIRA_ALUNO_TEXTO_CENTRAL_TAMANHO),
			self::DEFAULT_CARTEIRA_ALUNO_TEXTO_CENTRAL_TAMANHO
		);
	}

	public static function setTextoCentralCarteiraAlunoTamanho($tamanho){
		return self::setValor(
			self::KEY_CARTEIRA_ALUNO_TEXTO_CENTRAL_TAMANHO,
			self::normalizeSize($tamanho, self::DEFAULT_CARTEIRA_ALUNO_TEXTO_CENTRAL_TAMANHO)
		);
	}

	public static function getTextoCentralCarteiraAlunoCor(){
		return self::normalizeColor(
			self::getValor(self::KEY_CARTEIRA_ALUNO_TEXTO_CENTRAL_COR, self::DEFAULT_CARTEIRA_ALUNO_TEXTO_CENTRAL_COR),
			self::DEFAULT_CARTEIRA_ALUNO_TEXTO_CENTRAL_COR
		);
	}

	public static function setTextoCentralCarteiraAlunoCor($cor){
		return self::setValor(self::KEY_CARTEIRA_ALUNO_TEXTO_CENTRAL_COR, self::normalizeColor($cor, self::DEFAULT_CARTEIRA_ALUNO_TEXTO_CENTRAL_COR));
	}

	public static function getRodapeCarteiraAluno(){
		return self::getValor(self::KEY_CARTEIRA_ALUNO_RODAPE, self::DEFAULT_CARTEIRA_ALUNO_RODAPE);
	}

	public static function setRodapeCarteiraAluno($rodape){
		$rodape = trim((string)$rodape);
		return self::setValor(self::KEY_CARTEIRA_ALUNO_RODAPE, $rodape ?: self::DEFAULT_CARTEIRA_ALUNO_RODAPE);
	}

	public static function getRodapeCarteiraAlunoTamanho(){
		return self::normalizeSize(
			self::getValor(self::KEY_CARTEIRA_ALUNO_RODAPE_TAMANHO, self::DEFAULT_CARTEIRA_ALUNO_RODAPE_TAMANHO),
			self::DEFAULT_CARTEIRA_ALUNO_RODAPE_TAMANHO
		);
	}

	public static function setRodapeCarteiraAlunoTamanho($tamanho){
		return self::setValor(
			self::KEY_CARTEIRA_ALUNO_RODAPE_TAMANHO,
			self::normalizeSize($tamanho, self::DEFAULT_CARTEIRA_ALUNO_RODAPE_TAMANHO)
		);
	}

	public static function getRodapeCarteiraAlunoCor(){
		return self::normalizeColor(
			self::getValor(self::KEY_CARTEIRA_ALUNO_RODAPE_COR, self::DEFAULT_CARTEIRA_ALUNO_RODAPE_COR),
			self::DEFAULT_CARTEIRA_ALUNO_RODAPE_COR
		);
	}

	public static function setRodapeCarteiraAlunoCor($cor){
		return self::setValor(self::KEY_CARTEIRA_ALUNO_RODAPE_COR, self::normalizeColor($cor, self::DEFAULT_CARTEIRA_ALUNO_RODAPE_COR));
	}

	public static function getCorFundoCarteiraAluno(){
		return self::normalizeColor(
			self::getValor(self::KEY_CARTEIRA_ALUNO_COR_FUNDO, self::DEFAULT_CARTEIRA_ALUNO_COR_FUNDO),
			self::DEFAULT_CARTEIRA_ALUNO_COR_FUNDO
		);
	}

	public static function setCorFundoCarteiraAluno($cor){
		return self::setValor(self::KEY_CARTEIRA_ALUNO_COR_FUNDO, self::normalizeColor($cor, self::DEFAULT_CARTEIRA_ALUNO_COR_FUNDO));
	}

	public static function getUsarMarcaDaguaCarteiraAluno(){
		return self::getValor(self::KEY_CARTEIRA_ALUNO_USAR_MARCADAGUA, self::DEFAULT_CARTEIRA_ALUNO_USAR_MARCADAGUA) === '1';
	}

	public static function setUsarMarcaDaguaCarteiraAluno($usarMarcaDagua){
		return self::setValor(self::KEY_CARTEIRA_ALUNO_USAR_MARCADAGUA, $usarMarcaDagua ? '1' : '0');
	}

	public static function getMarcaDaguaCarteiraAluno(){
		return self::getLogoPath(self::KEY_CARTEIRA_ALUNO_MARCADAGUA, self::DEFAULT_CARTEIRA_ALUNO_MARCADAGUA);
	}

	public static function getMarcaDaguaCarteiraAlunoUrl(){
		return self::getLogoUrl(self::getMarcaDaguaCarteiraAluno());
	}

	public static function setMarcaDaguaCarteiraAluno($marcaDagua){
		$marcaDagua = trim((string)$marcaDagua);
		return self::setValor(self::KEY_CARTEIRA_ALUNO_MARCADAGUA, $marcaDagua ?: self::DEFAULT_CARTEIRA_ALUNO_MARCADAGUA);
	}

	public static function getMarcaDaguaCarteiraAlunoOpacidade(){
		return self::normalizePercent(
			self::getValor(self::KEY_CARTEIRA_ALUNO_MARCADAGUA_OPACIDADE, self::DEFAULT_CARTEIRA_ALUNO_MARCADAGUA_OPACIDADE),
			self::DEFAULT_CARTEIRA_ALUNO_MARCADAGUA_OPACIDADE
		);
	}

	public static function getMarcaDaguaCarteiraAlunoOpacidadeCss(){
		return number_format(self::getMarcaDaguaCarteiraAlunoOpacidade() / 100, 2, '.', '');
	}

	public static function setMarcaDaguaCarteiraAlunoOpacidade($opacidade){
		return self::setValor(
			self::KEY_CARTEIRA_ALUNO_MARCADAGUA_OPACIDADE,
			self::normalizePercent($opacidade, self::DEFAULT_CARTEIRA_ALUNO_MARCADAGUA_OPACIDADE)
		);
	}

	public static function getAssinaturaCarteiraAluno(){
		return self::getLogoPath(self::KEY_CARTEIRA_ALUNO_ASSINATURA, self::DEFAULT_CARTEIRA_ALUNO_ASSINATURA);
	}

	public static function getAssinaturaCarteiraAlunoUrl(){
		return self::getLogoUrl(self::getAssinaturaCarteiraAluno());
	}

	public static function setAssinaturaCarteiraAluno($assinatura){
		$assinatura = trim((string)$assinatura);
		return self::setValor(self::KEY_CARTEIRA_ALUNO_ASSINATURA, $assinatura ?: self::DEFAULT_CARTEIRA_ALUNO_ASSINATURA);
	}

	public static function getFrequenciaGeralManhaInicio(){
		return self::normalizeTime(
			self::getValor(self::KEY_FREQUENCIA_GERAL_MANHA_INICIO, self::DEFAULT_FREQUENCIA_GERAL_MANHA_INICIO),
			self::DEFAULT_FREQUENCIA_GERAL_MANHA_INICIO
		);
	}

	public static function setFrequenciaGeralManhaInicio($time){
		return self::setValor(self::KEY_FREQUENCIA_GERAL_MANHA_INICIO, self::normalizeTime($time, self::DEFAULT_FREQUENCIA_GERAL_MANHA_INICIO));
	}

	public static function getFrequenciaGeralManhaFim(){
		return self::normalizeTime(
			self::getValor(self::KEY_FREQUENCIA_GERAL_MANHA_FIM, self::DEFAULT_FREQUENCIA_GERAL_MANHA_FIM),
			self::DEFAULT_FREQUENCIA_GERAL_MANHA_FIM
		);
	}

	public static function setFrequenciaGeralManhaFim($time){
		return self::setValor(self::KEY_FREQUENCIA_GERAL_MANHA_FIM, self::normalizeTime($time, self::DEFAULT_FREQUENCIA_GERAL_MANHA_FIM));
	}

	public static function getFrequenciaGeralTardeInicio(){
		return self::normalizeTime(
			self::getValor(self::KEY_FREQUENCIA_GERAL_TARDE_INICIO, self::DEFAULT_FREQUENCIA_GERAL_TARDE_INICIO),
			self::DEFAULT_FREQUENCIA_GERAL_TARDE_INICIO
		);
	}

	public static function setFrequenciaGeralTardeInicio($time){
		return self::setValor(self::KEY_FREQUENCIA_GERAL_TARDE_INICIO, self::normalizeTime($time, self::DEFAULT_FREQUENCIA_GERAL_TARDE_INICIO));
	}

	public static function getFrequenciaGeralTardeFim(){
		return self::normalizeTime(
			self::getValor(self::KEY_FREQUENCIA_GERAL_TARDE_FIM, self::DEFAULT_FREQUENCIA_GERAL_TARDE_FIM),
			self::DEFAULT_FREQUENCIA_GERAL_TARDE_FIM
		);
	}

	public static function setFrequenciaGeralTardeFim($time){
		return self::setValor(self::KEY_FREQUENCIA_GERAL_TARDE_FIM, self::normalizeTime($time, self::DEFAULT_FREQUENCIA_GERAL_TARDE_FIM));
	}

	public static function getFrequenciaGeralNoiteInicio(){
		return self::normalizeTime(
			self::getValor(self::KEY_FREQUENCIA_GERAL_NOITE_INICIO, self::DEFAULT_FREQUENCIA_GERAL_NOITE_INICIO),
			self::DEFAULT_FREQUENCIA_GERAL_NOITE_INICIO
		);
	}

	public static function setFrequenciaGeralNoiteInicio($time){
		return self::setValor(self::KEY_FREQUENCIA_GERAL_NOITE_INICIO, self::normalizeTime($time, self::DEFAULT_FREQUENCIA_GERAL_NOITE_INICIO));
	}

	public static function getFrequenciaGeralNoiteFim(){
		return self::normalizeTime(
			self::getValor(self::KEY_FREQUENCIA_GERAL_NOITE_FIM, self::DEFAULT_FREQUENCIA_GERAL_NOITE_FIM),
			self::DEFAULT_FREQUENCIA_GERAL_NOITE_FIM
		);
	}

	public static function setFrequenciaGeralNoiteFim($time){
		return self::setValor(self::KEY_FREQUENCIA_GERAL_NOITE_FIM, self::normalizeTime($time, self::DEFAULT_FREQUENCIA_GERAL_NOITE_FIM));
	}

	public static function getInativacaoFaltasIntercaladasMes(){
		return self::normalizePositiveInt(
			self::getValor(self::KEY_INATIVACAO_FALTAS_INTERCALADAS_MES, self::DEFAULT_INATIVACAO_FALTAS_INTERCALADAS_MES),
			self::DEFAULT_INATIVACAO_FALTAS_INTERCALADAS_MES
		);
	}

	public static function setInativacaoFaltasIntercaladasMes($quantidade){
		$updated = self::setValor(
			self::KEY_INATIVACAO_FALTAS_INTERCALADAS_MES,
			self::normalizePositiveInt($quantidade, self::DEFAULT_INATIVACAO_FALTAS_INTERCALADAS_MES)
		);

		self::setInativacaoUltimaExecucao('');
		return $updated;
	}

	public static function getInativacaoFaltasSeguidasMes(){
		return self::normalizePositiveInt(
			self::getValor(self::KEY_INATIVACAO_FALTAS_SEGUIDAS_MES, self::DEFAULT_INATIVACAO_FALTAS_SEGUIDAS_MES),
			self::DEFAULT_INATIVACAO_FALTAS_SEGUIDAS_MES
		);
	}

	public static function setInativacaoFaltasSeguidasMes($quantidade){
		$updated = self::setValor(
			self::KEY_INATIVACAO_FALTAS_SEGUIDAS_MES,
			self::normalizePositiveInt($quantidade, self::DEFAULT_INATIVACAO_FALTAS_SEGUIDAS_MES)
		);

		self::setInativacaoUltimaExecucao('');
		return $updated;
	}

	public static function getInativacaoUltimaExecucao(){
		$data = trim((string)self::getValor(self::KEY_INATIVACAO_ULTIMA_EXECUCAO, ''));

		return preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) ? $data : '';
	}

	public static function setInativacaoUltimaExecucao($data){
		$data = trim((string)$data);
		$data = preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) ? $data : '';

		return self::setValor(self::KEY_INATIVACAO_ULTIMA_EXECUCAO, $data);
	}

	public static function getPeriodoFrequenciaGeralAtual($time = null){
		$time = self::normalizeTime($time ?? date('H:i'), date('H:i'));
		$periodos = [
			'manha' => [self::getFrequenciaGeralManhaInicio(), self::getFrequenciaGeralManhaFim()],
			'tarde' => [self::getFrequenciaGeralTardeInicio(), self::getFrequenciaGeralTardeFim()],
			'noite' => [self::getFrequenciaGeralNoiteInicio(), self::getFrequenciaGeralNoiteFim()],
		];

		foreach($periodos as $periodo => [$inicio, $fim]){
			if(self::isTimeBetween($time, $inicio, $fim)){
				return $periodo;
			}
		}

		return '';
	}

	public static function getTurmaFrequenciaGeralAtual($fallbackTurma = 0, $time = null){
		$periodo = self::getPeriodoFrequenciaGeralAtual($time);
		$idTurma = $periodo !== '' ? self::getTurmaIdPorPeriodo($periodo) : 0;

		return $idTurma > 0 ? $idTurma : (int)$fallbackTurma;
	}
}
