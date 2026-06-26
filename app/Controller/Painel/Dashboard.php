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
    private const ALUNO_CADASTRO_MIN_PERCENTUAL = 50;
    private const ALUNO_DOCUMENTOS_COMPLETUDE = [
        'documentoIdentificacao' => ['documento-identificacao.pdf', 'rg.pdf', 'cpf.pdf'],
        'documentoResidencia' => ['comprovante-residencia.pdf'],
    ];

    private static function getQuantidade($result){
        $obResult = $result ? $result->fetchObject() : null;

        return (int)($obResult->qtd ?? 0);
    }

    private static function hasValorUtil($value){
        $value = trim((string)$value);

        return $value !== '' && $value !== '0' && $value !== '0000-00-00' && $value !== '0000-00-00 00:00:00';
    }

    private static function hasDigitosValidos($value, $length, $exact = true){
        $digits = preg_replace('/\D+/', '', (string)$value);

        if($digits === '' || preg_match('/^0+$/', $digits)){
            return false;
        }

        return $exact ? strlen($digits) === $length : strlen($digits) >= $length;
    }

    private static function hasDataUtil($value){
        if(!self::hasValorUtil($value)){
            return false;
        }

        return strtotime((string)$value) !== false;
    }

    private static function hasDocumentoAluno($idAluno, $fileNames){
        $dir = dirname(__DIR__).'/File/files/documentos-alunos/'.(int)$idAluno;

        foreach((array)$fileNames as $fileName){
            if(is_file($dir.'/'.$fileName)){
                return true;
            }
        }

        return false;
    }

    private static function getPercentualCadastroAluno($obAluno){
        $items = [
            self::hasValorUtil($obAluno->nome ?? ''),
            self::hasDigitosValidos($obAluno->cep ?? '', 8, false),
            self::hasValorUtil($obAluno->endereco ?? ''),
            self::hasValorUtil($obAluno->numero ?? ''),
            self::hasValorUtil($obAluno->bairro ?? ''),
            self::hasValorUtil($obAluno->cidade ?? ''),
            self::hasValorUtil($obAluno->uf ?? ''),
            self::hasValorUtil($obAluno->naturalidade ?? ''),
            self::hasValorUtil($obAluno->escolaridade ?? ''),
            self::hasValorUtil($obAluno->estadoCivil ?? ''),
            self::hasValorUtil($obAluno->sexo ?? ''),
            self::hasDataUtil($obAluno->dataNasc ?? ''),
            self::hasDataUtil($obAluno->dataCad ?? ''),
            self::hasDigitosValidos($obAluno->fone ?? '', 11),
            self::hasDigitosValidos($obAluno->cpf ?? '', 11),
            self::hasValorUtil($obAluno->turma ?? ''),
            self::hasValorUtil($obAluno->mae ?? ''),
            self::hasValorUtil($obAluno->status ?? ''),
            self::hasDocumentoAluno($obAluno->id ?? 0, self::ALUNO_DOCUMENTOS_COMPLETUDE['documentoIdentificacao']),
            self::hasDocumentoAluno($obAluno->id ?? 0, self::ALUNO_DOCUMENTOS_COMPLETUDE['documentoResidencia']),
        ];

        $total = count($items);
        $preenchidos = count(array_filter($items));

        return $total > 0 ? round(($preenchidos / $total) * 100) : 0;
    }

    private static function alunoEntraNoDashboard($obAluno){
        return self::getPercentualCadastroAluno($obAluno) > self::ALUNO_CADASTRO_MIN_PERCENTUAL;
    }

    private static function getContagemAlunosDashboard($turma = null){
        $where = $turma !== null ? 'turma = '.(int)$turma : null;
        $results = EntityAluno::getAlunos($where, 'id DESC');
        $contagem = [
            'total' => 0,
            'ativos' => 0,
            'inativos' => 0,
        ];

        while($obAluno = $results->fetchObject(EntityAluno::class)){
            if(!self::alunoEntraNoDashboard($obAluno)){
                continue;
            }

            $contagem['total']++;

            if((int)$obAluno->status === 1){
                $contagem['ativos']++;
            }elseif((int)$obAluno->status === 2){
                $contagem['inativos']++;
            }
        }

        return $contagem;
    }

    private static function getDivergenciasReconhecimento($idAula){
        try{
            return self::getQuantidade(EntityFrequencia::getFrequencias(
                'idAula = '.(int)$idAula.' AND comparacaoFacialResultado = "divergente"',
                null,
                null,
                'COUNT(*) as qtd'
            ));
        }catch(\Throwable $e){
            return 0;
        }
    }

    private static function getReconhecimentoAlerta($totalDivergencias){
        $totalDivergencias = (int)$totalDivergencias;

        if($totalDivergencias > 0){
            $label = $totalDivergencias.' divergência'.($totalDivergencias > 1 ? 's' : '');
            return '<span class="dashboard-recognition-status has-alert" rel="tooltip" title="Há reconhecimento facial divergente nesta aula">'.$label.'</span>';
        }

        return '<span class="dashboard-recognition-status">OK</span>';
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
            $totalDivergencias = self::getDivergenciasReconhecimento($obAula->id);
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
                'reconhecimento' => self::getReconhecimentoAlerta($totalDivergencias),
                'status' => $obStatus ? $obStatus->nome : 'Sem status',
                'statusClasse' => self::getStatusAulaClasse((int)$obAula->status)
            ]);
        }

        if($resultados !== ''){
            return $resultados;
        }

        return '<tr><td colspan="8" class="dashboard-report-empty">Nenhuma aula cadastrada.</td></tr>';
    }

    private static function getStatusAulaClasse($status){
        if($status === 1) return 'is-open';
        if($status === 2) return 'is-closed';
        if($status === 3) return 'is-canceled';

        return '';
    }

    private static function renderDashboardContent($request = null, $aplicarInativacao = true){
        if($aplicarInativacao){
            EntityInativacaoAluno::aplicarCriteriosAutomaticos();
        }

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
       
        $alunosDashboard = self::getContagemAlunosDashboard();
        $alunosManhaDashboard = self::getContagemAlunosDashboard(1);
        $alunosNoiteDashboard = self::getContagemAlunosDashboard(3);
        $totalAlunos = $alunosDashboard['total'];
        $totalAtivos = $alunosDashboard['ativos'];
        $totalInativos = $alunosDashboard['inativos'];
        $totalManha = $alunosManhaDashboard['total'];
        $totalManhaAtivos = $alunosManhaDashboard['ativos'];
        $totalManhaInativos = $alunosManhaDashboard['inativos'];
        $totalNoite = $alunosNoiteDashboard['total'];
        $totalNoiteAtivos = $alunosNoiteDashboard['ativos'];
        $totalNoiteInativos = $alunosNoiteDashboard['inativos'];
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

        return $content;
    }

    //retorna o conteudo (view) DO DASHBOOARD
    public static function getDashboard($request = null){
        return parent::getPanelDashboard('Dashboard > Cursinho', self::renderDashboardContent($request, true),'dashboard', self::$hidden);
    }

    public static function getDashboardLive($request = null){
        $html = self::renderDashboardContent(null, false);

        return [
            'html' => $html,
            'signature' => md5($html),
            'updatedAt' => date('c')
        ];
    }
    
}
