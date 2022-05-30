<?php

namespace App\Controller\Pages;

use \App\Utils\View;
use App\Controller\Admin;
use \App\Model\Entity\Aluno as EntityAluno;

class Home extends Page{
	
	/*
	//Método responsável por retornar os atendimentos do relatório da RAAS
	public static function getProducaoPorAtendimento($dataInicio,$dataFim){
		
		$resultado = '';
		
		
		$where = 'status = "P" and data BETWEEN "'.$dataInicio.'" and "'.$dataFim.'" GROUP BY P.nome' ;
		$table = 'atendimentos as A INNER JOIN procedimentos as P ON P.id = A.idProcedimento';
		$fields = 'P.nome as atendimento, COUNT(*) as total';
		$order = ' P.nome';
		
		$dadosAtendimentos = EntityPaciente::getPacientesRel($where,$order,null,$fields,$table);
	
		while ($obAtendimento = $dadosAtendimentos -> fetchObject(EntityPaciente::class)) {
			
			$resultado .=  "['$obAtendimento->atendimento', $obAtendimento->total], "  ;
		}
		
		return $resultado;
		
	}
	
	*/
	
	
	
	
	
	
	//retorna o conteudo (view) da nossa home
	public static function getHome(){
		$mes_extenso = array(
				'January' => 'Janeiro',
				'February' => 'Fevereiro',
				'March' => 'Marco',
				'April' => 'Abril',
				'May' => 'Maio',
				'June' => 'Junho',
				'July' => 'Julho',
				'August' => 'Agosto',
				'November' => 'Novembro',
				'September' => 'Setembro',
				'October' => 'Outubro',
				'December' => 'Dezembro'
		);

		$mes = $mes_extenso[date('F',strtotime("-1 month"))] ;
		$P_Dia = date('Y-m-01',strtotime("-1 month"));
		$U_Dia = date('Y-m-t',strtotime("-1 month"));
		
		$totalAlunos = EntityAluno::getAlunos(null, 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
		$totalAtivos = EntityAluno::getAlunos('status = 1', 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
		$totalInativos = EntityAluno::getAlunos('status = 2', 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
		$totalManha = EntityAluno::getAlunos('turma = 1', 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
		$totalNoite = EntityAluno::getAlunos('turma = 3', 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
		
		$content = View::render('pages/home',[
				
				'totalAlunos' => $totalAlunos,
				'totalAtivos' => $totalAtivos,
				'totalInativos' => $totalInativos,
		        'totalManha' => $totalManha,
		        'totalNoite' => $totalNoite
		]);
		
		return parent::getPage('Siscaps', $content);
		
	}
	
}