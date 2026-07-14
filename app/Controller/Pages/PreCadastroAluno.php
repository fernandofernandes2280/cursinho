<?php

namespace App\Controller\Pages;

use \App\Controller\Painel\Resize;
use \App\Controller\Painel\Aluno as PainelAluno;
use \App\Model\Entity\Aluno as EntityAluno;
use \App\Model\Entity\Bairro as EntityBairro;
use \App\Model\Entity\Escolaridade as EntityEscolaridade;
use \App\Model\Entity\EstadoCivil as EntityEstadoCivil;
use \App\Model\Entity\Status as EntityStatus;
use \App\Model\Entity\Turma as EntityTurma;
use \App\Utils\Funcoes;
use \App\Utils\View;
use App\Service\AuditLogger;
use Bissolli\ValidadorCpfCnpj\CPF;

class PreCadastroAluno extends Page{
    private const DOCUMENTO_MAX_BYTES = 20 * 1024 * 1024;
    private const POST_MAX_BYTES = 25 * 1024 * 1024;
    private const SELFIE_MAX_BYTES = 20 * 1024 * 1024;
    private const STATUS_ALUNO_ATIVO = 1;
    private const AUDIT_ALUNO_FIELDS = [
        'id',
        'matricula',
        'nome',
        'cpf',
        'fone',
        'status',
        'turma',
        'dataNasc',
        'foto',
    ];

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
    ];

    private static function renderPage($title, $content){
        return View::render('pages/precadastro/page', [
            'title' => $title,
            'content' => $content,
        ]);
    }

    private static function getStatusMessage($status){
        $uploadMaxLabel = self::formatBytes(self::getUploadMaxBytes());
        $postMaxLabel = self::formatBytes(self::getPostMaxBytes());
        $messages = [
            'cpfInvalid' => ['danger', 'CPF inválido.'],
            'cpfNotFound' => ['danger', 'CPF não localizado para pré-cadastro.'],
            'inactiveComplete' => ['danger', 'Seu cadastro já está completo, mas sua situação está inativa. Procure a coordenação do cursinho para regularizar sua situação.'],
            'saved' => ['success', 'Pré-cadastro salvo com sucesso.'],
            'selfieRequired' => ['danger', 'Envie uma selfie para concluir o pré-cadastro.'],
            'fotoInvalid' => ['danger', 'Envie uma selfie válida em JPG ou PNG, com até '.self::formatBytes(self::SELFIE_MAX_BYTES).'.'],
            'requiredFields' => ['danger', 'Preencha todos os campos obrigatórios.'],
            'documentoInvalid' => ['danger', 'Envie os documentos somente em PDF.'],
            'documentoUploadError' => ['danger', 'Não foi possível receber o PDF. Verifique se cada arquivo tem até '.$uploadMaxLabel.' e se o envio total tem até '.$postMaxLabel.'.'],
            'documentoUploadLimit' => ['danger', 'O upload ultrapassou o limite permitido. Use PDFs de até '.$uploadMaxLabel.' cada e envio total de até '.$postMaxLabel.'. No servidor, mantenha upload_max_filesize=20M e post_max_size=25M.'],
            'documentoUploadPartial' => ['danger', 'O envio do PDF foi interrompido antes de concluir. Tente selecionar o arquivo novamente.'],
            'documentoUploadServerError' => ['danger', 'O servidor não conseguiu receber o PDF. Verifique a pasta temporária e as permissões de upload do PHP.'],
            'documentoSaveError' => ['danger', 'Não foi possível salvar os documentos. Verifique as permissões da pasta de uploads.'],
            'documentosRequired' => ['danger', 'Envie Documento de Identificação e Comprovante de Residência.'],
        ];

        if(!isset($messages[$status])){
            return '';
        }

        [$type, $message] = $messages[$status];

        return '<span class="precadastro-message precadastro-message-'.$type.'" role="status">'.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').'</span>';
    }

    private static function getCpfValido($cpf){
        $cpf = trim((string)$cpf);

        if($cpf === ''){
            return '';
        }

        $validaCpf = new CPF($cpf);

        return $validaCpf->isValid() ? $validaCpf->getValue() : '';
    }

    private static function nullIfBlank($value){
        if(is_null($value)){
            return null;
        }

        $value = trim((string)$value);

        return $value === '' ? null : $value;
    }

    private static function getOptionalSelectOptions($callback, $selected){
        $selected = self::nullIfBlank($selected);
        $options = call_user_func($callback, $selected ?? -1);

        return '<option value=""></option>'.$options;
    }

    private static function parseIniBytes($value){
        $value = trim((string)$value);

        if($value === ''){
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float)$value;

        if($unit === 'g'){
            return (int)($number * 1024 * 1024 * 1024);
        }

        if($unit === 'm'){
            return (int)($number * 1024 * 1024);
        }

        if($unit === 'k'){
            return (int)($number * 1024);
        }

        return (int)$number;
    }

    private static function getPhpUploadMaxBytes(){
        return self::parseIniBytes(ini_get('upload_max_filesize') ?: '');
    }

    private static function getPhpPostMaxBytes(){
        return self::parseIniBytes(ini_get('post_max_size') ?: '');
    }

    private static function getUploadMaxBytes(){
        $phpUploadMax = self::getPhpUploadMaxBytes();

        if($phpUploadMax <= 0){
            return self::DOCUMENTO_MAX_BYTES;
        }

        return min(self::DOCUMENTO_MAX_BYTES, $phpUploadMax);
    }

    private static function getPostMaxBytes(){
        $phpPostMax = self::getPhpPostMaxBytes();

        if($phpPostMax <= 0){
            return self::POST_MAX_BYTES;
        }

        return min(self::POST_MAX_BYTES, $phpPostMax);
    }

    private static function formatBytes($bytes){
        $bytes = (int)$bytes;

        if($bytes >= 1024 * 1024){
            return rtrim(rtrim(number_format($bytes / 1024 / 1024, 1, ',', ''), '0'), ',').' MB';
        }

        if($bytes >= 1024){
            return rtrim(rtrim(number_format($bytes / 1024, 1, ',', ''), '0'), ',').' KB';
        }

        return $bytes.' bytes';
    }

    private static function getDocumentoUploadErrorStatus($error){
        switch((int)$error){
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'documentoUploadLimit';
            case UPLOAD_ERR_PARTIAL:
                return 'documentoUploadPartial';
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_EXTENSION:
                return 'documentoUploadServerError';
        }

        return 'documentoUploadError';
    }

    private static function getAlunoByCpf($cpf){
        return EntityAluno::getAlunos('cpf = "'.addslashes($cpf).'"')->fetchObject(EntityAluno::class);
    }

    private static function formatDateInput($date){
        $timestamp = strlen((string)$date) ? strtotime($date) : false;

        return $timestamp ? date('Y-m-d', $timestamp) : '';
    }

    private static function normalizeDateInput($date, $fallback){
        $date = trim((string)$date);

        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)){
            return $date;
        }

        return $fallback;
    }

    private static function escape($value){
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
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

    private static function getAlunoDocumentoInfo($idAluno, $documento){
        if((int)$idAluno <= 0){
            return '';
        }

        $meta = self::getAlunoDocumentosMeta($idAluno);

        foreach(self::getAlunoDocumentoFileNames($documento) as $currentFileName){
            $path = self::getAlunoDocumentosDir($idAluno).'/'.$currentFileName;

            if(is_file($path)){
                $displayName = trim((string)($meta[$currentFileName]['originalName'] ?? '')) ?: $currentFileName;

                return '<a href="'.self::getAlunoDocumentoUrl($idAluno, $currentFileName).'" target="_blank" rel="noopener" class="student-name-link">'.self::escape($displayName).'</a>';
            }
        }

        return '';
    }

    private static function hasAlunoDocumento($idAluno, $documento){
        if((int)$idAluno <= 0){
            return false;
        }

        foreach(self::getAlunoDocumentoFileNames($documento) as $currentFileName){
            if(is_file(self::getAlunoDocumentosDir($idAluno).'/'.$currentFileName)){
                return true;
            }
        }

        return false;
    }

    private static function hasRequiredDocumentos($request, $idAluno){
        $fileVars = $request->getFileVars();
        $requiredFields = ['documentoIdentificacao', 'documentoResidencia'];

        foreach($requiredFields as $field){
            $file = $fileVars[$field] ?? null;
            $hasUpload = is_array($file) && (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

            if(!$hasUpload && !self::hasAlunoDocumento($idAluno, self::DOCUMENTOS_ALUNO[$field])){
                return false;
            }
        }

        return true;
    }

    private static function getAlunoDocumentosVars($idAluno){
        return [
            'documentoIdentificacaoInfo' => self::getAlunoDocumentoInfo($idAluno, self::DOCUMENTOS_ALUNO['documentoIdentificacao']),
            'documentoResidenciaInfo' => self::getAlunoDocumentoInfo($idAluno, self::DOCUMENTOS_ALUNO['documentoResidencia']),
        ];
    }

    private static function isPdfUpload($file){
        if(!is_array($file) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE){
            return true;
        }

        if((int)($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK){
            return false;
        }

        if((int)($file['size'] ?? 0) > self::getUploadMaxBytes()){
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

    private static function documentosAlunoUploadStatus($request){
        $fileVars = $request->getFileVars();

        foreach(self::DOCUMENTOS_ALUNO as $field => $documento){
            $file = $fileVars[$field] ?? null;

            if(!is_array($file)){
                continue;
            }

            $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);

            if($error === UPLOAD_ERR_OK || $error === UPLOAD_ERR_NO_FILE){
                if($error === UPLOAD_ERR_OK && (int)($file['size'] ?? 0) > self::getUploadMaxBytes()){
                    return 'documentoUploadLimit';
                }

                continue;
            }

            return self::getDocumentoUploadErrorStatus($error);
        }

        return '';
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

    private static function hasRequiredFields($postVars){
        $requiredFields = [
            'nome',
            'dataNasc',
            'sexo',
            'fone',
            'turma',
            'mae',
            'naturalidade',
            'escolaridade',
            'estadoCivil',
            'cep',
            'endereco',
            'numero',
            'bairro',
            'cidade',
            'uf',
        ];

        foreach($requiredFields as $field){
            if(trim((string)($postVars[$field] ?? '')) === ''){
                return false;
            }
        }

        return true;
    }

    private static function hasAlunoRequiredFields(EntityAluno $obAluno){
        $requiredFields = [
            'nome',
            'dataNasc',
            'sexo',
            'fone',
            'turma',
            'mae',
            'naturalidade',
            'escolaridade',
            'estadoCivil',
            'cep',
            'endereco',
            'numero',
            'bairro',
            'cidade',
            'uf',
        ];

        foreach($requiredFields as $field){
            if(trim((string)($obAluno->$field ?? '')) === ''){
                return false;
            }
        }

        return true;
    }

    private static function isPreCadastroCompleto(EntityAluno $obAluno){
        return self::hasAlunoRequiredFields($obAluno) &&
            !$obAluno->semFoto() &&
            self::hasAlunoDocumento($obAluno->id, self::DOCUMENTOS_ALUNO['documentoIdentificacao']) &&
            self::hasAlunoDocumento($obAluno->id, self::DOCUMENTOS_ALUNO['documentoResidencia']);
    }

    private static function isAlunoAtivo(EntityAluno $obAluno){
        return (int)$obAluno->status === self::STATUS_ALUNO_ATIVO;
    }

    private static function ativarAlunoSePreCadastroCompleto(EntityAluno $obAluno){
        if(self::isAlunoAtivo($obAluno) || !self::isPreCadastroCompleto($obAluno)){
            return false;
        }

        $obAluno->status = self::STATUS_ALUNO_ATIVO;

        return $obAluno->atualizar();
    }

    private static function getCarteiraRoute($idAluno){
        return '/precadastro/'.(int)$idAluno.'/carteira';
    }

    private static function renderCheck($request, $statusOverride = null, $cpfOverride = null){
        $queryParams = $request->getQueryParams();
        $cpf = $cpfOverride !== null ? (string)$cpfOverride : (string)($queryParams['cpf'] ?? '');
        $status = $statusOverride !== null ? (string)$statusOverride : (string)($queryParams['preStatus'] ?? '');

        if($status === 'cpfNotFound' && trim($cpf) === ''){
            $status = '';
        }

        return self::renderPage('Pré-cadastro do aluno', View::render('pages/precadastro/index', [
            'statusMessage' => self::getStatusMessage($status),
            'cpf' => htmlspecialchars($cpf, ENT_QUOTES, 'UTF-8'),
        ]));
    }

    private static function getSelfieFileName(EntityAluno $obAluno, $mime){
        $base = $obAluno->matricula ?: $obAluno->cpf ?: $obAluno->id;
        $base = preg_replace('/[^0-9A-Za-z\-]/', '', (string)$base);
        $extension = $mime === 'image/png' ? 'png' : 'jpg';

        return 'selfie_'.$base.'.'.$extension;
    }

    private static function salvarSelfieBinaria(EntityAluno $obAluno, $binary, $mime){
        if($binary === '' || strlen($binary) > self::SELFIE_MAX_BYTES){
            return false;
        }

        if(!@getimagesizefromstring($binary)){
            return false;
        }

        if(!in_array($mime, ['image/jpeg', 'image/png'], true)){
            return false;
        }

        $diretorioFotos = dirname(__DIR__).'/File/files/fotos/';
        if(!is_dir($diretorioFotos) && !@mkdir($diretorioFotos, 0775, true)){
            return false;
        }

        if(!is_writable($diretorioFotos)){
            return false;
        }

        $fileName = self::getSelfieFileName($obAluno, $mime);
        $filePath = $diretorioFotos.$fileName;

        if(@file_put_contents($filePath, $binary) === false){
            return false;
        }

        $img = new Resize();
        $img->initialize([
            'source_image' => $filePath,
            'width' => 160,
            'height' => 200,
        ]);

        if(!$img->crop()){
            @unlink($filePath);
            return false;
        }

        $obAluno->foto = $fileName;

        return true;
    }

    private static function salvarSelfie($request, EntityAluno $obAluno){
        $postVars = $request->getPostVars();
        $image = trim((string)($postVars['fotoSelfie'] ?? ''));

        if($image !== ''){
            if(!preg_match('/^data:image\/(jpe?g|png);base64,/', $image, $matches)){
                return false;
            }

            $binary = base64_decode(preg_replace('/^data:image\/(jpe?g|png);base64,/', '', $image), true);
            $mime = strtolower($matches[1]) === 'png' ? 'image/png' : 'image/jpeg';

            return $binary !== false && self::salvarSelfieBinaria($obAluno, $binary, $mime);
        }

        $files = $request->getFileVars();
        $file = $files['selfie'] ?? null;

        if(!is_array($file) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE){
            return null;
        }

        if((int)$file['error'] !== UPLOAD_ERR_OK || (int)$file['size'] > self::SELFIE_MAX_BYTES){
            return false;
        }

        $info = @getimagesize($file['tmp_name']);
        $mime = $info['mime'] ?? '';

        if(!in_array($mime, ['image/jpeg', 'image/png'], true)){
            return false;
        }

        $binary = file_get_contents($file['tmp_name']);

        return $binary !== false && self::salvarSelfieBinaria($obAluno, $binary, $mime);
    }

    private static function renderForm($request, EntityAluno $obAluno){
        $queryParams = $request->getQueryParams();
        $preStatus = $queryParams['preStatus'] ?? '';
        $foto = $obAluno->getFoto(false);
        $semFoto = $obAluno->semFoto();
        $sexo = (string)$obAluno->sexo;
        $cadastroCompleto = self::isPreCadastroCompleto($obAluno);
        $cadastroSalvo = $preStatus === 'saved' && $cadastroCompleto;

        return self::renderPage('Pré-cadastro do aluno', View::render('pages/precadastro/form', array_merge([
            'statusMessage' => self::getStatusMessage($preStatus),
            'id' => (int)$obAluno->id,
            'matricula' => htmlspecialchars((string)$obAluno->matricula, ENT_QUOTES, 'UTF-8'),
            'nome' => htmlspecialchars((string)$obAluno->nome, ENT_QUOTES, 'UTF-8'),
            'cep' => htmlspecialchars((string)$obAluno->cep, ENT_QUOTES, 'UTF-8'),
            'endereco' => htmlspecialchars((string)$obAluno->endereco, ENT_QUOTES, 'UTF-8'),
            'numero' => htmlspecialchars((string)$obAluno->numero, ENT_QUOTES, 'UTF-8'),
            'cidade' => htmlspecialchars((string)$obAluno->cidade, ENT_QUOTES, 'UTF-8'),
            'uf' => htmlspecialchars((string)$obAluno->uf, ENT_QUOTES, 'UTF-8'),
            'naturalidade' => htmlspecialchars((string)$obAluno->naturalidade, ENT_QUOTES, 'UTF-8'),
            'dataNasc' => self::formatDateInput($obAluno->dataNasc),
            'fone' => htmlspecialchars((string)$obAluno->fone, ENT_QUOTES, 'UTF-8'),
            'cpf' => htmlspecialchars((string)$obAluno->cpf, ENT_QUOTES, 'UTF-8'),
            'mae' => htmlspecialchars((string)$obAluno->mae, ENT_QUOTES, 'UTF-8'),
            'optionBairros' => self::getOptionalSelectOptions([EntityBairro::class, 'getSelectBairros'], $obAluno->bairro),
            'optionEscolaridade' => self::getOptionalSelectOptions([EntityEscolaridade::class, 'getSelectEscolaridade'], $obAluno->escolaridade),
            'optionEstadoCivil' => self::getOptionalSelectOptions([EntityEstadoCivil::class, 'getSelectEstadoCivil'], $obAluno->estadoCivil),
            'optionTurma' => EntityTurma::getSelectTurmas($obAluno->turma),
            'optionStatus' => EntityStatus::getSelectStatus($obAluno->status ?: 1),
            'selectedSexoM' => $sexo === 'MAS' ? 'selected' : '',
            'selectedSexoF' => $sexo === 'FEM' ? 'selected' : '',
            'foto' => $foto,
            'fotoObrigatoria' => $semFoto ? '1' : '0',
            'selfieStatus' => $semFoto ? 'Selfie pendente' : 'Selfie cadastrada',
            'selfieRequirement' => $semFoto ? '<p class="precadastro-selfie-alert">Foto obrigatória</p>' : '',
            'documentMaxSize' => self::getUploadMaxBytes(),
            'documentMaxSizeLabel' => self::formatBytes(self::getUploadMaxBytes()),
            'postMaxSize' => self::getPostMaxBytes(),
            'postMaxSizeLabel' => self::formatBytes(self::getPostMaxBytes()),
            'photoMaxSize' => self::SELFIE_MAX_BYTES,
            'photoMaxSizeLabel' => self::formatBytes(self::SELFIE_MAX_BYTES),
            'cadastroSalvo' => $cadastroSalvo ? '1' : '0',
            'cadastroCompleto' => $cadastroCompleto ? '1' : '0',
            'carteiraUrl' => URL.self::getCarteiraRoute($obAluno->id),
            'carteiraDisabled' => $cadastroSalvo ? '' : 'disabled',
        ], self::getAlunoDocumentosVars($obAluno->id))));
    }

    public static function getCarteiraAluno($request, $id){
        $obAluno = EntityAluno::getAlunoById($id);

        if(!$obAluno instanceof EntityAluno || !self::isPreCadastroCompleto($obAluno)){
            $request->getRouter()->redirect('/precadastro');
        }

        if(!self::isAlunoAtivo($obAluno)){
            $request->getRouter()->redirect('/precadastro?cpf='.rawurlencode((string)$obAluno->cpf).'&preStatus=inactiveComplete');
        }

        Funcoes::init();
        $sessionBackup = [
            'idAluno' => $_SESSION['idAluno'] ?? null,
            'naoCompleto' => $_SESSION['naoCompleto'] ?? null,
            'updated' => $_SESSION['updated'] ?? null,
        ];
        unset($_SESSION['idAluno'], $_SESSION['naoCompleto'], $_SESSION['updated']);

        $content = PainelAluno::getCarteiraAluno($request, $id);

        foreach($sessionBackup as $key => $value){
            if($value === null){
                unset($_SESSION[$key]);
                continue;
            }

            $_SESSION[$key] = $value;
        }

        return $content;
    }

    public static function setCarteiraAluno($request, $id){
        $obAluno = EntityAluno::getAlunoById($id);

        if(!$obAluno instanceof EntityAluno || !self::isPreCadastroCompleto($obAluno)){
            $request->getRouter()->redirect('/precadastro');
        }

        if(!self::isAlunoAtivo($obAluno)){
            $request->getRouter()->redirect('/precadastro?cpf='.rawurlencode((string)$obAluno->cpf).'&preStatus=inactiveComplete');
        }

        return PainelAluno::setCarteiraAluno($request, $id);
    }

    public static function getPreCadastro($request){
        $queryParams = $request->getQueryParams();
        $cpf = self::getCpfValido($queryParams['cpf'] ?? '');

        if($cpf === ''){
            return self::renderCheck($request);
        }

        $obAluno = self::getAlunoByCpf($cpf);

        if(!$obAluno instanceof EntityAluno){
            return self::renderCheck($request, 'cpfNotFound', $cpf);
        }

        if(self::isPreCadastroCompleto($obAluno) && !self::isAlunoAtivo($obAluno)){
            return self::renderCheck($request, 'inactiveComplete', $cpf);
        }

        if(($queryParams['preStatus'] ?? '') === '' && self::isPreCadastroCompleto($obAluno)){
            $request->getRouter()->redirect(self::getCarteiraRoute($obAluno->id));
        }

        return self::renderForm($request, $obAluno);
    }

    public static function setPreCadastro($request){
        $postVars = $request->getPostVars();
        $acao = $postVars['acao'] ?? 'buscar';
        $cpf = self::getCpfValido($postVars['cpf'] ?? '');

        if($cpf === ''){
            $request->getRouter()->redirect('/precadastro?preStatus=cpfInvalid');
        }

        $obAluno = self::getAlunoByCpf($cpf);

        if(!$obAluno instanceof EntityAluno){
            return self::renderCheck($request, 'cpfNotFound', $cpf);
        }

        if(self::isPreCadastroCompleto($obAluno) && !self::isAlunoAtivo($obAluno)){
            return self::renderCheck($request, 'inactiveComplete', $cpf);
        }

        if($acao !== 'salvar'){
            if(self::isPreCadastroCompleto($obAluno)){
                $request->getRouter()->redirect(self::getCarteiraRoute($obAluno->id));
            }

            $request->getRouter()->redirect('/precadastro?cpf='.$cpf);
        }

        if(!self::hasRequiredFields($postVars)){
            $request->getRouter()->redirect('/precadastro?cpf='.$cpf.'&preStatus=requiredFields');
        }

        $documentoUploadStatus = self::documentosAlunoUploadStatus($request);
        if($documentoUploadStatus !== ''){
            $request->getRouter()->redirect('/precadastro?cpf='.$cpf.'&preStatus='.$documentoUploadStatus);
        }

        if(!self::documentosAlunoValidos($request)){
            $request->getRouter()->redirect('/precadastro?cpf='.$cpf.'&preStatus=documentoInvalid');
        }

        if(!self::hasRequiredDocumentos($request, $obAluno->id)){
            $request->getRouter()->redirect('/precadastro?cpf='.$cpf.'&preStatus=documentosRequired');
        }

        $selfie = self::salvarSelfie($request, $obAluno);
        if($selfie === false){
            $request->getRouter()->redirect('/precadastro?cpf='.$cpf.'&preStatus=fotoInvalid');
        }

        if($selfie === null && $obAluno->semFoto()){
            $request->getRouter()->redirect('/precadastro?cpf='.$cpf.'&preStatus=selfieRequired');
        }

        $auditBefore = AuditLogger::snapshot($obAluno, self::AUDIT_ALUNO_FIELDS);

        $obAluno->nome = Funcoes::convertePriMaiuscula($postVars['nome'] ?? $obAluno->nome);
        $obAluno->cep = $postVars['cep'] ?? $obAluno->cep;
        $obAluno->endereco = Funcoes::convertePriMaiuscula($postVars['endereco'] ?? $obAluno->endereco);
        $obAluno->numero = $postVars['numero'] ?? $obAluno->numero;
        $obAluno->bairro = $postVars['bairro'] ?? $obAluno->bairro;
        $obAluno->cidade = Funcoes::convertePriMaiuscula($postVars['cidade'] ?? $obAluno->cidade);
        $obAluno->uf = Funcoes::convertePriMaiuscula($postVars['uf'] ?? $obAluno->uf);
        $obAluno->naturalidade = Funcoes::convertePriMaiuscula($postVars['naturalidade'] ?? $obAluno->naturalidade);
        $obAluno->escolaridade = $postVars['escolaridade'] ?? $obAluno->escolaridade;
        $obAluno->estadoCivil = $postVars['estadoCivil'] ?? $obAluno->estadoCivil;
        $obAluno->sexo = $postVars['sexo'] ?? $obAluno->sexo;
        $obAluno->dataNasc = self::normalizeDateInput($postVars['dataNasc'] ?? '', $obAluno->dataNasc);
        $obAluno->fone = preg_replace('/\D+/', '', $postVars['fone'] ?? '') ?: $obAluno->fone;
        $obAluno->mae = Funcoes::convertePriMaiuscula($postVars['mae'] ?? $obAluno->mae);
        $obAluno->turma = $postVars['turma'] ?? $obAluno->turma;
        $obAluno->cpf = $cpf;

        $obAluno->atualizar();

        if(!self::salvarDocumentosAluno($request, $obAluno->id)){
            $request->getRouter()->redirect('/precadastro?cpf='.$cpf.'&preStatus=documentoSaveError');
        }

        self::ativarAlunoSePreCadastroCompleto($obAluno);

        $obAlunoAtualizado = EntityAluno::getAlunoById($obAluno->id);
        AuditLogger::record(
            $request,
            'atualizar',
            'precadastro',
            'Aluno',
            $obAluno->id,
            'Pré-cadastro salvo pelo aluno: '.$obAluno->nome,
            $auditBefore,
            AuditLogger::snapshot($obAlunoAtualizado, self::AUDIT_ALUNO_FIELDS)
        );

        $request->getRouter()->redirect('/precadastro?cpf='.$cpf.'&preStatus=saved');
    }
}
