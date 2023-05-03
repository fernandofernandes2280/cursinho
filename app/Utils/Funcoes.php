<?php

namespace App\Utils;

class Funcoes{
    
    //Método responsavel por Inicializar Sessão
    public static function init(){
        //verifica se a sessao não está ativa
        if(session_status() != PHP_SESSION_ACTIVE ){
            session_start();
        }
    }
    
	//Método para gerar qualquer tipo de máscara
	public static function mask($val, $mask)
	{
		$maskared = '';
		$k = 0;
		for ($i = 0; $i <= strlen($mask) - 1; ++$i) {
			if ($mask[$i] == '#') {
				if (isset($val[$k])) {
					$maskared .= $val[$k++];
				}
			} else {
				if (isset($mask[$i])) {
					$maskared .= $mask[$i];
				}
			}
		}
		
		return $maskared;
	}
	
	public static function convertePriMaiuscula($string) {
		$string = mb_strtolower(trim(preg_replace("/\s+/", " ", $string)));//transformo em minuscula toda a sentença
		$palavras = explode(" ", $string);//explodo a sentença em um array
		$t =  count($palavras);//conto a quantidade de elementos do array
		for ($i=0; $i <$t; $i++){ //entro em um for limitando pela quantidade de elementos do array
			$retorno[$i] = ucfirst($palavras[$i]);//altero a primeira letra de cada palavra para maiuscula
			if($retorno[$i] == "Dos" || $retorno[$i] == "De" || $retorno[$i] == "Do" || $retorno[$i] == "Da" || $retorno[$i] == "E" || $retorno[$i] == "Das"):
			$retorno[$i] = mb_strtolower($retorno[$i]);//converto em minuscula o elemento do array que contenha preposição de nome próprio
			endif;
		}
		return implode(" ", $retorno);
	}
	
	
	public static function maskFone($val){
	    if(!empty($val)){
	    
    	    if (strlen($val) == 8){
    	      $tel = self::mask($val, '####-####');  
    	    }
    	    if (strlen($val) == 9){
    	        $tel = self::mask($val, '#####-####');
    	    }
	    }else{
	        $tel = $val;
	    }
	    
	    return $tel;
	}
	
	public static function getPermissoes(){
	    
	    $permissao = array(
	        'excluirAluno' => '',
	        'excluirProfessor' => ''
	    );
	    
	    Funcoes::init();
	    @$_SESSION['usuario']['excluirAluno'] == 1 ? $visivelDeleteAluno = '' : $visivelDeleteAluno = 'hidden';
	    @$_SESSION['usuario']['excluirProfessor'] == 1 ? $visivelDeleteProfessor = '' : $visivelDeleteProfessor = 'hidden';
	    @$_SESSION['usuario']['menuAlunos'] == 1 ? $visivelMenuAlunos = '' : $visivelMenuAlunos = 'hidden';
	    @$_SESSION['usuario']['menuProfessores'] == 1 ? $visivelMenuProfessores = '' : $visivelMenuProfessores = 'hidden';
	    @$_SESSION['usuario']['menuAulas'] == 1 ? $visivelMenuAulas = '' : $visivelMenuAulas = 'hidden';
	    @$_SESSION['usuario']['menuFrequencias'] == 1 ? $visivelMenuFrequencias = '' : $visivelMenuFrequencias = 'hidden';
	    $permissao['excluirAluno'] = $visivelDeleteAluno;
	    $permissao['excluirProfessor'] = $visivelDeleteProfessor;
	    $permissao['menuAlunos'] = $visivelMenuAlunos;
	    $permissao['menuProfessores'] = $visivelMenuProfessores;
	    $permissao['menuAulas'] = $visivelMenuAulas;
	    $permissao['menuFrequencias'] = $visivelMenuFrequencias;
	    
	    return    $permissao;
	}
	
}