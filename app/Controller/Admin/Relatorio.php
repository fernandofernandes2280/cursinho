<?php

namespace App\Controller\Admin;

use \App\Utils\View;
use \App\Model\Entity\Aluno as EntityAluno;
use \App\Model\Entity\Bairro as EntityBairro;
use \App\Model\Entity\Escolaridade as EntityEscolaridade;
use \App\Model\Entity\EstadoCivil as EntityEstadoCivil;
use \App\Model\Entity\Turma as EntityTurma;
use \App\Model\Entity\Status as EntityStatus;
use \App\Utils\Funcoes;
use \App\Controller\File\Upload as Upload;
use \App\Controller\Operador;
use \App\Model\Entity\User as EntityUser;

use \WilliamCosta\DatabaseManager\Pagination;
use Dompdf\Dompdf;
use Bissolli\ValidadorCpfCnpj\CPF;
use WilliamCosta\DatabaseManager\Database;

class Relatorio  extends Page{
	
    //Método responsavel por gerar o PDF de Relatório de Alunos
    public static function getPdfAluno($request){
        
        //Inicializa a sessão
        Funcoes::init();
        //recebe os valores das variáveis de sessão
        //cláusula where
        $where = 'id IN ('.$_SESSION['id'].')';
        
        //Ordem de pesquisa
        $order = 'nome ASC' ;
        
        //se não clicar na pesquisa rápida, continua daqui
        
        //instância a classe
        $dompdf = new Dompdf(["enable_remote" => true]);
        $options = $dompdf->getOptions();
        $options->setDefaultFont('Courier');
        $dompdf->setOptions($options);
        //abre a sessão de cache
        //	ob_start();
        //caminho do arquivo
        //	require '{{URL}}../../resources/view/admin/modules/pacientes/capa.html';
        //recebe o conteudo entre as tags ob_start e ob_get_clean
        //	$pdf = ob_get_clean();
        
        //$pdf = self::getAlunos($request);
        
        //Obtem os pacientes
        $results = EntityAluno::getAlunos($where,$order);
        
        $resultados = '';
        $ord = 0;
        //Renderiza
        while ($obAluno = $results -> fetchObject(EntityAluno::class)) {
            $ord++;
            
            //View de pacientes
            $resultados .= View::render('pages/relatorios/itemAlunos',[
                
                //muda cor do texto do status para azul(ativo) ou vermelho(inativo)
                $obAluno->status == 1 ? $cor = 'bg-gradient-success' : $cor = 'bg-gradient-danger',
                'ord' => $ord,
                'matricula' => $obAluno->matricula,
                'nome' => $obAluno->nome,
                'endereco' => $obAluno->endereco.', '.EntityBairro::getBairroById($obAluno->bairro)->nome.', '.$obAluno->cidade.', '.$obAluno->uf,
                'escolaridade' =>EntityEscolaridade::getEscolaridadeById($obAluno->escolaridade)->nome,
                'sexo' => $obAluno->sexo,
                'dataNasc' => date('d-m-Y', strtotime($obAluno->dataNasc)),
                'dataCad' => date('d-m-Y', strtotime($obAluno->dataCad)),
                'fone' => Funcoes::mask($obAluno->fone, '(##) #####-####') ,
                'cpf' => Funcoes::mask($obAluno->cpf, '###.###.###-##') ,
                'turma' =>EntityTurma::getTurmaById($obAluno->turma)->nome,
                'status' =>EntityStatus::getStatusById($obAluno->status)->nome,
                
            ]);
            
        }
        
        $obPagination = 0;
        //View de pacientes
        $alunos = View::render('pages/relatorios/alunos',[
            
            //'itens' => View::render('pages/relatorios/itemAlunos',[])
            'itens' => $resultados
            
        ]);
        
        
        
        //carrega o conteúdo do arquivo .php
        $dompdf->loadHtml($alunos);
        
        
        
        //Configura o tamanho do papel
        
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $dompdf->stream("Lme-Termo.php", ["Attachment" => false]);
        
    }

	

}