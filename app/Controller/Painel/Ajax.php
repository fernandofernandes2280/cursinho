<?php

namespace App\Controller\Painel;

use \App\Utils\View;
use \App\Model\Entity\User as EntityUser;
use \App\Model\Entity\DisciplinaProfessor as EntityDisciplinaProfessor;
use \WilliamCosta\DatabaseManager\Pagination;

class Ajax extends Page {
	
	public static function getDisciplinasProfessor($request){
	    $postVars = $request->getPostVars();
	    $idProfessor = $postVars['idProfessor'] ?? $postVars['id'] ?? 0;

	    return EntityDisciplinaProfessor::getDisciplinasPorProfessor($idProfessor);
	}

	public static function setDisciplinaProfessor($request){
	    $postVars = $request->getPostVars();
	    $idProfessor = $postVars['idProfessor'] ?? 0;
	    $nome = $postVars['nome'] ?? '';

	    try{
	        $disciplina = EntityDisciplinaProfessor::cadastrarDisciplinaRapida($idProfessor, $nome);

	        return [
	            'success' => true,
	            'message' => $disciplina['vinculada'] ? 'Disciplina adicionada ao professor.' : 'Disciplina já estava vinculada ao professor.',
	            'disciplina' => $disciplina,
	            'disciplinas' => EntityDisciplinaProfessor::getDisciplinasPorProfessor($idProfessor)
	        ];
	    }catch(\InvalidArgumentException $e){
	        return [
	            'success' => false,
	            'message' => $e->getMessage()
	        ];
	    }catch(\Throwable $e){
	        return [
	            'success' => false,
	            'message' => 'Não foi possível cadastrar a disciplina agora.'
	        ];
	    }
	}
	
	
}
