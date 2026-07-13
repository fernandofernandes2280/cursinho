<?php

namespace App\Controller\Painel;

use \App\Utils\View;
use \App\Model\Entity\Escolaridade as EntityEscolaridade;
use \App\Utils\Funcoes;
use \WilliamCosta\DatabaseManager\Pagination;

class Escolaridade extends Page{
	
	//esconde busca rápida de prontuário no navBar
	private static $hidden = 'hidden';

	private static function escape($value){
		return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
	}
	
	//Método responsavel por obter a renderização da listagem dos registros do banco
	private static function getEscolaridadesItems($request, &$obPagination){

		$itens = '';
		
		//Quantidade total de registros
		$quantidadetotal =  EntityEscolaridade::getEscolaridades(null, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;
		
		//Página atual
		$queryParams = $request->getQueryParams();
		$paginaAtual = $queryParams['page'] ?? 1;
		
		//Instancia de paginacao
		$obPagination = new Pagination($quantidadetotal,$paginaAtual,5);
		
		//Resultados da Página
		$results = EntityEscolaridade::getEscolaridades(null, 'id',$obPagination->getLimit());
		
		//Renderiza o item
		while ($ob = $results->fetchObject(EntityEscolaridade::class)) {
		
			//View de listagem
			$itens.= View::render('painel/modules/escolaridades/item',[
					'id' => $ob->id,
					'nome' => $ob->nome,
					'nomeAttr' => self::escape($ob->nome),
					'deleteUrl' => URL.'/escolaridades/'.(int)$ob->id.'/delete',
					

			]);
		}
		
		
		//Retorna a listagem
		return $itens;
		
	}
	
	
	//Método responsavel por renderizar a view de Listagem
	public static function getEscolaridades($request){
		
		//Conteúdo da Home
		$content = View::render('painel/modules/escolaridades/index',[
				'itens' => self::getEscolaridadesItems($request, $obPagination),
				'pagination' => parent::getPagination($request, $obPagination),
				'status' => self::getStatus($request),
				'title' => 'Escolaridades',
				'statusMessage' => ''
		]);
		
		//Retorna a página completa
		return parent::getPanel('Escolaridades > Siscaps', $content,'escolaridades', self::$hidden);
		
	}
	
	//Metodo responsávelpor retornar o formulário de cadastro 
	public static function getNewEscolaridade($request){
		
		//Conteúdo do Formulário
		$content = View::render('painel/modules/escolaridades/form',[
				'title' => 'Cadastrar Escolaridade',
				'nome' => '',
				'statusMessage' => '',
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Cadastrar Escolaridade > Siscaps', $content,'escolaridades', self::$hidden);
		
	}
	
	
	//Metodo responsávelpor por cadastrar no banco
	public static function setNewEscolaridade($request){
		//Post vars
		$postVars = $request->getPostVars();
		

		$nome = $postVars['nome'] ?? '';

		
		//Nova instancia
		$ob = new EntityEscolaridade();
		$ob->nome = $nome;
		
		$ob->cadastrar();
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/escolaridades/'.$ob->id.'/edit?status=created');
		
	}
	
	//Método responsavel por retornar a mensagem de status
	private static function getStatus($request){
		//Query PArams
		$queryParams = $request->getQueryParams();
		
		if(isset($queryParams['statusMessage'])){
			return Funcoes::getStatus($request);
		}

		//Status
		if(!isset($queryParams['status'])) return '';
		
		//Mensagens de status
		switch ($queryParams['status']) {
			case 'created':
				return Alert::getSuccess('Escolaridade criada com sucesso!');
			break;
			case 'updated':
				return Alert::getSuccess('Escolaridade atualizada com sucesso!');
				break;
			case 'deleted':
				return Alert::getSuccess('Escolaridade excluída com sucesso!');
				break;
			
		}
	}
	
	
	//Metodo responsávelpor retornar o formulário de Edição 
	public static function getEditEscolaridade($request,$id){
		//obtém o usuário do banco de dados
		$ob = EntityEscolaridade::getEscolaridadeById($id);
		
		//Valida a instancia
		if(!$ob instanceof EntityEscolaridade){
			$request->getRouter()->redirect('/escolaridades');
		}
		
		//Conteúdo do Formulário
		$content = View::render('painel/modules/escolaridades/form',[
				'title' => 'Editar Escolaridade',
				'nome' => $ob->nome,
				'statusMessage' => self::getStatus($request),
				
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Editar Escolaridade > Siscaps', $content,'escolaridades', self::$hidden);
		
	}
	
	//Metodo responsável por gravar a atualizacao de um usuário
	public static function setEditEscolaridade($request,$id){
		//obtém o usuário do banco de dados
		$ob = EntityEscolaridade::getEscolaridadeById($id);
		
		//Valida a instancia
		if(!$ob instanceof EntityEscolaridade){
			$request->getRouter()->redirect('/escolaridades');
		}
		
		
		//Post Vars
		$postVars = $request->getPostVars();
		$nome = $postVars['nome'] ?? '';
		
		//Atualiza a instância
		$ob->nome = $nome;
		
		$ob->atualizar();
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/escolaridades/'.$ob->id.'/edit?status=updated');
		
		
	}
	
	
	//Metodo responsávelpor retornar o formulário de Exclusão 
	public static function getDeleteEscolaridade($request,$id){
		$request->getRouter()->redirect('/escolaridades');
	}
	
	//Metodo responsável por Excluir 
	public static function setDeleteEscolaridade($request,$id){
		$isAjax = Funcoes::isAjaxRequest($request);

		//obtém o usuário do banco de dados
		$ob = EntityEscolaridade::getEscolaridadeById($id);
		
		//Valida a instancia
		if(!$ob instanceof EntityEscolaridade){
			if($isAjax){
				return Funcoes::getDeleteJsonResponse(false, 'Escolaridade não encontrada.');
			}

			$request->getRouter()->redirect('/escolaridades');
		}
		
			
		
		
		//Exclui
			$ob->excluir($id);

		if($isAjax){
			Funcoes::flashStatus('deleted');

			return Funcoes::getDeleteJsonResponse(true, 'Escolaridade excluída com sucesso.', [
				'id' => (int)$ob->id,
				'redirectUrl' => URL.'/escolaridades',
			]);
		}
	
		
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/escolaridades?statusMessage=deleted');
		
		
	}
	
	
}
