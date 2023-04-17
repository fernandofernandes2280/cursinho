<?php

namespace App\Controller\Admin;

use \App\Utils\View;
use \WilliamCosta\DatabaseManager\Pagination;

use \App\Model\Entity\Aula as EntityAula;
use \App\Model\Entity\Turma as EntityTurma;
use \App\Model\Entity\Professor as EntityProfessor;
use \App\Model\Entity\Disciplina as EntityDisciplina;
use \App\Model\Entity\Aluno as EntityAluno;
use \App\Model\Entity\Frequencia as EntityFrequencia;
//use \App\Model\Entity\Presenca as EntityPresenca;
use \App\Model\Entity\Status as EntityStatus;
use \App\Utils\Funcoes;
use Bissolli\ValidadorCpfCnpj\CPF;

class Presenca extends Page{
	
	//Armazena quantidade total de pacientes listados
	private static $qtdTotal ;
	private static $totalGeralBpac = 0;
	//esconde busca rápida de prontuário no navBar
	private static $hidden = '';
	
	
	//MÉTODO RESPONSÁVEL POR RENDERIZAR FREQUENCIA GERAL COM QRCODE NO CELULAR
	public static function getPresenca($request){
	    //Post vars
	    //  $postVars = $request->getPostVars();
	    
	    
	  //  $obAula = EntityAula::getAulaById($id);
	  //  if(!$obAula instanceof EntityAula){
	        //Redireciona
	//        $request->getRouter()->redirect('/admin/frequencias');
	//    }
	    
	    //Conteúdo da Home
	    $content = View::render('/pages/presencaqrcode/index',[
	        
	        'title'=> 'Presença Rápida',
	        'aula' =>'Aula do dia: ' .date('d/m/Y'),
	    //    'idAula' => $obAula->id
	        
	    ]);
	    
	    //Retorna a página completa
	    return parent::getPage('Frêquencias > Cursinho', $content,'presencas', 'hidden');
	}
	

	
}

