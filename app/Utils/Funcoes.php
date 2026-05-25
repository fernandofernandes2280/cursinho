<?php

namespace App\Utils;

use App\Controller\Admin\Alert;
class Funcoes{
    
    
    //Método responsavel por retornar a mensagem de status
    public  static function getStatus($request){
        //Query PArams
        $queryParams = $request->getQueryParams();
        
        //Status
        if(!isset($queryParams['statusMessage'])) return '';
        
        //Mensagens de status
        switch ($queryParams['statusMessage']) {
            case 'created':
                return Alert::getSuccess('Dados gravado(s) com sucesso!');
                break;
            case 'updated':
                return Alert::getSuccess('Dados atualizado(s) com sucesso!');
                break;
            case 'deleted':
                return Alert::getSuccess('Dados excluído(s) com sucesso!');
                break;
            case 'duplicad':
                return Alert::getError('Dados Já cadastrado!');
                break;
            case 'deletedfail':
                return Alert::getError('Você não tem permissão para Excluir! Contate o administrador.');
                break;
            case 'semfoto':
                return Alert::getError('Nenhuma foto foi enviada!');
                break;
            case 'cpfInvalid':
                return Alert::getError('CPF Inválido!');
                break;
            case 'cpfDuplicated':
                return Alert::getError('CPF já está sendo utilizado por outro usuário!');
                break;
            case 'emailDuplicated':
                return Alert::getError('E-mail já está sendo utilizado!');
                break;
        }
    }
    
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
	

	public static function getSessaoPermissoes($obUser){
	    Funcoes::init();
	    
	    if($_SESSION['admin']['tipo'] == $obUser->tipo){
	    $_SESSION['usuario'] = [
	        'id' => $obUser->id,
	        'nome' => $obUser->nome,
	        'email' => $obUser->email,
	        'tipo' => $obUser->tipo,
	        'foto' => $obUser->foto,
	        'excluirAluno' => $obUser->excluirAluno,
	        'excluirProfessor' => $obUser->excluirProfessor,
	        'excluirUsuario' => $obUser->excluirUsuario,
	        'menuAlunos' => $obUser->menuAlunos,
	        'menuProfessores' => $obUser->menuProfessores,
	        'menuAulas' => $obUser->menuAulas,
	        'menuFrequencias' => $obUser->menuFrequencias,
	        'btnNovoUsuario' => $obUser->btnNovoUsuario,
	        'menuPresenca' => $obUser->menuPresenca,
	        'menuDisciplinas' => $obUser->menuDisciplinas,
	        'excluirDisciplina' => $obUser->excluirDisciplina,
	    ];
	    }
	}
	
	
	//Método responsável por retornar as permissões do usuário
	public static function getPermissoes(){
	    Funcoes::init();
	    $isAdmin = @$_SESSION['usuario']['tipo'] == 'Admin';
	    $visivelPermissoes = $isAdmin ? '' : 'hidden';
	    $visivelDeleteAluno = $isAdmin || @$_SESSION['usuario']['excluirAluno'] == 1 ? '' : 'hidden';
	    $visivelDeleteProfessor = $isAdmin || @$_SESSION['usuario']['excluirProfessor'] == 1 ? '' : 'hidden';
	    $visivelDeleteDisciplina = $isAdmin || @$_SESSION['usuario']['excluirDisciplina'] == 1 ? '' : 'hidden';
	    $visivelDeleteUsuario = $isAdmin || @$_SESSION['usuario']['excluirUsuario'] == 1 ? '' : 'hidden';
	    $visivelMenuAlunos = $isAdmin || @$_SESSION['usuario']['menuAlunos'] == 1 ? '' : 'hidden';
	    $visivelMenuProfessores = $isAdmin || @$_SESSION['usuario']['menuProfessores'] == 1 ? '' : 'hidden';
	    $visivelMenuAulas = $isAdmin || @$_SESSION['usuario']['menuAulas'] == 1 ? '' : 'hidden';
	    $visivelMenuFrequencias = $isAdmin || @$_SESSION['usuario']['menuFrequencias'] == 1 ? '' : 'hidden';
	    $visivelBtnNovoUsuario = $isAdmin || @$_SESSION['usuario']['btnNovoUsuario'] == 1 ? '' : 'hidden';
	    $visivelMenuPresenca = $isAdmin || @$_SESSION['usuario']['menuPresenca'] == 1 ? '' : 'hidden';
	    $visivelMenuDisciplinas = $isAdmin || @$_SESSION['usuario']['menuDisciplinas'] == 1 ? '' : 'hidden';
	    $permissao['excluirAluno'] = $visivelDeleteAluno;
	    $permissao['excluirProfessor'] = $visivelDeleteProfessor;
	    $permissao['excluirDisciplina'] = $visivelDeleteDisciplina;
	    $permissao['excluirUsuario'] = $visivelDeleteUsuario;
	    $permissao['menuAlunos'] = $visivelMenuAlunos;
	    $permissao['menuProfessores'] = $visivelMenuProfessores;
	    $permissao['menuAulas'] = $visivelMenuAulas;
	    $permissao['menuFrequencias'] = $visivelMenuFrequencias;
	    $permissao['btnNovoUsuario'] = $visivelBtnNovoUsuario;
	    $permissao['menuPresenca'] = $visivelMenuPresenca;
	    $permissao['menuDisciplinas'] = $visivelMenuDisciplinas;
	    $permissao['permissoes'] = $visivelPermissoes;
	    
	    return    $permissao;
	}
	
}
