<?php

namespace App\Utils;

use App\Controller\Painel\Alert;
use App\Session\User\Login as SessionUserLogin;

class Funcoes{
    private const OLD_INPUT_SESSION_KEY = '_old_input';
    private const STATUS_FLASH_SESSION_KEY = '_status_flash';
    
    
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
            case 'duplicated':
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
            case 'cpfduplicated':
                return Alert::getError('CPF já está sendo utilizado por outro usuário!');
                break;
            case 'emailDuplicated':
                return Alert::getError('E-mail já está sendo utilizado!');
                break;
	            case 'loginSuccess':
	                return Alert::getSuccess('Login efetuado com sucesso!');
	                break;
	            case 'logoInvalid':
	                return Alert::getError('Envie um logotipo válido nos formatos PNG, JPG ou WEBP.');
	                break;
	            case 'assinaturaInvalid':
	                return Alert::getError('A assinatura do coordenador deve ser enviada exclusivamente em PNG, para preservar o fundo transparente.');
	                break;
	            case 'documentoInvalid':
	                return Alert::getError('Envie os documentos do aluno somente em PDF.');
	                break;
	            case 'documentoDeleted':
	                return Alert::getSuccess('Documento excluído com sucesso!');
	                break;
	            case 'documentoDeleteError':
	                return Alert::getError('Não foi possível excluir o documento.');
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

    //Método responsável por guardar temporariamente uma mensagem de status
    public static function flashStatus($statusMessage, $context = []){
        self::init();

        if(!is_array($context)){
            $context = [];
        }

        unset($context['statusMessage']);

        $_SESSION[self::STATUS_FLASH_SESSION_KEY] = [
            'statusMessage' => $statusMessage,
            'context' => $context
        ];

        return true;
    }

    //Método responsável por recuperar e remover uma mensagem de status temporária
    public static function pullStatus(){
        self::init();

        $status = $_SESSION[self::STATUS_FLASH_SESSION_KEY] ?? [];

        unset($_SESSION[self::STATUS_FLASH_SESSION_KEY]);

        return is_array($status) ? $status : [];
    }

    //Método responsável por guardar temporariamente os dados digitados em um formulário
    public static function flashOldInput($form, $data, $except = ['senha', 'password', 'password_confirmation']){
        self::init();

        if(!is_array($data)){
            $data = [];
        }

        foreach ($except as $field) {
            unset($data[$field]);
        }

        $_SESSION[self::OLD_INPUT_SESSION_KEY][$form] = $data;

        return true;
    }

    //Método responsável por recuperar e remover os dados temporários de um formulário
    public static function pullOldInput($form){
        self::init();

        $old = $_SESSION[self::OLD_INPUT_SESSION_KEY][$form] ?? [];

        unset($_SESSION[self::OLD_INPUT_SESSION_KEY][$form]);

        if(empty($_SESSION[self::OLD_INPUT_SESSION_KEY])){
            unset($_SESSION[self::OLD_INPUT_SESSION_KEY]);
        }

        return is_array($old) ? $old : [];
    }

    //Método responsável por limpar dados temporários de um formulário
    public static function clearOldInput($form){
        self::init();

        unset($_SESSION[self::OLD_INPUT_SESSION_KEY][$form]);

        if(empty($_SESSION[self::OLD_INPUT_SESSION_KEY])){
            unset($_SESSION[self::OLD_INPUT_SESSION_KEY]);
        }

        return true;
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
	    
	    if((int)($_SESSION['usuario']['id'] ?? 0) == (int)$obUser->id){
	    	SessionUserLogin::login($obUser);
	    }
	}
	
	
	//Método responsável por retornar as permissões do usuário
	public static function getPermissoes(){
	    Funcoes::init();
	    $visivelPermissoes = SessionUserLogin::isAdmin() ? '' : 'hidden';
	    $visivelDeleteAluno = SessionUserLogin::can('excluirAluno') ? '' : 'hidden';
	    $visivelDeleteProfessor = SessionUserLogin::can('excluirProfessor') ? '' : 'hidden';
	    $visivelDeleteDisciplina = SessionUserLogin::can('excluirDisciplina') ? '' : 'hidden';
	    $visivelDeleteUsuario = SessionUserLogin::can('excluirUsuario') ? '' : 'hidden';
	    $visivelMenuAlunos = SessionUserLogin::can('menuAlunos') ? '' : 'hidden';
	    $visivelMenuProfessores = SessionUserLogin::can('menuProfessores') ? '' : 'hidden';
	    $visivelMenuAulas = SessionUserLogin::can('menuAulas') ? '' : 'hidden';
	    $visivelMenuFrequencias = SessionUserLogin::can('menuFrequencias') ? '' : 'hidden';
		    $visivelBtnNovoUsuario = SessionUserLogin::can('btnNovoUsuario') ? '' : 'hidden';
	    $visivelMenuDisciplinas = SessionUserLogin::can('menuDisciplinas') ? '' : 'hidden';
	    $visivelMenuUsuarios = SessionUserLogin::can('menuUsuarios') ? '' : 'hidden';
	    $visivelMenuConfiguracoes = SessionUserLogin::can('menuConfiguracoes') ? '' : 'hidden';
	    $permissao['excluirAluno'] = $visivelDeleteAluno;
	    $permissao['excluirProfessor'] = $visivelDeleteProfessor;
	    $permissao['excluirDisciplina'] = $visivelDeleteDisciplina;
	    $permissao['excluirUsuario'] = $visivelDeleteUsuario;
	    $permissao['menuAlunos'] = $visivelMenuAlunos;
	    $permissao['menuProfessores'] = $visivelMenuProfessores;
	    $permissao['menuAulas'] = $visivelMenuAulas;
	    $permissao['menuFrequencias'] = $visivelMenuFrequencias;
		    $permissao['btnNovoUsuario'] = $visivelBtnNovoUsuario;
	    $permissao['menuDisciplinas'] = $visivelMenuDisciplinas;
	    $permissao['menuUsuarios'] = $visivelMenuUsuarios;
	    $permissao['menuConfiguracoes'] = $visivelMenuConfiguracoes;
	    $permissao['permissoes'] = $visivelPermissoes;
	    
	    return    $permissao;
	}
	
}
