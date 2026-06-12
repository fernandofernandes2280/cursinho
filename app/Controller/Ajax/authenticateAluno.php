<?php

require_once __DIR__.'/../../../includes/app.php';

use App\Model\Entity\Aluno as EntityAluno;
use App\Model\Entity\Aula as EntityAula;
use App\Model\Entity\Frequencia as EntityFrequencia;
use WilliamCosta\DatabaseManager\Database;

header('Content-Type: application/json; charset=utf-8');

function salvarFotoAuditoriaPresenca($image, $idAula, $idAluno, $matricula){
    $image = trim((string)$image);

    if($image === '' || !preg_match('/^data:image\/(jpe?g|png);base64,/', $image, $matches)){
        return null;
    }

    $base64 = preg_replace('/^data:image\/(jpe?g|png);base64,/', '', $image);
    $binary = base64_decode($base64, true);

    if($binary === false || strlen($binary) < 100 || strlen($binary) > 3 * 1024 * 1024){
        return null;
    }

    if(!@getimagesizefromstring($binary)){
        return null;
    }

    $extension = strtolower($matches[1]) === 'png' ? 'png' : 'jpg';
    $matricula = preg_replace('/[^0-9A-Za-z\-]/', '', (string)$matricula);
    $folderPath = dirname(__DIR__).'/File/files/frequencias-auditoria/';

    if(!is_dir($folderPath)){
        mkdir($folderPath, 0775, true);
    }

    $fileName = 'aula'.$idAula.'_aluno'.$idAluno.'_'.$matricula.'_'.date('Ymd_His').'_'.bin2hex(random_bytes(4)).'.'.$extension;
    $filePath = $folderPath.$fileName;

    return file_put_contents($filePath, $binary) !== false ? $fileName : null;
}

$matricula = trim((string)($_POST['matricula'] ?? ''));
$matricula = preg_replace('/[^0-9A-Za-z\-]/', '', $matricula);
$idAula = (int)($_POST['idAula'] ?? 0);
$send = filter_var($_POST['send'] ?? false, FILTER_VALIDATE_BOOLEAN);
$modo = (string)($_POST['modo'] ?? 'registrar');
$fotoAuditoriaRequest = $_POST['fotoAuditoria'] ?? '';

$response = [
    'successExiste' => false,
    'successAtivo' => false,
    'successVinculo' => false,
    'successPresenca' => false,
    'successUpdate' => false,
    'successInsert' => false,
    'mensagem' => 'Código inválido, tente novamente!',
];

if(!$send || $matricula === '' || $idAula <= 0){
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$obAula = EntityAula::getAulaById($idAula);
if(!$obAula instanceof EntityAula){
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$obAluno = EntityAluno::getAlunoByMatricula($matricula);
if(!$obAluno instanceof EntityAluno){
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$autor = (int)($_SESSION['usuario']['id'] ?? 0);
$response = array_merge($response, [
    'successExiste' => true,
    'successAtivo' => (string)$obAluno->status === '1',
    'matricula' => $obAluno->matricula,
    'nome' => $obAluno->nome,
    'foto' => $obAluno->getFoto(false),
]);

if(!$response['successAtivo']){
    $response['mensagem'] = 'Aluno inativo! Presença não confirmada.';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$idAluno = (int)$obAluno->id;
$obFrequencia = EntityFrequencia::getFrequencias(
    'idAula = '.$idAula.' AND idAluno = '.$idAluno
)->fetchObject(EntityFrequencia::class);

if($obFrequencia instanceof EntityFrequencia){
    $response['successVinculo'] = true;

    if($obFrequencia->status === 'P'){
        $response['successPresenca'] = true;
        $response['mensagem'] = 'Presença Já Registrada!';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if($modo === 'consultar'){
        $response['mensagem'] = 'Confirme para registrar a presença.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $fotoAuditoria = salvarFotoAuditoriaPresenca($fotoAuditoriaRequest, $idAula, $idAluno, $obAluno->matricula);

    $response['successUpdate'] = $obFrequencia->registrarPresenca($autor, $fotoAuditoria);
    $response['mensagem'] = $response['successUpdate'] ? 'Presença Registrada!' : 'Erro Interno! Tente novamente.';
    $response['fotoAuditoria'] = $fotoAuditoria;

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

if($modo === 'consultar'){
    $response['mensagem'] = 'Confirme para registrar a presença.';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$fotoAuditoria = salvarFotoAuditoriaPresenca($fotoAuditoriaRequest, $idAula, $idAluno, $obAluno->matricula);
$response['successInsert'] = (bool)(new Database('frequencia'))->insert([
    'idAula' => $idAula,
    'idAluno' => $idAluno,
    'dataReg' => date('Y-m-d H:i:s'),
    'status' => 'P',
    'autor' => $autor,
    'fotoAuditoria' => $fotoAuditoria,
    'dataAuditoria' => $fotoAuditoria ? date('Y-m-d H:i:s') : null,
]);
$response['mensagem'] = $response['successInsert'] ? 'Presença Registrada!' : 'Erro Interno! Tente novamente.';
$response['fotoAuditoria'] = $fotoAuditoria;

echo json_encode($response, JSON_UNESCAPED_UNICODE);
