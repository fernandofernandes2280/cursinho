<?php

namespace App\Controller\Painel;

use \App\Utils\View;
use \App\Model\Entity\Aluno as EntityAluno;
use \App\Model\Entity\Bairro as EntityBairro;
use \App\Model\Entity\Escolaridade as EntityEscolaridade;
use \App\Model\Entity\EstadoCivil as EntityEstadoCivil;
use \App\Model\Entity\Turma as EntityTurma;
use \App\Model\Entity\Status as EntityStatus;
use \App\Model\Entity\Configuracao as EntityConfiguracao;
use \App\Utils\Funcoes;
use \App\Controller\File\Upload as Upload;
use Bissolli\ValidadorCpfCnpj\CPF;
use \WilliamCosta\DatabaseManager\Database;

class Aluno extends Page{
	private const DOCUMENTOS_ALUNO = [
		'documentoIdentificacao' => [
			'fileName' => 'documento-identificacao.pdf',
			'label' => 'Documento de Identificação',
			'fallbackFileNames' => ['rg.pdf', 'cpf.pdf'],
		],
		'documentoResidencia' => [
			'fileName' => 'comprovante-residencia.pdf',
			'label' => 'Comprovante de Residência',
		],
		'documentoOutros' => [
			'fileName' => 'outros-documentos.pdf',
			'label' => 'Outros documentos',
		],
	];

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

	private static function formatDateTime($date){
		$timestamp = strlen((string)$date) ? strtotime($date) : false;

		return $timestamp ? date('d/m/Y H:i', $timestamp) : '-';
	}

	private static function escape($value){
		return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
	}

	private static function renderCarteiraLinhas($linhas){
		if(!is_array($linhas)){
			$linhas = [(string)$linhas];
		}

		$linhas = array_filter(array_map('trim', $linhas));
		$linhas = array_map([self::class, 'escape'], $linhas);

		return count($linhas) ? implode('<br>', $linhas) : '';
	}

	private static function getAlunoDocumentosDir($idAluno){
		return dirname(__DIR__).'/File/files/documentos-alunos/'.(int)$idAluno;
	}

	private static function getAlunoDocumentoUrl($idAluno, $fileName){
		return URL.'/app/Controller/File/files/documentos-alunos/'.(int)$idAluno.'/'.$fileName;
	}

	private static function getAlunoDocumentosMetaPath($idAluno){
		return self::getAlunoDocumentosDir($idAluno).'/documentos.json';
	}

	private static function getAlunoDocumentosMeta($idAluno){
		$path = self::getAlunoDocumentosMetaPath($idAluno);

		if(!is_file($path)){
			return [];
		}

		$meta = json_decode((string)file_get_contents($path), true);

		return is_array($meta) ? $meta : [];
	}

	private static function salvarAlunoDocumentosMeta($idAluno, $meta){
		return file_put_contents(
			self::getAlunoDocumentosMetaPath($idAluno),
			json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
		) !== false;
	}

	private static function getUploadOriginalName($file, $fallback){
		$name = basename(str_replace('\\', '/', (string)($file['name'] ?? '')));

		return $name !== '' ? $name : $fallback;
	}

	private static function getAlunoDocumentoFileNames($documento){
		return array_merge([$documento['fileName']], (array)($documento['fallbackFileNames'] ?? []));
	}

	private static function limparDocumentosSubstituidos($dir, $documento, &$meta){
		foreach(self::getAlunoDocumentoFileNames($documento) as $fileName){
			if($fileName === $documento['fileName']){
				continue;
			}

			$path = $dir.'/'.$fileName;

			if(is_file($path) && !unlink($path)){
				return false;
			}

			unset($meta[$fileName]);
		}

		return true;
	}

	private static function getAlunoDocumentoArquivo($idAluno, $documento){
		foreach(self::getAlunoDocumentoFileNames($documento) as $currentFileName){
			$path = self::getAlunoDocumentosDir($idAluno).'/'.$currentFileName;

			if(is_file($path)){
				return $currentFileName;
			}
		}

		return '';
	}

	private static function getAlunoDocumentoInfo($idAluno, $fileName, $fallbackFileNames = []){
		if((int)$idAluno <= 0){
			return '';
		}

		$fileNames = self::getAlunoDocumentoFileNames([
			'fileName' => $fileName,
			'fallbackFileNames' => $fallbackFileNames,
		]);
		$meta = self::getAlunoDocumentosMeta($idAluno);

		foreach($fileNames as $currentFileName){
			$path = self::getAlunoDocumentosDir($idAluno).'/'.$currentFileName;

			if(is_file($path)){
				$displayName = trim((string)($meta[$currentFileName]['originalName'] ?? '')) ?: $currentFileName;

				return '<a href="'.self::getAlunoDocumentoUrl($idAluno, $currentFileName).'" target="_blank" rel="noopener" class="student-name-link">'.self::escape($displayName).'</a>';
			}
		}

		return '';
	}

	private static function getAlunoDocumentoDeleteButton($idAluno, $field){
		if((int)$idAluno <= 0 || !isset(self::DOCUMENTOS_ALUNO[$field])){
			return '';
		}

		$currentFileName = self::getAlunoDocumentoArquivo($idAluno, self::DOCUMENTOS_ALUNO[$field]);

		if($currentFileName === ''){
			return '';
		}

		return '<button type="button" class="aluno-document-delete" data-document-delete="'.URL.'/alunos/'.(int)$idAluno.'/documentos/'.$field.'/delete" title="Excluir documento"><i class="material-icons">delete</i></button>';
	}

	private static function getAlunoDocumentosVars($idAluno = null){
		$idAluno = (int)$idAluno;

		return [
			'documentoIdentificacaoInfo' => self::getAlunoDocumentoInfo(
				$idAluno,
				self::DOCUMENTOS_ALUNO['documentoIdentificacao']['fileName'],
				self::DOCUMENTOS_ALUNO['documentoIdentificacao']['fallbackFileNames']
			),
			'documentoResidenciaInfo' => self::getAlunoDocumentoInfo($idAluno, self::DOCUMENTOS_ALUNO['documentoResidencia']['fileName']),
			'documentoOutrosInfo' => self::getAlunoDocumentoInfo($idAluno, self::DOCUMENTOS_ALUNO['documentoOutros']['fileName']),
			'documentoIdentificacaoDelete' => self::getAlunoDocumentoDeleteButton($idAluno, 'documentoIdentificacao'),
			'documentoResidenciaDelete' => self::getAlunoDocumentoDeleteButton($idAluno, 'documentoResidencia'),
			'documentoOutrosDelete' => self::getAlunoDocumentoDeleteButton($idAluno, 'documentoOutros'),
		];
	}

	private static function isPdfUpload($file){
		if(!is_array($file) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE){
			return true;
		}

		if((int)($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK){
			return false;
		}

		$extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));

		if($extension !== 'pdf'){
			return false;
		}

		$tmpName = (string)($file['tmp_name'] ?? '');
		$type = strtolower((string)($file['type'] ?? ''));

		if(is_file($tmpName)){
			$header = file_get_contents($tmpName, false, null, 0, 1024);
			if($header !== false && strpos($header, '%PDF-') !== false){
				return true;
			}

			if(function_exists('finfo_open')){
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				if($finfo){
					$detectedType = finfo_file($finfo, $tmpName);
					finfo_close($finfo);
					if($detectedType){
						$type = strtolower((string)$detectedType);
					}
				}
			}
		}

		return in_array($type, ['application/pdf', 'application/x-pdf'], true);
	}

	private static function documentosAlunoValidos($request){
		$fileVars = $request->getFileVars();

		foreach(self::DOCUMENTOS_ALUNO as $field => $documento){
			$file = $fileVars[$field] ?? null;

			if(!is_array($file) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE){
				continue;
			}

			if(!self::isPdfUpload($file)){
				return false;
			}
		}

		return true;
	}

	private static function salvarDocumentosAluno($request, $idAluno){
		$fileVars = $request->getFileVars();
		$dir = self::getAlunoDocumentosDir($idAluno);
		$temArquivos = false;

		if(!self::documentosAlunoValidos($request)){
			return false;
		}

		foreach(self::DOCUMENTOS_ALUNO as $field => $documento){
			$file = $fileVars[$field] ?? null;
			$temArquivos = $temArquivos || (is_array($file) && (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE);
		}

		if(!$temArquivos){
			return true;
		}

		if(!is_dir($dir) && !mkdir($dir, 0775, true)){
			return false;
		}

		$meta = self::getAlunoDocumentosMeta($idAluno);

		foreach(self::DOCUMENTOS_ALUNO as $field => $documento){
			$file = $fileVars[$field] ?? null;

			if(!is_array($file) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE){
				continue;
			}

			if(!move_uploaded_file($file['tmp_name'], $dir.'/'.$documento['fileName'])){
				return false;
			}

			if(!self::limparDocumentosSubstituidos($dir, $documento, $meta)){
				return false;
			}

			$meta[$documento['fileName']] = [
				'originalName' => self::getUploadOriginalName($file, $documento['fileName']),
				'updatedAt' => date('c'),
			];
		}

		return self::salvarAlunoDocumentosMeta($idAluno, $meta);
	}

	private static function excluirDocumentoAluno($idAluno, $field){
		if(!isset(self::DOCUMENTOS_ALUNO[$field])){
			return false;
		}

		$dir = self::getAlunoDocumentosDir($idAluno);

		if(!is_dir($dir)){
			return true;
		}

		$meta = self::getAlunoDocumentosMeta($idAluno);

		foreach(self::getAlunoDocumentoFileNames(self::DOCUMENTOS_ALUNO[$field]) as $fileName){
			$path = $dir.'/'.$fileName;

			if(is_file($path) && !unlink($path)){
				return false;
			}

			unset($meta[$fileName]);
		}

		return self::salvarAlunoDocumentosMeta($idAluno, $meta);
	}

	public static function setDeleteDocumentoAluno($request, $id, $documento){
		$obAluno = EntityAluno::getAlunoById($id);

		if(!$obAluno instanceof EntityAluno){
			$request->getRouter()->redirect('/alunos');
		}

		if(!self::excluirDocumentoAluno($obAluno->id, $documento)){
			$request->getRouter()->redirect('/alunos/'.$obAluno->id.'/edit?statusMessage=documentoDeleteError');
		}

		$request->getRouter()->redirect('/alunos/'.$obAluno->id.'/edit?statusMessage=documentoDeleted');
	}

	private static function getFrequenciaStatusLabel($status){
		if($status === 'P'){
			return 'Presente';
		}

		if($status === 'F'){
			return 'Falta';
		}

		return strlen((string)$status) ? (string)$status : '-';
	}

	private static function getFrequenciaStatusClass($status){
		if($status === 'P'){
			return 'bg-gradient-success';
		}

		if($status === 'F'){
			return 'bg-gradient-danger';
		}

		return 'bg-gradient-warning';
	}

	private static function getAlunoFrequenciasItems($idAluno){
		$resultados = '';
		$table = 'frequencia AS F
			INNER JOIN aulas AS AU ON AU.id = F.idAula
			LEFT JOIN turmas AS T ON T.id = AU.turma
			LEFT JOIN statusAula AS SA ON SA.id = AU.status
			LEFT JOIN professores AS P1 ON P1.id = AU.professor1
			LEFT JOIN professores AS P2 ON P2.id = AU.professor2
			LEFT JOIN disciplinas AS D1 ON D1.id = AU.disciplina1
			LEFT JOIN disciplinas AS D2 ON D2.id = AU.disciplina2';
		$fields = 'F.id,
			F.idAula,
			F.status AS frequenciaStatus,
			F.dataReg AS frequenciaDataReg,
			AU.data AS aulaData,
			AU.diaSemana,
			T.nome AS turmaNome,
			SA.nome AS aulaStatus,
			P1.nome AS professor1Nome,
			P2.nome AS professor2Nome,
			D1.nome AS disciplina1Nome,
			D2.nome AS disciplina2Nome';
		$results = (new Database($table))->select(
			'F.idAluno = '.(int)$idAluno,
			'AU.data DESC, AU.id DESC',
			null,
			$fields
		);

		while($obFrequencia = $results->fetchObject()){
			$resultados .= View::render('painel/modules/alunos/itemFrequencia',[
				'idAula' => (int)$obFrequencia->idAula,
				'data' => self::formatDate($obFrequencia->aulaData).' ( '.self::escape($obFrequencia->diaSemana ?: '-').' )',
				'turma' => self::escape($obFrequencia->turmaNome ?: 'Sem turma'),
				'professor1' => self::escape($obFrequencia->professor1Nome ?: 'Sem professor'),
				'disciplina1' => self::escape($obFrequencia->disciplina1Nome ?: 'Sem disciplina'),
				'professor2' => self::escape($obFrequencia->professor2Nome ?: ''),
				'disciplina2' => self::escape($obFrequencia->disciplina2Nome ?: ''),
				'frequencia' => self::escape(self::getFrequenciaStatusLabel($obFrequencia->frequenciaStatus)),
				'frequenciaCor' => self::getFrequenciaStatusClass($obFrequencia->frequenciaStatus),
				'aulaStatus' => self::escape($obFrequencia->aulaStatus ?: 'Sem status'),
				'registro' => self::formatDateTime($obFrequencia->frequenciaDataReg),
			]);
		}

		return $resultados;
	}

	private static function getAlunoFrequenciasResumo($idAluno){
		$obResumo = (new Database('frequencia'))->select(
			'idAluno = '.(int)$idAluno,
			null,
			null,
			'COUNT(*) AS total, SUM(status = "P") AS presencas, SUM(status = "F") AS faltas'
		)->fetchObject();
		$total = (int)($obResumo->total ?? 0);
		$presencas = (int)($obResumo->presencas ?? 0);
		$faltas = (int)($obResumo->faltas ?? 0);
		$percentual = $total > 0 ? ($presencas / $total) * 100 : 0;

		return [
			'total' => $total,
			'presencas' => $presencas,
			'faltas' => $faltas,
			'percentual' => number_format($percentual, 1, ',', '.').'%',
		];
	}

	private static function getAlunoFrequenciasTable($obAluno){
		$resumo = self::getAlunoFrequenciasResumo($obAluno->id);

		return View::render('painel/modules/alunos/frequencias',[
			'title' => 'Frequências das aulas',
			'subtitle' => self::escape($obAluno->matricula.' - '.$obAluno->nome),
			'totalFrequencias' => $resumo['total'],
			'totalPresencas' => $resumo['presencas'],
			'totalFaltas' => $resumo['faltas'],
			'percentualPresenca' => $resumo['percentual'],
			'itens' => self::getAlunoFrequenciasItems($obAluno->id),
		]);
	}

	private static function getAlunoEndereco($obAluno){
		$obBairro = (int)$obAluno->bairro > 0 ? EntityBairro::getBairroById((int)$obAluno->bairro) : null;
		$endereco = trim((string)$obAluno->endereco);
		$numero = trim((string)$obAluno->numero);
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

	private static function getCarteiraQrcodeInfo($obAluno){
		$oQRC = new \App\Controller\Qrcode\Qrcode();
		$oQRC->fullName($obAluno->matricula);

		$name = $obAluno->matricula.'.png';
		$src = 'data:image/png;base64,'.base64_encode($oQRC->png(300));

		return [
			'name' => $name,
			'src' => $src,
		];
	}

	private static function getCarteiraConfigVars(){
		$marcaDaguaCarteiraAlunoHtml = EntityConfiguracao::getUsarMarcaDaguaCarteiraAluno()
			? '<img width="400" class="img-fluid logocursocontainer" alt="Marca d\'água da carteira digital do aluno" src="'.htmlspecialchars(EntityConfiguracao::getMarcaDaguaCarteiraAlunoUrl(), ENT_QUOTES, 'UTF-8').'">'
			: '';

		return [
			'logoCarteiraAluno1' => htmlspecialchars(EntityConfiguracao::getLogoCarteiraAluno1Url(), ENT_QUOTES, 'UTF-8'),
			'logoCarteiraAluno2' => htmlspecialchars(EntityConfiguracao::getLogoCarteiraAluno2Url(), ENT_QUOTES, 'UTF-8'),
			'cabecalhoCarteiraAluno' => self::renderCarteiraLinhas(EntityConfiguracao::getCabecalhoCarteiraAlunoLinhas()),
			'cabecalhoCarteiraAlunoTamanho' => EntityConfiguracao::getCabecalhoCarteiraAlunoTamanho(),
			'cabecalhoCarteiraAlunoCor' => htmlspecialchars(EntityConfiguracao::getCabecalhoCarteiraAlunoCor(), ENT_QUOTES, 'UTF-8'),
			'subtituloCarteiraAluno' => self::escape(EntityConfiguracao::getSubtituloCarteiraAluno()),
			'subtituloCarteiraAlunoTamanho' => EntityConfiguracao::getSubtituloCarteiraAlunoTamanho(),
			'subtituloCarteiraAlunoCor' => htmlspecialchars(EntityConfiguracao::getSubtituloCarteiraAlunoCor(), ENT_QUOTES, 'UTF-8'),
			'textoCentralCarteiraAluno' => self::escape(EntityConfiguracao::getTextoCentralCarteiraAluno()),
			'textoCentralCarteiraAlunoTamanho' => EntityConfiguracao::getTextoCentralCarteiraAlunoTamanho(),
			'textoCentralCarteiraAlunoCor' => htmlspecialchars(EntityConfiguracao::getTextoCentralCarteiraAlunoCor(), ENT_QUOTES, 'UTF-8'),
			'rodapeCarteiraAluno' => self::escape(EntityConfiguracao::getRodapeCarteiraAluno()),
			'rodapeCarteiraAlunoTamanho' => EntityConfiguracao::getRodapeCarteiraAlunoTamanho(),
			'rodapeCarteiraAlunoCor' => htmlspecialchars(EntityConfiguracao::getRodapeCarteiraAlunoCor(), ENT_QUOTES, 'UTF-8'),
			'corFundoCarteiraAluno' => htmlspecialchars(EntityConfiguracao::getCorFundoCarteiraAluno(), ENT_QUOTES, 'UTF-8'),
			'marcaDaguaCarteiraAlunoHtml' => $marcaDaguaCarteiraAlunoHtml,
			'marcaDaguaCarteiraAlunoOpacidade' => EntityConfiguracao::getMarcaDaguaCarteiraAlunoOpacidadeCss(),
			'assinaturaCarteiraAluno' => htmlspecialchars(EntityConfiguracao::getAssinaturaCarteiraAlunoUrl(), ENT_QUOTES, 'UTF-8'),
		];
	}

	private static function getCarteiraAlunoVars($obAluno, $cardIdAttribute = '', $cardExtraClass = '', $configVars = null){
		$qrcode = self::getCarteiraQrcodeInfo($obAluno);
		$obTurma = (int)$obAluno->turma > 0 ? EntityTurma::getTurmaById((int)$obAluno->turma) : null;
		$obStatus = (int)$obAluno->status > 0 ? EntityStatus::getStatusById((int)$obAluno->status) : null;
		$configVars = is_array($configVars) ? $configVars : self::getCarteiraConfigVars();
		$nomeAluno = strtoupper((string)$obAluno->nome);
		$nomeAlunoLength = strlen($nomeAluno);
		$nomeCarteiraAlunoClass = $nomeAlunoLength > 60
			? ' carteira-info-nome-extra-long'
			: ($nomeAlunoLength > 42 ? ' carteira-info-nome-long' : '');

		return array_merge($configVars, [
			'foto' => $obAluno->getFoto(),
			'matricula'=> $obAluno->matricula,
			'nome' => $nomeAluno,
			'nomeCarteiraAlunoClass' => $nomeCarteiraAlunoClass,
			'turma' => $obTurma ? strtoupper((string)$obTurma->nome) : 'SEM TURMA',
			'mae' => strtoupper((string)$obAluno->mae),
			'cpf' => Funcoes::mask($obAluno->cpf, '###.###.###-##'),
			'dataNasc' => self::formatDate($obAluno->dataNasc),
			'dataCad'=> self::formatDate($obAluno->dataCad),
			'qrcode' => $qrcode['name'],
			'qrcodePath' => $qrcode['src'],
			'status' => $obStatus ? $obStatus->nome : 'Sem status',
			'cardIdAttribute' => $cardIdAttribute,
			'cardExtraClass' => $cardExtraClass,
		]);
	}

	private static function renderCarteiraAlunoCard($obAluno, $cardIdAttribute = '', $cardExtraClass = '', $configVars = null){
		return View::render('pages/carteira/card', self::getCarteiraAlunoVars($obAluno, $cardIdAttribute, $cardExtraClass, $configVars));
	}

	private static function renderCarteiraStyles(){
		return View::render('pages/carteira/styles', self::getCarteiraConfigVars());
	}

	//Método responsavel por obter a rendereizacao da lista de Alunos
	private static function getAlunoItems($request){

		$resultados = '';
		$order = 'nome ASC'; ;
		$results = EntityAluno::getAlunos(null, $order);

		while ($obAluno = $results -> fetchObject(EntityAluno::class)) {

			    $obStatus = $obAluno->status !== null && $obAluno->status !== '' ? EntityStatus::getStatusById((int)$obAluno->status) : null;
			    $obTurma = (int)$obAluno->turma > 0 ? EntityTurma::getTurmaById((int)$obAluno->turma) : null;
			    $obEscolaridade = (int)$obAluno->escolaridade > 0 ? EntityEscolaridade::getEscolaridadeById((int)$obAluno->escolaridade) : null;
			    $statusCor = $obAluno->status == 1 ? 'bg-gradient-success' : 'bg-gradient-danger';
			    $statusToken = $obAluno->status == 1 ? 'ativo' : 'inativo';

				$resultados .= View::render('painel/modules/alunos/item',[
			    'nome' => $obAluno->nome,
			    'status' => $obStatus ? $obStatus->nome : 'Sem status',
			    'cpf' => Funcoes::mask($obAluno->cpf, '###.###.###-##') ,
			    'id' => $obAluno->id,
				    'matricula' => $obAluno->matricula,
				    'turma' => $obTurma ? $obTurma->nome : 'Sem turma',
				    'fone' => self::formatPhone($obAluno->fone),
				    'endereco' => self::getAlunoEndereco($obAluno),
				    'escolaridade' => $obEscolaridade ? $obEscolaridade->nome : '',
				    'sexo' => $obAluno->sexo,
				    'dataNasc' => self::formatDate($obAluno->dataNasc),
				    'dataCad' => self::formatDate($obAluno->dataCad),
				    'mae' => $obAluno->mae,
				    'foto' => $obAluno->getFoto(),
				    'statusCor' => $statusCor,
			    'statusToken' => $statusToken,
			    'visivelDeleteAluno' => permissaoExcluirAluno,
			]);
		}

		return $resultados;
	}



	//Método responsavel por renderizar a view de Listagem de Alunos
	public static function getAlunos($request){

	    //finaliza sessao de aluno novo caso estejam ativas
	    Funcoes::init();
	    EntityAluno::getFinalizaSessaoDados();

		//Recebe os parâmetros da requisição
		$queryParams = $request->getQueryParams();

		//esconde busca rápida de prontuário no navBar
		$hidden = '';
			//Conteúdo da Home
		$content = View::render('painel/modules/alunos/index',[
				'title' => 'Alunos',
				'itens' => self::getAlunoItems($request),
				'statusMessage' => Funcoes::getStatus($request),
				'nome' =>  $queryParams['nome'] ?? '',
				'matricula' =>  $queryParams['matricula'] ?? '',
		        'id' =>  $queryParams['id'] ?? '',
		        'matricula' =>  $queryParams['matricula'] ?? '',
		        'cpf' =>  $queryParams['cpfPesq'] ?? '',
		]);

		//Retorna a página completa
		return parent::getPanel('Alunos > Cursinho', $content,'alunos');

	}

	public static function getCarteirasAlunos($request){
		Funcoes::init();

		$queryParams = $request->getQueryParams();
		$ids = [];

		if(isset($queryParams['ids'])){
			foreach(explode(',', (string)$queryParams['ids']) as $id){
				$id = (int)$id;
				if($id > 0){
					$ids[] = $id;
				}
			}
			$ids = array_values(array_unique($ids));
		}

		$where = count($ids) ? 'id IN ('.implode(',', $ids).')' : null;
		$results = EntityAluno::getAlunos($where, 'nome ASC');
		$carteiras = '';
		$total = 0;
		$configVars = self::getCarteiraConfigVars();

		while($obAluno = $results->fetchObject(EntityAluno::class)){
			$carteiras .= self::renderCarteiraAlunoCard($obAluno, '', 'carteira-card-print', $configVars);
			$total++;
		}

		return View::render('pages/carteiras-alunos', [
			'title' => 'Carteiras dos alunos',
			'carteiras' => $carteiras,
			'carteiraStyles' => View::render('pages/carteira/styles', $configVars),
			'totalCarteiras' => $total,
		]);
	}



	//Método responsavel por renderizar a view de Listagem de Pacientes
	public static function setCortarFoto($request){

	    //Recebe os parâmetros da requisição
	    $postVars = $request->getPostVars();

	//    var_dump($postVars);exit;
	    //obtém o Aluno do banco de dados
	    $obAluno = EntityAluno::getAlunoById($id);

	    //Valida a instancia
	    if(!$obAluno instanceof EntityAluno){
	        $request->getRouter()->redirect('/alunos');
	    }

	    $src = $obAluno->getFoto(false);

	    //esconde busca rápida de prontuário no navBar
	    $hidden = '';



		    //Conteúdo da Home
	    $content = View::render('painel/modules/alunos/formCortarPhoto',[
	        'foto' => $src

	    ]);

	    //Retorna a página completa
	    return parent::getPanel('Cortar Foto > Cursinho', $content,'alunos');

	}


	//Metodo responsávelpor retornar o formulário de Captura de foto do aluno
	public static function getPhotoAluno($request,$id){
	    $obAluno = EntityAluno::getAlunoById($id);

	    //Conteúdo do Formulário
	    $content = View::render('painel/modules/alunos/formPhoto',[
	       'title' => 'Alunos > Capturar foto',
	        'aluno' => $obAluno->matricula.' '.$obAluno->nome,
	        'id' => $obAluno->id
	    ]);

	    //Retorna a página completa
	    return parent::getPanel('Editar Aluno > Cursinho', $content,'alunos');

	}


	//Metodo responsávelpor retornar o formulário de Captura de foto do aluno
	public static function setPhotoAluno($request){

	    //Post Vars
	    $postVars = $request->getPostVars();
	    $fileVars = $request->getFileVars();


	    $obAluno = EntityAluno::getAlunoById($postVars['id']);
	    $fileUpload = $fileVars['fImage'] ?? null;
	    $hasFileUpload = is_array($fileUpload)
	        && trim((string)($fileUpload['name'] ?? '')) !== ''
	        && (int)($fileUpload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
	    $hasWebcamImage = trim((string)($postVars['image'] ?? '')) !== '';

	    if($hasFileUpload){
	        $postVars['image'] = '';

	        if(!Upload::setUploadImages($request)){
	            $request->getRouter()->redirect('/alunos/'.$obAluno->id.'/edit?statusMessage=fotoSaveError');
	        }
	        //Redireciona o usuário
	        $request->getRouter()->redirect('/alunos/'.$obAluno->id.'/edit?statusMessage=updated');
	    }

	    if ($hasWebcamImage){



	    //MÉTODO RESPONSÁVEL POR FAZER O UPLOADO DA IMAGE VINDA DA WEB CAM DO PROFESSOR
	    if(!Upload::setUploadImagesWebCamAluno($request)){
	        $request->getRouter()->redirect('/alunos/'.$obAluno->id.'/edit?statusMessage=fotoSaveError');
	    }




    	    //Redireciona o usuário
    	    $request->getRouter()->redirect('/alunos/'.$obAluno->id.'/edit?statusMessage=updated');
	    }

	    $request->getRouter()->redirect('/alunos/'.$obAluno->id.'/edit?statusMessage=semfoto');


	}




	//Metodo responsávelpor retornar o formulário de Edição de um Aluno
	public static function getEditAluno($request,$id){

	    Funcoes::init();
	    $idAula = $_SESSION['idAula'] ?? '';
	    $hideBtnFreq = '';

	    //obtém o Aluno do banco de dados
	    $obAluno = EntityAluno::getAlunoById($id);

	    //Valida a instancia
	    if(!$obAluno instanceof EntityAluno){
	        $request->getRouter()->redirect('/alunos');
	    }

	    $obAluno->sexo == 'MAS' ? $selectedSexoM = 'selected' : $selectedSexoM = '';
	    $obAluno->sexo == 'FEM' ? $selectedSexoF = 'selected' : $selectedSexoF = '';

	    //Conteúdo do Formulário
	    $content = View::render('painel/modules/alunos/form', array_merge([
	        'matricula'=>$obAluno->matricula,
	        'id' => $obAluno->id,
	        'title' => 'Alunos > Editar',
	        'nome' => $obAluno->nome,
	        'cep' => $obAluno->cep,
	        'endereco' => $obAluno->endereco,
	        'numero' => $obAluno->numero,
	        'statusMessage' => Funcoes::getStatus($request),
	        'naturalidade' => $obAluno->naturalidade,
	        'fone' =>$obAluno->fone ,
	        'mae' => $obAluno->mae,
	        'obs' => $obAluno->obs,
	        'cpf' => Funcoes::mask($obAluno->cpf, '###.###.###-##') ,
	        'optionBairros' => EntityBairro::getSelectBairros($obAluno->bairro),
	        'optionEscolaridade' => EntityEscolaridade::getSelectEscolaridade($obAluno->escolaridade),
	        'optionEstadoCivil' => EntityEstadoCivil::getSelectEstadoCivil($obAluno->estadoCivil),
	        'cidade' => $obAluno->cidade,
	        'uf' => $obAluno->uf,
	        'dataNasc' => date('Y-m-d', strtotime($obAluno->dataNasc)),
	        'dataCad' => date('Y-m-d', strtotime($obAluno->dataCad)),
	        'optionTurma' => EntityTurma::getSelectTurmas($obAluno->turma),
	        'optionStatus' => EntityStatus::getSelectStatus($obAluno->status),
	        'foto' => $obAluno->getFoto(),
	        'ponteiro' => '',
	        'fotoLink' => URL.'/alunos/photo/'.$obAluno->id,
	        'fotoSubmit' => '0',
	        'idAula' => @$idAula,
	        'hideBtnFreq' => $hideBtnFreq,
	        'idAluno' => $obAluno->id,
	        'selectedSexoM' => $selectedSexoM  ,
	        'selectedSexoF' => $selectedSexoF
	    ], self::getAlunoDocumentosVars($obAluno->id)));
	    $content .= self::getAlunoFrequenciasTable($obAluno);

	    //Retorna a página completa
	    return parent::getPanel('Editar Aluno > Cursinho', $content,'alunos');

	}

	//Metodo responsável por gravar a atualização de um Paciente
	public static function setEditAluno($request,$id){
	    //Post Vars
	    $postVars = $request->getPostVars();




	    //instancia classe pra verificar CPF
	    $validaCpf = new CPF($postVars['cpf']);

	    //obtém o Aluno do banco de dados
	    $obAluno = EntityAluno::getAlunoById($id);


	    //verifica se cpf informado é diferente do cpf já cadastrado
	    if($obAluno->cpf != $validaCpf->getValue()){
	        //busca usuário pelo CPF sem a maskara
	        $ob = EntityAluno::getAlunoByCpf($validaCpf->getValue());
	        //verifica se cpf informado já está cadastrado
	        if(($ob instanceof EntityAluno)){
	            $request->getRouter()->redirect('/alunos/'.$ob->id.'/edit?statusMessage=cpfDuplicated');
	        }
	    }


	    //Valida a instancia
	    if(!$obAluno instanceof EntityAluno){
	        $request->getRouter()->redirect('/alunos');
	    }

	    //redireciona caso seja feita busca rápida pela Matrícula
	    if(@$postVars['matricula']){
	        //obtém o Aluno do banco de dados
	        $obAlunoMatricula = EntityAluno::getAlunoByMatricula($postVars['matricula']);
	        //redireciona para os dados do aluno
	        $request->getRouter()->redirect('/alunos/'.$obAlunoMatricula->id.'/edit');

	    }

	    if(!self::documentosAlunoValidos($request)){
	        $request->getRouter()->redirect('/alunos/'.$obAluno->id.'/edit?statusMessage=documentoInvalid');
	    }


	    //Atualiza a instância
	    $obAluno->nome = Funcoes::convertePriMaiuscula($postVars['nome']) ?? $obAluno->nome;
	    $obAluno->cep = $postVars['cep'] ?? $obAluno->cep;
	    $obAluno->endereco = Funcoes::convertePriMaiuscula($postVars['endereco']) ?? $obAluno->endereco;
	    $obAluno->numero =  $postVars['numero'] ?? $obAluno->numero;
	    $obAluno->bairro =  $postVars['bairro'] ?? $obAluno->bairro;
	    $obAluno->cidade = $postVars['cidade'] ?? $obAluno->cidade;
	    $obAluno->uf = Funcoes::convertePriMaiuscula($postVars['uf']) ?? $obAluno->uf;
	    $obAluno->dataNasc = implode("-",array_reverse(explode("/",$postVars['dataNasc'])));
	    //recebe a data do formulário e converte para objeto data
	    $dataCad = date_create_from_format('Y-m-d', $postVars['dataCad']);
	    //formata a data vinda do formulário com a hora atual
	    $obAluno->dataCad = $dataCad->format('Y-m-d H:i:s');
	    $obAluno->sexo = $postVars['sexo'] ?? $obAluno->sexo;
	    $obAluno->naturalidade = $postVars['naturalidade'] ?? $obAluno->naturalidade;
	    $obAluno->escolaridade = $postVars['escolaridade'] ?? $obAluno->escolaridade;
	    $obAluno->fone = preg_replace('/\D+/', '', $postVars['fone'] ?? '') ?: $obAluno->fone;
	    $obAluno->mae = Funcoes::convertePriMaiuscula($postVars['mae'])?? $obAluno->mae;
	    $obAluno->estadoCivil = $postVars['estadoCivil'] ?? $obAluno->estadoCivil;
	    $obAluno->status = $postVars['status'] ?? $obAluno->status;
	    $obAluno->obs = $postVars['obs'] ?? $obAluno->obs;
	    $obAluno->sexo = $postVars['sexo'] ?? $obAluno->sexo;
	    //recebe apenas os números do cpf
	    $obAluno->cpf = $validaCpf->getValue() ?? $obAluno->cpf;
	    $obAluno->turma = $postVars['turma'] ?? $obAluno->turma;
	    $obAluno->atualizar();

	    if(!self::salvarDocumentosAluno($request, $obAluno->id)){
	        $request->getRouter()->redirect('/alunos/'.$obAluno->id.'/edit?statusMessage=documentoInvalid');
	    }

	    //	Logs::setNewLog($request);

	    //Redireciona o usuário
	    $request->getRouter()->redirect('/alunos/'.$obAluno->id.'/edit?statusMessage=updated');

	}

	//Metodo responsávelpor retornar o formulário de Cadastro de um Aluno
	public static function getNewAluno($request){

	    //Inicia sessão
	    Funcoes::init();

	    $queryParams = $request->getQueryParams();
	    $old = Funcoes::pullOldInput('aluno.novo');
	    $statusMessage = $queryParams['statusMessage'] ?? '';
	    $cpfAluno = $queryParams['cpfAluno'] ?? ($old['cpf'] ?? '');


	    //instancia classe pra verificar CPF
	    $validaCpf = new CPF($cpfAluno);

	    //verifica se é válido o cpf
	    if (!$validaCpf->isValid()){

	        $request->getRouter()->redirect('/alunos/?statusMessage=cpfInvalid');
	    }


	    //busca usuário pelo CPF sem a maskara
	    $ob = EntityAluno::getAlunoByCpf($validaCpf->getValue());
	    //verifica se o cpf já está cadastrado
	    if($ob instanceof EntityAluno && !in_array($statusMessage, ['cpfDuplicated', 'cpfduplicated'])){
	        $request->getRouter()->redirect('/alunos?statusMessage=duplicad');
	    }

	    $sexo = $old['sexo'] ?? '';

	    //Conteúdo do Formulário
	    $content = View::render('painel/modules/alunos/form', array_merge([
	        'matricula'=> '',
	        'title' => 'Alunos > Novo',
	        'nome' => $old['nome'] ?? '',
	        'cep' => $old['cep'] ?? '',
	        'endereco' => $old['endereco'] ?? '',
	        'numero' => $old['numero'] ?? '',
	        'naturalidade' => $old['naturalidade'] ?? '',
	        'fone' => ($old['fone'] ?? '') ?: '(00)00000-0000',
	        'mae' => $old['mae'] ?? '',
	        'obs' => $old['obs'] ?? '',
	        'cpf' => $old['cpf'] ?? $validaCpf->getValue(),
	        'dataNasc' => $old['dataNasc'] ??'',
	        'dataCad' => $old['dataCad'] ?? date('Y-m-d'),
	        'statusMessage' => Funcoes::getStatus($request),
	        'optionBairros' => EntityBairro::getSelectBairros($old['bairro'] ?? null),
	        'optionEscolaridade' => EntityEscolaridade::getSelectEscolaridade($old['escolaridade'] ?? null),
	        'optionEstadoCivil' => EntityEstadoCivil::getSelectEstadoCivil($old['estadoCivil'] ?? null),
	        'cidade' => $old['cidade'] ?? 'Santana',
	        'uf' => $old['uf'] ?? 'Ap',
	        'optionTurma' => EntityTurma::getSelectTurmas($old['turma'] ?? null),
	        'optionStatus' => EntityStatus::getSelectStatus($old['status'] ?? null),
	        'foto' => EntityAluno::FOTO_PADRAO,
	        'ponteiro' => '',
	        'fotoLink' => '#',
	        'fotoSubmit' => '1',
	        'id' => '',
	        'idAula' => '',
	        'idAluno' => '',
	        'hideBtnFreq' => 'hidden',
	        'selectedSexoM' => $sexo === 'MAS' ? 'selected' : '',
	        'selectedSexoF' => $sexo === 'FEM' ? 'selected' : ''

	    ], self::getAlunoDocumentosVars()));

	    //Retorna a página completa
	    return parent::getPanel('Novo Aluno > Cursinho', $content,'alunos');

	}

	//Metodo responsável por gravar um Novo Aluno
	public static function setNewAluno($request){


	    //Post Vars
	    $postVars = $request->getPostVars();

	    //Cria sessão com os dados do form
	    EntityAluno::getSessaoDados($postVars);


	    //instancia classe pra verificar CPF
	    $validaCpf = new CPF($postVars['cpf']);

	    //busca usuário pelo CPF sem a maskara
	    $ob = EntityAluno::getAlunoByCpf($validaCpf->getValue());
	    //verifica se o cpf já está cadastrado
	    if($ob instanceof EntityAluno){
	        $request->getRouter()->redirect('/alunos/new?'.http_build_query([
	            'cpfAluno' => $validaCpf->getValue(),
	            'statusMessage' => 'cpfDuplicated'
	        ]));
	    }

	    if(!self::documentosAlunoValidos($request)){
	        $request->getRouter()->redirect('/alunos/new?'.http_build_query([
	            'cpfAluno' => $validaCpf->getValue(),
	            'statusMessage' => 'documentoInvalid'
	        ]));
	    }


	    //Nova instância de Aluno
	    $obAluno = new EntityAluno;


	    //recebe os dados
	    $obAluno->nome = Funcoes::convertePriMaiuscula($postVars['nome']);
	    $obAluno->cep = $postVars['cep'];
	    $obAluno->endereco = Funcoes::convertePriMaiuscula($postVars['endereco']);
	    $obAluno->numero =  $postVars['numero'];
	    $obAluno->bairro =  $postVars['bairro'];
	    $obAluno->cidade = $postVars['cidade'];
	    $obAluno->uf = Funcoes::convertePriMaiuscula($postVars['uf']);
	    $obAluno->dataNasc = implode("-",array_reverse(explode("/",$postVars['dataNasc'])));
	    //Data de cadastro sempre baseada na data atual do sistema.
	    $obAluno->dataCad = date('Y-m-d H:i:s');
	    $obAluno->sexo = $postVars['sexo'];
	    $obAluno->naturalidade = $postVars['naturalidade'];
	    $obAluno->escolaridade = $postVars['escolaridade'];
	    $obAluno->fone = preg_replace('/\D+/', '', $postVars['fone'] ?? '');
	    $obAluno->mae = Funcoes::convertePriMaiuscula($postVars['mae']);
	    $obAluno->estadoCivil = $postVars['estadoCivil'];
	    $obAluno->status = $postVars['status'];
	    $obAluno->obs = $postVars['obs'];
	    //recebe apenas os números do cpf
	    $obAluno->cpf = $validaCpf->getValue();
	    $obAluno->turma = $postVars['turma'];
	    $obAluno->cadastrar();

	    //define a matrícula
	    $obMatricula = EntityAluno::getAlunoByCpf($validaCpf->getValue());
	    $obMatricula->matricula = EntityAluno::geraMatricula($obMatricula->id);
	    $obMatricula->atualizar();

	    if(!self::salvarDocumentosAluno($request, $obAluno->id)){
	        EntityAluno::getFinalizaSessaoDados();
	        $request->getRouter()->redirect('/alunos/'.$obAluno->id.'/edit?statusMessage=documentoInvalid');
	    }

	    //encerra sessão com os dados do form
	    EntityAluno::getFinalizaSessaoDados();

	    //	Logs::setNewLog($request);

	    //Redireciona o usuário
	    if(($postVars['redirectPhoto'] ?? '') == '1'){
	        $request->getRouter()->redirect('/alunos/photo/'.$obAluno->id);
	    }

	    $request->getRouter()->redirect('/alunos/'.$obAluno->id.'/edit?statusMessage=created');

	}

	//Metodo responsávelpor retornar o formulário de Exclusão de um Aluno
	public static function getDeleteAluno($request,$id){
	    //obtém o deopimento do banco de dados
	    $obAluno = EntityAluno::getAlunoById($id);

	    //Valida a instancia
	    if(!$obAluno instanceof EntityAluno){
	        $request->getRouter()->redirect('/alunos');
	    }


	    //Conteúdo do Formulário
	    $content = View::render('painel/modules/alunos/delete',[
	        'title'=> 'Alunos > Excluir',
	        'matricula' => $obAluno->matricula,
	        'nome' => $obAluno->nome


	    ]);

	    //Retorna a página completa
	    return parent::getPanel('Excluir Aluno > Cursinho', $content,'alunos');

	}
	//Metodo responsável por Excluir um Aluno
	public static function setDeleteAluno($request,$id){


	    //obtém o paciente do banco de dados
	    $obAluno = EntityAluno::getAlunoById($id);

	    //Valida a instancia
	    if(!$obAluno instanceof EntityAluno){
	        $request->getRouter()->redirect('/alunos');
	    }

	    //Exclui o depoimento
	    $obAluno->excluir();

	    //Redireciona o usuário
	    $request->getRouter()->redirect('/alunos?statusMessage=deleted');


	}

	//MÉTODO RESPONSÁVEL POR RENDERIZAR A CARTEIRA DE ALUNO
	public static function getCarteiraAluno($request,$id){

	    Funcoes::init();

	    if(empty($id)){

	            //VERIFICA SE O CADASTRO ESTÁ INCOMPLETO
	        if(isset($_SESSION['naoCompleto'])) $request->getRouter()->redirect('/aluno');

	        if(!isset($_SESSION['idAluno'])) $request->getRouter()->redirect('/aluno');

	    }

	    @$_SESSION['idAluno'] ? $id = $_SESSION['idAluno'] : $id = $id;

	    @$_SESSION['idAluno'] ? $hiddenBtnSairUpdate = '' : $hiddenBtnSairUpdate = 'hidden';
	    @$_SESSION['idAluno'] ? $hiddenBtnSair = 'hidden' : $hiddenBtnSair = '';


	    @$_SESSION['updated'] ? $hiddenAlterar = '' : $hiddenAlterar = 'hidden';

	    //obtém o Aluno do banco de dados
	    $obAluno = EntityAluno::getAlunoById($id);

	    //Valida a instancia
	    if(!$obAluno instanceof EntityAluno){
	        $request->getRouter()->redirect('/alunos');
	    }


	    //verifica se o aluno enviou a foto. se não enviou volta pro form update de cadastro
	    if(isset($_SESSION['idAluno']) && (!isset($_SESSION['usuario'])) && $obAluno->semFoto()) $request->getRouter()->redirect('/aluno/update');

	    $carteiraVars = self::getCarteiraAlunoVars($obAluno, 'id="target"', '');
	    $content = View::render('pages/carteira', array_merge($carteiraVars, [
	        'title'=>'Alunos > Carteira de Estudante',
	        'hiddenBtnAlterar' => $hiddenAlterar,
	        'hiddenBtnSairUpdate' => $hiddenBtnSairUpdate,
	        'hiddenBtnSair' => $hiddenBtnSair,
	        'carteiraCard' => View::render('pages/carteira/card', $carteiraVars),
	        'carteiraStyles' => View::render('pages/carteira/styles', $carteiraVars),
	    ]));

	    //Retorna a página completa

	 //   unset($_SESSION['idAluno']);

	        return parent::getPageCarteira('Carteira do Aluno > Cursinho', $content,'alunos');


	       // return parent::getPanel('Carteira do Aluno > Cursinho', $content,'alunos', 'hidden');

	}




	//MÉTODO RESPONSÁVEL POR GERAR O ARQUIVO DE IMAGEM DA CARTEIRA DE ALUNO
	public static function setCarteiraAluno($request,$id){


	    //Get the base-64 string from data
	    $filteredData=substr($_POST['img_val'], strpos($_POST['img_val'], ",")+1);

	    //Decode the string
	    $unencodedData=base64_decode($filteredData, true);

	    $name=$_POST['matricula'].$_POST['nome'].'.png';

	    $rotang = -90; // Rotation angle
	    $source = $unencodedData !== false ? imagecreatefromstring($unencodedData) : false;

	    if(!$source){
	        http_response_code(400);
	        exit('Imagem inválida.');
	    }

	    imagealphablending($source, false);
	    imagesavealpha($source, true);

	    $rotation = imagerotate($source, $rotang, imageColorAllocateAlpha($source, 0, 0, 0, 127));
	    imagealphablending($rotation, false);
	    imagesavealpha($rotation, true);

	    //download da imagem
	    if($_POST['opcao'] == 'down'){
	        header('Content-Disposition: Attachment;filename='.$name.'');
	    }

	    header('Content-type: image/png');
	    imagepng($rotation);
	    imagedestroy($source);
	    imagedestroy($rotation);

	    //   header("Content-Disposition: attachment; filename=\"$filename\"");
	    //  readfile($filename);

	    /*
	     header("Expires: 0");
	     header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	     header("Cache-Control: no-store, no-cache, must-revalidate");
	     header("Cache-Control: post-check=0, pre-check=0", false);
	     header("Pragma: no-cache");

	     $ext = pathinfo($file, PATHINFO_EXTENSION);
	     $basename = pathinfo($file, PATHINFO_BASENAME);

	     header("Content-type: application/".$ext);
	     // tell file size
	     header('Content-length: '.filesize($file));
	     // set file name
	     header("Content-Disposition: attachment; filename=\"$basename\"");
	     readfile($file);
	     */
	    // Exit script. So that no useless data is output.
	    exit;


	}






}
