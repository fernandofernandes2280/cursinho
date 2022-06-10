<?php

namespace App\Controller\Admin;

use \App\Utils\View;
use App\Controller\Admin;
use \App\Model\Entity\Aluno as EntityAluno;
use \App\Model\Entity\Frequencia as EntityFrequencia;
use \App\Model\Entity\Aula as EntityAula;
use App\Controller\Admin\Page;


class Dashboard extends Page{
    
    //esconde busca rápida de prontuário no navBar
    private static $hidden = 'hidden';
    
    //retorna o conteudo (view) da nossa home
    public static function getDashboard(){
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
        
        //INATIVA ALUNOS
        
        $menos15dias = date('Y-m-d',strtotime("-15 day"));
        $dataAtual = date('Y-m-d');
        
        $where = ' F.status = "F" AND A.data BETWEEN "'.$menos15dias.'" AND "'.$dataAtual.'" GROUP BY F.idAluno ';
        $order = 'F.idAluno';
        $fields = 'F.idAluno As idAluno, COUNT(F.idAluno) AS qtd';
        $table = 'aulas AS A INNER JOIN frequencia AS F ON A.id = F.idAula';
        $results = EntityAula::getAulasInativaAluno($where,$order,null,$fields,$table);
           
        $dia = date('d');
        
        //EXECUTA O PROCESSO DE INATIVAR TODO DIA 1 E 15 DO MES
        if($dia == 1 || $dia == 15){
                
            while ($obInativo = $results -> fetchObject(EntityAula::class)) {
                
                if($obInativo->qtd == 3){ //INATIVA APENAS QUEM TEM TRÊS FALTAS NO INTERVALO DE DIAS
                    $obAluno = EntityAluno::getAlunoById($obInativo->idAluno);
                    $obAluno ->status = 2; //status INATIVO
                    $obAluno -> atualizar();
                }
            }
        
        }
        
        
      //  $mes = $mes_extenso[date('F',strtotime("-1 month"))] ;
      //  $P_Dia = date('Y-m-01',strtotime("-1 month"));
       // $U_Dia = date('Y-m-t',strtotime("-1 month"));
       
        $totalAlunos = EntityAluno::getAlunos(null, 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
        $totalAtivos = EntityAluno::getAlunos('status = 1', 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
        $totalInativos = EntityAluno::getAlunos('status = 2', 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
        $totalManha = EntityAluno::getAlunos('turma = 1', 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
        $totalNoite = EntityAluno::getAlunos('turma = 3', 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
        
        $content = View::render('pages/dashboard',[
            
            'totalAlunos' => $totalAlunos,
            'totalAtivos' => $totalAtivos,
            'totalInativos' => $totalInativos,
            'totalManha' => $totalManha,
            'totalNoite' => $totalNoite
        ]);
        
        return parent::getPanelDashboard('Dashboard > Cursinho', $content,'dashboard', self::$hidden);
        
    }
    
}