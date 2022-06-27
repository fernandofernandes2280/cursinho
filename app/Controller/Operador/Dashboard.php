<?php

namespace App\Controller\Operador;

use \App\Utils\View;
use \App\Model\Entity\Aluno as EntityAluno;
use \App\Model\Entity\Aula as EntityAula;


class Dashboard extends Page{
    
    //esconde busca rápida de prontuário no navBar
    private static $hidden = 'hidden';
    
    //retorna o conteudo (view) DO DASHBOOARD
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
        $dia = date('d');
        
        //EXECUTA O PROCESSO DE INATIVAR TODO DIA 1
        
        if($dia == 1){
            
            $menos15dias = date('Y-m-d',strtotime("-30 day"));
            $dataAtual = date('Y-m-d');
            
            $where = ' F.status = "F" AND A.data BETWEEN "'.$menos15dias.'" AND "'.$dataAtual.'" GROUP BY F.idAluno ';
            $order = 'F.idAluno';
            $fields = 'F.idAluno As idAluno, COUNT(F.idAluno) AS qtd';
            $table = 'aulas AS A INNER JOIN frequencia AS F ON A.id = F.idAula';
            $results = EntityAula::getAulasInativaAluno($where,$order,null,$fields,$table);
            
            
            while ($obInativo = $results -> fetchObject(EntityAula::class)) {
                
                if($obInativo->qtd > 2){ //INATIVA COM MAIS DE DUAS FALTAS NO INTERVALO DE DIAS
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
        $totalManhaAtivos = EntityAluno::getAlunos('turma = 1 AND status = 1', 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
        $totalManhaInativos = EntityAluno::getAlunos('turma = 1 AND status = 2', 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
        $totalNoite = EntityAluno::getAlunos('turma = 3', 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
        $totalNoiteAtivos = EntityAluno::getAlunos('turma = 3 AND status = 1', 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
        $totalNoiteInativos = EntityAluno::getAlunos('turma = 3 AND status = 2', 'id DESC',null,'COUNT(*) as qtd')->fetchObject()->qtd;
        $content = View::render('pages/dashboard',[
            
            'totalAlunos' => $totalAlunos,
            'totalAtivos' => $totalAtivos,
            'totalInativos' => $totalInativos,
            'totalManha' => $totalManha,
            'totalManhaAtivos' => $totalManhaAtivos,
            'totalManhaInativos' => $totalManhaInativos,
            'totalNoite' => $totalNoite,
            'totalNoiteAtivos' => $totalNoiteAtivos,
            'totalNoiteInativos' => $totalNoiteInativos,
        ]);
        
        return parent::getPanelDashboard('Dashboard > Cursinho', $content,'dashboard', self::$hidden);
        
    }
    
}