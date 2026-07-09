<?php

namespace App\Controller\Painel;

use \App\Utils\View;
use \App\Utils\Funcoes;
use \App\Model\Entity\Configuracao as EntityConfiguracao;

class Configuracao extends Page{
	private const LOGO_UPLOAD_RELATIVE_DIR = 'app/Controller/File/files/logos';
	private const LOGO_ALLOWED_MIMES = [
		'image/png' => 'png',
		'image/jpeg' => 'jpg',
		'image/webp' => 'webp',
	];
	private const PNG_ALLOWED_MIMES = [
		'image/png' => 'png',
	];

	private static function getLogoVersion($logo){
		$logoPath = dirname(__DIR__, 3).'/'.ltrim($logo, '/');

		return is_file($logoPath) ? filemtime($logoPath) : time();
	}

	private static function uploadLogo($request, $field, $prefix, $callback, $allowedMimes = null){
		$allowedMimes = $allowedMimes ?? self::LOGO_ALLOWED_MIMES;
		$fileVars = $request->getFileVars();
		$file = $fileVars[$field] ?? null;

		if(!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE){
			return true;
		}

		if(($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')){
			return false;
		}

		$mime = '';
		if(function_exists('finfo_open')){
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
			if($finfo){
				finfo_close($finfo);
			}
		}

		if(!$mime && function_exists('mime_content_type')){
			$mime = mime_content_type($file['tmp_name']);
		}

		if(!isset($allowedMimes[$mime])){
			return false;
		}

		$uploadDir = dirname(__DIR__).'/File/files/logos';

		if(!is_dir($uploadDir)){
			mkdir($uploadDir, 0775, true);
		}

		$extension = $allowedMimes[$mime];
		$fileName = $prefix.'-'.date('YmdHis').'-'.bin2hex(random_bytes(4)).'.'.$extension;
		$destination = $uploadDir.'/'.$fileName;

		if(!move_uploaded_file($file['tmp_name'], $destination)){
			return false;
		}

		call_user_func($callback, self::LOGO_UPLOAD_RELATIVE_DIR.'/'.$fileName);

		return true;
	}

	private static function uploadLogoRelatorio($request){
		return self::uploadLogo($request, 'logoRelatorio', 'relatorio-logo', [EntityConfiguracao::class, 'setLogoRelatorio']);
	}

	private static function uploadLogoCarteiraAluno1($request){
		return self::uploadLogo($request, 'logoCarteiraAluno1', 'carteira-aluno-logo-1', [EntityConfiguracao::class, 'setLogoCarteiraAluno1']);
	}

	private static function uploadLogoCarteiraAluno2($request){
		return self::uploadLogo($request, 'logoCarteiraAluno2', 'carteira-aluno-logo-2', [EntityConfiguracao::class, 'setLogoCarteiraAluno2']);
	}

	private static function uploadMarcaDaguaCarteiraAluno($request){
		return self::uploadLogo($request, 'marcaDaguaCarteiraAluno', 'carteira-aluno-marcadagua', [EntityConfiguracao::class, 'setMarcaDaguaCarteiraAluno']);
	}

	private static function uploadAssinaturaCarteiraAluno($request){
		return self::uploadLogo($request, 'assinaturaCarteiraAluno', 'carteira-aluno-assinatura', [EntityConfiguracao::class, 'setAssinaturaCarteiraAluno'], self::PNG_ALLOWED_MIMES);
	}

	public static function getConfiguracoes($request){
		$logoRelatorio = EntityConfiguracao::getLogoRelatorioUrl();
		$logoCarteiraAluno1 = EntityConfiguracao::getLogoCarteiraAluno1Url();
		$logoCarteiraAluno2 = EntityConfiguracao::getLogoCarteiraAluno2Url();
		$marcaDaguaCarteiraAluno = EntityConfiguracao::getMarcaDaguaCarteiraAlunoUrl();
		$assinaturaCarteiraAluno = EntityConfiguracao::getAssinaturaCarteiraAlunoUrl();

		$content = View::render('painel/modules/configuracoes/form',[
			'title' => 'Configurações',
			'statusMessage' => Funcoes::getStatus($request),
			'tituloRelatorio' => htmlspecialchars(EntityConfiguracao::getTituloRelatorio(), ENT_QUOTES, 'UTF-8'),
			'logoRelatorio' => htmlspecialchars($logoRelatorio, ENT_QUOTES, 'UTF-8'),
			'logoVersion' => self::getLogoVersion(EntityConfiguracao::getLogoRelatorio()),
			'logoCarteiraAluno1' => htmlspecialchars($logoCarteiraAluno1, ENT_QUOTES, 'UTF-8'),
			'logoCarteiraAluno1Version' => self::getLogoVersion(EntityConfiguracao::getLogoCarteiraAluno1()),
			'logoCarteiraAluno2' => htmlspecialchars($logoCarteiraAluno2, ENT_QUOTES, 'UTF-8'),
			'logoCarteiraAluno2Version' => self::getLogoVersion(EntityConfiguracao::getLogoCarteiraAluno2()),
			'logoCarteiraAluno1Escala' => EntityConfiguracao::getLogoCarteiraAluno1Escala(),
			'logoCarteiraAluno2Escala' => EntityConfiguracao::getLogoCarteiraAluno2Escala(),
			'cabecalhoCarteiraAluno' => htmlspecialchars(EntityConfiguracao::getCabecalhoCarteiraAluno(), ENT_QUOTES, 'UTF-8'),
			'cabecalhoCarteiraAlunoTamanho' => EntityConfiguracao::getCabecalhoCarteiraAlunoTamanho(),
			'cabecalhoCarteiraAlunoCor' => htmlspecialchars(EntityConfiguracao::getCabecalhoCarteiraAlunoCor(), ENT_QUOTES, 'UTF-8'),
			'subtituloCarteiraAluno' => htmlspecialchars(EntityConfiguracao::getSubtituloCarteiraAluno(), ENT_QUOTES, 'UTF-8'),
			'subtituloCarteiraAlunoTamanho' => EntityConfiguracao::getSubtituloCarteiraAlunoTamanho(),
			'subtituloCarteiraAlunoCor' => htmlspecialchars(EntityConfiguracao::getSubtituloCarteiraAlunoCor(), ENT_QUOTES, 'UTF-8'),
			'textoCentralCarteiraAluno' => htmlspecialchars(EntityConfiguracao::getTextoCentralCarteiraAluno(), ENT_QUOTES, 'UTF-8'),
			'textoCentralCarteiraAlunoTamanho' => EntityConfiguracao::getTextoCentralCarteiraAlunoTamanho(),
			'textoCentralCarteiraAlunoCor' => htmlspecialchars(EntityConfiguracao::getTextoCentralCarteiraAlunoCor(), ENT_QUOTES, 'UTF-8'),
			'rodapeCarteiraAluno' => htmlspecialchars(EntityConfiguracao::getRodapeCarteiraAluno(), ENT_QUOTES, 'UTF-8'),
			'rodapeCarteiraAlunoTamanho' => EntityConfiguracao::getRodapeCarteiraAlunoTamanho(),
			'rodapeCarteiraAlunoCor' => htmlspecialchars(EntityConfiguracao::getRodapeCarteiraAlunoCor(), ENT_QUOTES, 'UTF-8'),
			'corFundoCarteiraAluno' => htmlspecialchars(EntityConfiguracao::getCorFundoCarteiraAluno(), ENT_QUOTES, 'UTF-8'),
			'usarMarcaDaguaCarteiraAlunoChecked' => EntityConfiguracao::getUsarMarcaDaguaCarteiraAluno() ? 'checked' : '',
			'marcaDaguaCarteiraAluno' => htmlspecialchars($marcaDaguaCarteiraAluno, ENT_QUOTES, 'UTF-8'),
			'marcaDaguaCarteiraAlunoVersion' => self::getLogoVersion(EntityConfiguracao::getMarcaDaguaCarteiraAluno()),
			'marcaDaguaCarteiraAlunoOpacidade' => EntityConfiguracao::getMarcaDaguaCarteiraAlunoOpacidade(),
			'assinaturaCarteiraAluno' => htmlspecialchars($assinaturaCarteiraAluno, ENT_QUOTES, 'UTF-8'),
			'assinaturaCarteiraAlunoVersion' => self::getLogoVersion(EntityConfiguracao::getAssinaturaCarteiraAluno()),
			'frequenciaGeralManhaInicio' => EntityConfiguracao::getFrequenciaGeralManhaInicio(),
			'frequenciaGeralManhaFim' => EntityConfiguracao::getFrequenciaGeralManhaFim(),
			'frequenciaGeralTardeInicio' => EntityConfiguracao::getFrequenciaGeralTardeInicio(),
			'frequenciaGeralTardeFim' => EntityConfiguracao::getFrequenciaGeralTardeFim(),
			'frequenciaGeralNoiteInicio' => EntityConfiguracao::getFrequenciaGeralNoiteInicio(),
			'frequenciaGeralNoiteFim' => EntityConfiguracao::getFrequenciaGeralNoiteFim(),
			'inativacaoFaltasIntercaladasMes' => EntityConfiguracao::getInativacaoFaltasIntercaladasMes(),
			'inativacaoFaltasSeguidasMes' => EntityConfiguracao::getInativacaoFaltasSeguidasMes(),
		]);

		return parent::getPanel('Configurações > Cursinho', $content, 'configuracoes');
	}

	public static function setConfiguracoes($request){
		$postVars = $request->getPostVars();
		$tituloRelatorio = $postVars['tituloRelatorio'] ?? '';

		EntityConfiguracao::setTituloRelatorio($tituloRelatorio);
		EntityConfiguracao::setLogoCarteiraAluno1Escala($postVars['logoCarteiraAluno1Escala'] ?? '');
		EntityConfiguracao::setLogoCarteiraAluno2Escala($postVars['logoCarteiraAluno2Escala'] ?? '');
		EntityConfiguracao::setCabecalhoCarteiraAluno($postVars['cabecalhoCarteiraAluno'] ?? '');
		EntityConfiguracao::setCabecalhoCarteiraAlunoTamanho($postVars['cabecalhoCarteiraAlunoTamanho'] ?? '');
		EntityConfiguracao::setCabecalhoCarteiraAlunoCor($postVars['cabecalhoCarteiraAlunoCor'] ?? '');
		EntityConfiguracao::setSubtituloCarteiraAluno($postVars['subtituloCarteiraAluno'] ?? '');
		EntityConfiguracao::setSubtituloCarteiraAlunoTamanho($postVars['subtituloCarteiraAlunoTamanho'] ?? '');
		EntityConfiguracao::setSubtituloCarteiraAlunoCor($postVars['subtituloCarteiraAlunoCor'] ?? '');
		EntityConfiguracao::setTextoCentralCarteiraAluno($postVars['textoCentralCarteiraAluno'] ?? '');
		EntityConfiguracao::setTextoCentralCarteiraAlunoTamanho($postVars['textoCentralCarteiraAlunoTamanho'] ?? '');
		EntityConfiguracao::setTextoCentralCarteiraAlunoCor($postVars['textoCentralCarteiraAlunoCor'] ?? '');
		EntityConfiguracao::setRodapeCarteiraAluno($postVars['rodapeCarteiraAluno'] ?? '');
		EntityConfiguracao::setRodapeCarteiraAlunoTamanho($postVars['rodapeCarteiraAlunoTamanho'] ?? '');
		EntityConfiguracao::setRodapeCarteiraAlunoCor($postVars['rodapeCarteiraAlunoCor'] ?? '');
		EntityConfiguracao::setCorFundoCarteiraAluno($postVars['corFundoCarteiraAluno'] ?? '');
		EntityConfiguracao::setUsarMarcaDaguaCarteiraAluno(isset($postVars['usarMarcaDaguaCarteiraAluno']));
		EntityConfiguracao::setMarcaDaguaCarteiraAlunoOpacidade($postVars['marcaDaguaCarteiraAlunoOpacidade'] ?? '');
		EntityConfiguracao::setFrequenciaGeralManhaInicio($postVars['frequenciaGeralManhaInicio'] ?? '');
		EntityConfiguracao::setFrequenciaGeralManhaFim($postVars['frequenciaGeralManhaFim'] ?? '');
		EntityConfiguracao::setFrequenciaGeralTardeInicio($postVars['frequenciaGeralTardeInicio'] ?? '');
		EntityConfiguracao::setFrequenciaGeralTardeFim($postVars['frequenciaGeralTardeFim'] ?? '');
		EntityConfiguracao::setFrequenciaGeralNoiteInicio($postVars['frequenciaGeralNoiteInicio'] ?? '');
		EntityConfiguracao::setFrequenciaGeralNoiteFim($postVars['frequenciaGeralNoiteFim'] ?? '');
		EntityConfiguracao::setInativacaoFaltasIntercaladasMes($postVars['inativacaoFaltasIntercaladasMes'] ?? '');
		EntityConfiguracao::setInativacaoFaltasSeguidasMes($postVars['inativacaoFaltasSeguidasMes'] ?? '');

		$status = 'updated';

		if(
			!self::uploadLogoRelatorio($request) ||
			!self::uploadLogoCarteiraAluno1($request) ||
			!self::uploadLogoCarteiraAluno2($request) ||
			!self::uploadMarcaDaguaCarteiraAluno($request)
		){
			$status = 'logoInvalid';
		}elseif(!self::uploadAssinaturaCarteiraAluno($request)){
			$status = 'assinaturaInvalid';
		}

		$request->getRouter()->redirect('/configuracoes?statusMessage='.$status);
	}
}
