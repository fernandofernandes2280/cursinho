<?php

require_once __DIR__.'/../../../includes/app.php';

use App\Model\Entity\Aluno as EntityAluno;
use App\Model\Entity\Aula as EntityAula;
use App\Model\Entity\Frequencia as EntityFrequencia;
use WilliamCosta\DatabaseManager\Database;

header('Content-Type: application/json; charset=utf-8');

$matricula = trim((string)($_POST['matricula'] ?? ''));
$matricula = preg_replace('/[^0-9A-Za-z\-]/', '', $matricula);
$idAula = (int)($_POST['idAula'] ?? 0);
$send = filter_var($_POST['send'] ?? false, FILTER_VALIDATE_BOOLEAN);

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

    $obFrequencia->status = 'P';
    $obFrequencia->autor = $autor;
    $response['successUpdate'] = $obFrequencia->atualizar();
    $response['mensagem'] = $response['successUpdate'] ? 'Presença Registrada!' : 'Erro Interno! Tente novamente.';

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$response['successInsert'] = (bool)(new Database('frequencia'))->insert([
    'idAula' => $idAula,
    'idAluno' => $idAluno,
    'dataReg' => date('Y-m-d H:i:s'),
    'status' => 'P',
    'autor' => $autor,
]);
$response['mensagem'] = $response['successInsert'] ? 'Presença Registrada!' : 'Erro Interno! Tente novamente.';

echo json_encode($response, JSON_UNESCAPED_UNICODE);
