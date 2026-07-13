<?php

require_once __DIR__.'/../../../includes/app.php';

use App\Model\Entity\Aluno as EntityAluno;
use App\Model\Entity\Aula as EntityAula;
use App\Model\Entity\Turma as EntityTurma;
use App\Model\Entity\Configuracao as EntityConfiguracao;
use App\Model\Entity\Frequencia as EntityFrequencia;
use App\Model\Entity\InativacaoAluno as EntityInativacaoAluno;
use App\Service\FaceComparison;
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

function getDiaSemanaAtualFrequencia(){
    $dias = ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SÁB'];

    return $dias[(int)date('w')] ?? '';
}

function getTituloAulaFrequencia(EntityAula $obAula){
    $obTurma = EntityTurma::getTurmaById((int)$obAula->turma);
    $turma = $obTurma instanceof EntityTurma ? $obTurma->nome : 'Sem turma';

    return 'Aula do dia: '.date('d/m/Y', strtotime($obAula->data)).' ( '.$obAula->diaSemana.' ) '.$turma;
}

function getIdRegistroNaoInformado($table){
    $nomes = ['Não Informado', 'Não Informados', 'Nao Informado', 'Nao Informados'];
    $condicoes = [];

    foreach ($nomes as $nome) {
        $condicoes[] = 'nome = "'.addslashes($nome).'"';
    }

    $obRegistro = (new Database($table))
        ->select('('.implode(' OR ', $condicoes).')', 'id ASC', '1', 'id')
        ->fetchObject();

    return $obRegistro ? (int)$obRegistro->id : 0;
}

function getAulaGenericaExistente($data, $turma){
    return EntityAula::getAulas(
        'DATE(data) = "'.addslashes($data).'" AND turma = '.(int)$turma,
        'id DESC',
        '1'
    )->fetchObject(EntityAula::class);
}

function prepararAulaGenerica(EntityAluno $obAluno, $autor){
    $data = date('Y-m-d');
    $turma = EntityConfiguracao::getTurmaFrequenciaGeralAtual((int)$obAluno->turma);

    EntityAula::fecharAulasAbertasAnteriores($data);

    $obAula = getAulaGenericaExistente($data, $turma);

    if($obAula instanceof EntityAula){
        EntityFrequencia::cadastrarFaltasDaAula($obAula->id, $obAula->turma, $autor);
        return $obAula;
    }

    $database = new Database('aulas');
    $database->beginTransaction();

    try{
        $obAula = new EntityAula();
        $obAula->data = $data;
        $obAula->turma = $turma;
        $obAula->professor1 = getIdRegistroNaoInformado('professores');
        $obAula->professor2 = $obAula->professor1;
        $obAula->disciplina1 = getIdRegistroNaoInformado('disciplinas');
        $obAula->disciplina2 = $obAula->disciplina1;
        $obAula->obs = 'Aula criada automaticamente pela leitura de frequência geral.';
        $obAula->status = EntityAula::STATUS_ABERTA;
        $obAula->diaSemana = getDiaSemanaAtualFrequencia();
        $obAula->cadastrar($database);

        EntityFrequencia::cadastrarFaltasDaAula($obAula->id, $obAula->turma, $autor, $database);

        $database->commit();
    }catch(Throwable $e){
        $database->rollBack();
        throw $e;
    }

    return $obAula;
}

function preencherDadosAulaResposta(array &$response, EntityAula $obAula){
    $response['idAula'] = (int)$obAula->id;
    $response['aulaTitulo'] = getTituloAulaFrequencia($obAula);
}

function aulaEhDeHoje(EntityAula $obAula){
    return date('Y-m-d', strtotime($obAula->data)) === date('Y-m-d');
}

function reativarAlunoSeNecessario(EntityAluno $obAluno){
    if((string)$obAluno->status === '1'){
        return false;
    }

    $obAluno->status = 1;
    $obAluno->atualizar();

    return true;
}

$matricula = trim((string)($_POST['matricula'] ?? ''));
$matricula = preg_replace('/[^0-9A-Za-z\-]/', '', $matricula);
$idAula = (int)($_POST['idAula'] ?? 0);
$send = filter_var($_POST['send'] ?? false, FILTER_VALIDATE_BOOLEAN);
$modo = (string)($_POST['modo'] ?? 'registrar');
$aulaGenerica = filter_var($_POST['aulaGenerica'] ?? false, FILTER_VALIDATE_BOOLEAN);
$fotoAuditoriaRequest = $_POST['fotoAuditoria'] ?? '';

$response = [
    'successExiste' => false,
    'successAtivo' => false,
    'successVinculo' => false,
    'successPresenca' => false,
    'successUpdate' => false,
    'successInsert' => false,
    'mensagem' => 'Código inválido, tente novamente!',
    'idAula' => $idAula,
    'aulaTitulo' => '',
    'aulaGenerica' => $aulaGenerica,
];

if(!$send || $matricula === '' || (!$aulaGenerica && $idAula <= 0)){
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

EntityInativacaoAluno::aplicarCriteriosAutomaticos();

$obAluno = EntityAluno::getAlunoByMatricula($matricula);
if(!$obAluno instanceof EntityAluno){
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$autor = (int)($_SESSION['usuario']['id'] ?? 0);
$alunoEstavaInativo = (string)$obAluno->status !== '1';
$response = array_merge($response, [
    'successExiste' => true,
    'successAtivo' => true,
    'alunoEstavaInativo' => $alunoEstavaInativo,
    'alunoReativado' => false,
    'matricula' => $obAluno->matricula,
    'nome' => $obAluno->nome,
    'foto' => $obAluno->getFoto(false),
]);

$idAluno = (int)$obAluno->id;
$obAula = null;

if($modo === 'registrar'){
    $response['alunoReativado'] = reativarAlunoSeNecessario($obAluno);
}

if($idAula > 0){
    $obAulaInformada = EntityAula::getAulaById($idAula);

    if(!$obAulaInformada instanceof EntityAula){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if($aulaGenerica){
        $turmaAulaGeralAtual = EntityConfiguracao::getTurmaFrequenciaGeralAtual((int)$obAluno->turma);

        if(!aulaEhDeHoje($obAulaInformada) || (int)$obAulaInformada->turma !== (int)$turmaAulaGeralAtual){
            $idAula = 0;
            $response['idAula'] = 0;
            $response['aulaTitulo'] = 'Frequência Geral';
            $response['aulaGenerica'] = true;
        }else{
            $obAula = $obAulaInformada;
            preencherDadosAulaResposta($response, $obAula);
        }
    }elseif(!aulaEhDeHoje($obAulaInformada)){
        $aulaGenerica = true;
        $idAula = 0;
        $response['idAula'] = 0;
        $response['aulaTitulo'] = 'Frequência Geral';
        $response['aulaGenerica'] = true;
    }else{
        $obAula = $obAulaInformada;
        preencherDadosAulaResposta($response, $obAula);
    }
}

if($aulaGenerica && $idAula <= 0){
    $response['aulaGenerica'] = true;
    $turmaAulaGeral = EntityConfiguracao::getTurmaFrequenciaGeralAtual((int)$obAluno->turma);
    $obAulaExistente = getAulaGenericaExistente(date('Y-m-d'), $turmaAulaGeral);

    if($obAulaExistente instanceof EntityAula){
        preencherDadosAulaResposta($response, $obAulaExistente);

        $obFrequenciaExistente = EntityFrequencia::getFrequencias(
            'idAula = '.$obAulaExistente->id.' AND idAluno = '.$idAluno
        )->fetchObject(EntityFrequencia::class);

        if($obFrequenciaExistente instanceof EntityFrequencia && $obFrequenciaExistente->status === 'P'){
            $response['successVinculo'] = true;
            $response['successPresenca'] = true;
            $response['mensagem'] = 'Presença Já Registrada!';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    if($modo === 'consultar'){
        if($response['aulaTitulo'] === ''){
            $response['aulaTitulo'] = 'Frequência Geral';
        }
        $response['mensagem'] = 'Confirme para registrar a presença.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $obAula = prepararAulaGenerica($obAluno, $autor);
    $idAula = (int)$obAula->id;
    preencherDadosAulaResposta($response, $obAula);
}else{
    $obAula = $obAula instanceof EntityAula ? $obAula : EntityAula::getAulaById($idAula);

    if(!$obAula instanceof EntityAula){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if($response['aulaTitulo'] === ''){
        preencherDadosAulaResposta($response, $obAula);
    }
}

$obFrequencia = EntityFrequencia::getFrequencias(
    'idAula = '.$idAula.' AND idAluno = '.$idAluno
)->fetchObject(EntityFrequencia::class);

if(!$obFrequencia instanceof EntityFrequencia && $modo === 'registrar'){
    $obFrequencia = EntityFrequencia::garantirVinculoAlunoAula($idAula, $idAluno, $autor);
}

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
    $comparacaoFacial = FaceComparison::comparar($fotoAuditoria, $obAluno);

    $response['successUpdate'] = $obFrequencia->registrarPresenca($autor, $fotoAuditoria, $comparacaoFacial);
    $response['mensagem'] = $response['successUpdate'] ? 'Presença Registrada!' : 'Erro Interno! Tente novamente.';
    $response['fotoAuditoria'] = $fotoAuditoria;
    $response['comparacaoFacial'] = $comparacaoFacial;

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

if($modo === 'consultar'){
    $response['successVinculo'] = true;
    $response['mensagem'] = 'Confirme para registrar a presença.';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$response['mensagem'] = 'Erro Interno! Tente novamente.';

echo json_encode($response, JSON_UNESCAPED_UNICODE);
