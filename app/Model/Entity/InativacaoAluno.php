<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;
use App\Service\AuditLogger;

class InativacaoAluno {

	public static function aplicarCriteriosAutomaticos($forcar = false, $dataReferencia = null){
		$dataReferencia = $dataReferencia ?: date('Y-m-d');
		$hoje = date('Y-m-d', strtotime($dataReferencia));

		if(!$forcar && Configuracao::getInativacaoUltimaExecucao() === $hoje){
			return [
				'executado' => false,
				'inativados' => 0,
				'motivo' => 'Rotina já executada hoje.'
			];
		}

		$inicioMes = date('Y-m-01', strtotime($hoje));
		$dataFinal = date('Y-m-d', strtotime($hoje.' -1 day'));
		$faltasIntercaladas = Configuracao::getInativacaoFaltasIntercaladasMes();
		$faltasSeguidas = Configuracao::getInativacaoFaltasSeguidasMes();

		if(strtotime($dataFinal) < strtotime($inicioMes)){
			Configuracao::setInativacaoUltimaExecucao($hoje);
			return [
				'executado' => true,
				'inativados' => 0,
				'motivo' => 'Não há aulas anteriores no mês atual.'
			];
		}

		$results = (new Database())->execute(
			'SELECT F.idAluno, F.status, DATE(A.data) AS dataAula, A.id AS idAula
			   FROM frequencia AS F
			   INNER JOIN aulas AS A ON A.id = F.idAula
			   INNER JOIN alunos AS AL ON AL.id = F.idAluno
			  WHERE AL.status = 1
			    AND DATE(A.data) BETWEEN ? AND ?
			    AND COALESCE(A.status, 0) <> 3
			  ORDER BY F.idAluno ASC, DATE(A.data) ASC, A.id ASC',
			[$inicioMes, $dataFinal]
		);

		$alunos = [];

		while($row = $results->fetchObject()){
			$idAluno = (int)$row->idAluno;

			if(!isset($alunos[$idAluno])){
				$alunos[$idAluno] = [
					'totalFaltas' => 0,
					'faltasSeguidas' => 0,
					'atingiuIntercaladas' => false,
					'atingiuSeguidas' => false
				];
			}

			if((string)$row->status === 'F'){
				$alunos[$idAluno]['totalFaltas']++;
				$alunos[$idAluno]['faltasSeguidas']++;
				$alunos[$idAluno]['atingiuIntercaladas'] = $alunos[$idAluno]['totalFaltas'] >= $faltasIntercaladas;
				$alunos[$idAluno]['atingiuSeguidas'] = $alunos[$idAluno]['faltasSeguidas'] >= $faltasSeguidas;
				continue;
			}

			if($alunos[$idAluno]['atingiuIntercaladas'] || $alunos[$idAluno]['atingiuSeguidas']){
				$alunos[$idAluno]['totalFaltas'] = 0;
				$alunos[$idAluno]['atingiuIntercaladas'] = false;
				$alunos[$idAluno]['atingiuSeguidas'] = false;
			}

			$alunos[$idAluno]['faltasSeguidas'] = 0;
		}

		$databaseAlunos = new Database('alunos');
		$totalInativados = 0;

		foreach($alunos as $idAluno => $resumo){
			if(!$resumo['atingiuIntercaladas'] && !$resumo['atingiuSeguidas']){
				continue;
			}

			$databaseAlunos->update('id = '.(int)$idAluno.' AND status = 1', [
				'status' => 2
			]);
			AuditLogger::record(
				null,
				'inativar_automatico',
				'inativar',
				'Aluno',
				$idAluno,
				'Aluno inativado automaticamente por faltas.',
				[
					'id' => (int)$idAluno,
					'status' => 1,
				],
				[
					'id' => (int)$idAluno,
					'status' => 2,
					'inicio' => $inicioMes,
					'fim' => $dataFinal,
					'faltasIntercaladas' => $faltasIntercaladas,
					'faltasSeguidas' => $faltasSeguidas,
					'totalFaltas' => $resumo['totalFaltas'],
				]
			);
			$totalInativados++;
		}

		Configuracao::setInativacaoUltimaExecucao($hoje);

		return [
			'executado' => true,
			'inativados' => $totalInativados,
			'faltasIntercaladas' => $faltasIntercaladas,
			'faltasSeguidas' => $faltasSeguidas,
			'inicio' => $inicioMes,
			'fim' => $dataFinal
		];
	}
}
