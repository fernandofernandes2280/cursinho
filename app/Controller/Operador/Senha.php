<?php

namespace App\Controller\Operador;

use \App\Utils\View;
use \App\Controller\Operador\Alert;
use  \App\Model\Entity\User;

class Senha extends Page{
	
	//esconde busca rápida de prontuário no navBar
	private static $hidden = '';
	
	
	//Método responsavel por renderizar a view de Listagem de Pacientes
	public static function getTrocarSenha($request,$typeMessage = null, $message = null){
		
		//Mensagens de status
		switch ($typeMessage) {
			case 'Error':
				$status = Alert::getError($message);
				break;
			case 'Success':
				$status = Alert::getSuccess($message);
				break;
			case null:
				$status = '';
				break;
				
		}
		
		//Status
	//	$status = !is_null($errorMessage) ? Alert::getError($errorMessage)  : Alert::getSuccess('Senha atualizada com sucesso!');
		
		//Conteúdo da Home
		$content = View::render('operador/modules/users/formTrocarSenha',[
				
				'title' => 'Trocar Senha',
				'statusMessage' => $status
				
				
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Pacientes > SISCAPS', $content,'trocarSenha', 'hidden');
		
	}
	
	//Método responsavel por renderizar a view de Listagem de Pacientes
	public static function setTrocarSenha($request){
		
		//Post vars
		$postVars = $request->getPostVars();
		
	
		
		//Recebe o array email. Se não existir retorna vazio
		$senhaAtual = $postVars['senhaAtual'] ?? '';
		$novaSenha = $postVars['novaSenha'] ?? '';
		$confirmeNovaSenha = $postVars['confirmeNovaSenha'] ?? '';
		
		$id = ($_SESSION['operador']['usuario']['id']);
		
		//busca usuário pelo e-mail
		$obUser = User::getUserById($id);
		
		if($obUser instanceof User){
			
		//	var_dump($obUser->senha); exit;
			//Verifica a senha do usuário
		//	if(!password_verify($senhaAtual, $obUser->senha)){
		    if($senhaAtual !== $obUser->senha){
				return self::getTrocarSenha($request,'Error','Senha atual inválida');
			}
			
			if($novaSenha !== $confirmeNovaSenha){
				return self::getTrocarSenha($request,'Error','Nova Senha e Confirme Nova Senha são diferentes!');
			}
			
			//Atualiza a instância
			$obUser->senha = $novaSenha;
			$obUser->atualizar();
		
			return self::getTrocarSenha($request, 'Success','Senha atualizada com sucesso!');
		}
		
		
	}
	

	
	
	
}