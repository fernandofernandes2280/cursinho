<?php

namespace App\Controller\Admin;

use \App\Utils\View;
use \App\Model\Entity\Aluno as EntityAluno;
use \App\Model\Entity\Aula as EntityAula;

class Inativar extends Page{
	
	//esconde busca rápida de prontuário no navBar
	private static $hidden = 'hidden';
	

	public static function getInativar($request){
	    //Query PArams
	    $queryParams = $request->getQueryParams();
	    
	    if(isset($queryParams['cont'])){
	    
	    $mensagem = 'Foram inativados '.$queryParams['cont'].' alunos';
	    }else{
	        $mensagem = '';
	    }
	  
		//Conteúdo da Home
		$content = View::render('admin/modules/inativar/index',[
				//'itens' => self::getLogsItems($request, $obPagination),
				//'pagination' => parent::getPagination($request, $obPagination),
				'title' => 'Inativar Alunos',
		        'statusMessage' => self::getStatus($request),
		        'mensagem' => $mensagem
		]);
		
		//Retorna a página completa
		return parent::getPanel('Inativar > SISCAPS', $content,'inativar', self::$hidden);
		
	}
	
	public static function setInativar($request){
	    

//INATIVA ALUNOS
     $postVars = $request->getPostVars();
     
    
    $dataIncial = $postVars['dataInicial'];
    $dataFinal = $postVars['dataFinal'];
    
    $where = ' F.status = "F" AND A.data BETWEEN "'.$dataIncial.'" AND "'.$dataFinal.'" GROUP BY F.idAluno ';
    $order = 'F.idAluno';
    $fields = 'F.idAluno As idAluno, COUNT(F.idAluno) AS qtd';
    $table = 'aulas AS A INNER JOIN frequencia AS F ON A.id = F.idAula';
    $results = EntityAula::getAulasInativaAluno($where,$order,null,$fields,$table);
    
    $cont = 0;
    while ($obInativo = $results -> fetchObject(EntityAula::class)) {
        
        if($obInativo->qtd > 2){ //INATIVA COM MAIS DE DUAS FALTAS NO INTERVALO DE DIAS
            $obAluno = EntityAluno::getAlunoById($obInativo->idAluno);
            if($obAluno ->status == 1){
                $obAluno ->status = 2; //status INATIVO
                $obAluno -> atualizar();
                $cont++;
            }
            
        }
    }
    
    $request->getRouter()->redirect('/admin/inativar?statusMessage=success&cont='.$cont.'');

	    
	    
	}

	//Método responsavel por retornar a mensagem de status
	private static function getStatus($request){
	    //Query PArams
	    $queryParams = $request->getQueryParams();
	    
	    //Status
	    if(!isset($queryParams['statusMessage'])) return '';
	    
	    //Mensagens de status
	    switch ($queryParams['statusMessage']) {
	        case 'success':
	            return Alert::getSuccess('Operação realizada com Sucesso!');
	            break;
	        case 'error':
	            return Alert::getError('Erro ao processar operação, tente novamente!');
	            break;
	    }
	}
	
}