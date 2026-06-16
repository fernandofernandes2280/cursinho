<?php

namespace App\Controller\Painel;

use \App\Utils\View;
use \App\Model\Entity\Aluno as EntityAluno;
use \App\Model\Entity\Aula as EntityAula;
use \App\Model\Entity\Disciplina as EntityDisciplina;
use \App\Model\Entity\DisciplinaProfessor as EntityDisciplinaProfessor;
use \App\Model\Entity\Frequencia as EntityFrequencia;
use \App\Model\Entity\InativacaoAluno as EntityInativacaoAluno;
use \App\Model\Entity\Professor as EntityProfessor;
use \App\Model\Entity\Turma as EntityTurma;
use \App\Utils\Funcoes;



class Dashboard extends Page{
    
    //esconde busca rápida de prontuário no navBar
    private static $hidden = 'hidden';

    private static function getQuantidade($result){
        $obResult = $result ? $result->fetchObject() : null;

        return (int)($obResult->qtd ?? 0);
    }

    private static function getUltimasAulas(){
        $resultados = '';
        $results = EntityAula::getAulas(null, 'data DESC, id DESC', 5);

        while($obAula = $results->fetchObject(EntityAula::class)){
            $obStatus = (int)$obAula->status > 0 ? EntityAula::getStatusAulaById((int)$obAula->status) : null;
            $obTurma = (int)$obAula->turma > 0 ? EntityTurma::getTurmaById((int)$obAula->turma) : null;
            $obProfessor1 = (int)$obAula->professor1 > 0 ? EntityProfessor::getProfessorById((int)$obAula->professor1) : null;
            $obDisciplina1 = (int)$obAula->disciplina1 > 0 ? EntityDisciplina::getDisciplinaById((int)$obAula->disciplina1) : null;
            $obProfessor2 = (int)$obAula->professor2 > 0 ? EntityProfessor::getProfessorById((int)$obAula->professor2) : null;
            $obDisciplina2 = (int)$obAula->disciplina2 > 0 ? EntityDisciplina::getDisciplinaById((int)$obAula->disciplina2) : null;
            $totalPresencas = self::getQuantidade(EntityFrequencia::getFrequencias('idAula = '.$obAula->id.' AND status = "P"', null, null, 'COUNT(*) as qtd'));
            $totalFaltas = self::getQuantidade(EntityFrequencia::getFrequencias('idAula = '.$obAula->id.' AND status = "F"', null, null, 'COUNT(*) as qtd'));
            $complemento = '';

            if($obProfessor2 || $obDisciplina2){
                $complemento = '<span>'.($obProfessor2 ? $obProfessor2->nome : 'Sem professor').' / '.($obDisciplina2 ? $obDisciplina2->nome : 'Sem disciplina').'</span>';
            }

            $resultados .= View::render('pages/dashboard/ultimaAula',[
                'id' => $obAula->id,
                'data' => date('d/m/Y', strtotime($obAula->data)),
                'diaSemana' => $obAula->diaSemana,
                'turma' => $obTurma ? $obTurma->nome : 'Sem turma',
                'professorDisciplina' => ($obProfessor1 ? $obProfessor1->nome : 'Sem professor').' / '.($obDisciplina1 ? $obDisciplina1->nome : 'Sem disciplina'),
                'complemento' => $complemento,
                'presencas' => $totalPresencas,
                'faltas' => $totalFaltas,
                'status' => $obStatus ? $obStatus->nome : 'Sem status',
                'statusClasse' => self::getStatusAulaClasse((int)$obAula->status)
            ]);
        }

        if($resultados !== ''){
            return $resultados;
        }

        return '<tr><td colspan="7" class="dashboard-report-empty">Nenhuma aula cadastrada.</td></tr>';
    }

    private static function getStatusAulaClasse($status){
        if($status === 1) return 'is-open';
        if($status === 2) return 'is-closed';
        if($status === 3) return 'is-canceled';

        return '';
    }
    
    //retorna o conteudo (view) DO DASHBOOARD
    public static function getDashboard($request = null){
        EntityInativacaoAluno::aplicarCriteriosAutomaticos();

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
        

        
        
      //  $mes = $mes_extenso[date('F',strtotime("-1 month"))] ;
      //  $P_Dia = date('Y-m-01',strtotime("-1 month"));
       // $U_Dia = date('Y-m-t',strtotime("-1 month"));
       
        $totalAlunos = self::getQuantidade(EntityAluno::getAlunos(null, 'id DESC',null,'COUNT(*) as qtd'));
        $totalAtivos = self::getQuantidade(EntityAluno::getAlunos('status = 1', 'id DESC',null,'COUNT(*) as qtd'));
        $totalInativos = self::getQuantidade(EntityAluno::getAlunos('status = 2', 'id DESC',null,'COUNT(*) as qtd'));
        $totalManha = self::getQuantidade(EntityAluno::getAlunos('turma = 1', 'id DESC',null,'COUNT(*) as qtd'));
        $totalManhaAtivos = self::getQuantidade(EntityAluno::getAlunos('turma = 1 AND status = 1', 'id DESC',null,'COUNT(*) as qtd'));
        $totalManhaInativos = self::getQuantidade(EntityAluno::getAlunos('turma = 1 AND status = 2', 'id DESC',null,'COUNT(*) as qtd'));
        $totalNoite = self::getQuantidade(EntityAluno::getAlunos('turma = 3', 'id DESC',null,'COUNT(*) as qtd'));
        $totalNoiteAtivos = self::getQuantidade(EntityAluno::getAlunos('turma = 3 AND status = 1', 'id DESC',null,'COUNT(*) as qtd'));
        $totalNoiteInativos = self::getQuantidade(EntityAluno::getAlunos('turma = 3 AND status = 2', 'id DESC',null,'COUNT(*) as qtd'));
        $totalProfessores = self::getQuantidade(EntityProfessor::getProfessores(null, null, null, 'COUNT(*) as qtd'));
        $totalProfessoresAtivos = self::getQuantidade(EntityProfessor::getProfessores('status = 1', null, null, 'COUNT(*) as qtd'));
        $totalProfessoresInativos = max(0, $totalProfessores - $totalProfessoresAtivos);
        $totalDisciplinas = self::getQuantidade(EntityDisciplina::getDisciplinas(null, null, null, 'COUNT(*) as qtd'));
        $totalDisciplinasVinculadas = self::getQuantidade(EntityDisciplinaProfessor::getDisciplinasProfessor(null, null, null, 'COUNT(DISTINCT idDisciplina) as qtd'));
        $totalDisciplinasLivres = max(0, $totalDisciplinas - $totalDisciplinasVinculadas);
        $totalAulas = self::getQuantidade(EntityAula::getAulas(null, null, null, 'COUNT(*) as qtd'));
        $totalAulasAbertas = self::getQuantidade(EntityAula::getAulas('status = 1', null, null, 'COUNT(*) as qtd'));
        $totalAulasFechadasCanceladas = self::getQuantidade(EntityAula::getAulas('status IN (2, 3)', null, null, 'COUNT(*) as qtd'));
        $content = View::render('pages/dashboard',[
            'statusMessage' => $request ? Funcoes::getStatus($request) : '',
            
            'totalAlunos' => $totalAlunos,
            'totalAtivos' => $totalAtivos,
            'totalInativos' => $totalInativos,
            'totalManha' => $totalManha,
            'totalManhaAtivos' => $totalManhaAtivos, 
            'totalManhaInativos' => $totalManhaInativos,
            'totalNoite' => $totalNoite,
            'totalNoiteAtivos' => $totalNoiteAtivos,
            'totalNoiteInativos' => $totalNoiteInativos,
            'totalProfessores' => $totalProfessores,
            'totalProfessoresAtivos' => $totalProfessoresAtivos,
            'totalProfessoresInativos' => $totalProfessoresInativos,
            'totalDisciplinas' => $totalDisciplinas,
            'totalDisciplinasVinculadas' => $totalDisciplinasVinculadas,
            'totalDisciplinasLivres' => $totalDisciplinasLivres,
            'totalAulas' => $totalAulas,
            'totalAulasAbertas' => $totalAulasAbertas,
            'totalAulasFechadasCanceladas' => $totalAulasFechadasCanceladas,
            'ultimasAulas' => self::getUltimasAulas(),
        ]);
        
        return parent::getPanelDashboard('Dashboard > Cursinho', $content,'dashboard', self::$hidden);
        
    }
    
}
