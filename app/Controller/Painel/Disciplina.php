<?php

namespace App\Controller\Painel;

use \App\Utils\View;
use \App\Model\Entity\Disciplina as EntityDisciplina;
use \App\Utils\Funcoes;

class Disciplina extends Page{
	
	//esconde busca rápida de prontuário no navBar
	private static $hidden = 'hidden';
	
	//Método responsavel por obter a renderização da listagem dos registros do banco
	private static function getDisciplinaItems($request){

		$itens = '';
		
		//Resultados da Página
		$results = EntityDisciplina::getDisciplinas(null, 'nome');
		
		//Renderiza o item
		while ($ob = $results->fetchObject(EntityDisciplina::class)) {
		
			//View de listagem
			$itens.= View::render('painel/modules/disciplinas/item',[
					'id' => $ob->id,
					'nome' => $ob->nome,
			       'excluirDisciplinaVisivel' => permissaoExcluirDisciplinas,
					
			]);
		}
		
		
		//Retorna a listagem
		return $itens;
		
	}
	
	
	//Método responsavel por renderizar a view de Listagem
	public static function getDisciplina($request){
		
		//Conteúdo da Home
		$content = View::render('painel/modules/disciplinas/index',[
		        'title' => 'Disciplinas',
				'itens' => self::getDisciplinaItems($request),
				'statusMessage' => self::getStatus($request),

		]);
		
		//Retorna a página completa
		return parent::getPanel('Disciplinas > Cursinho', $content,'disciplinas', self::$hidden);
		
	}
	
	//Metodo responsávelpor retornar o formulário de cadastro 
	public static function getDisciplinaNew($request){
		
		//Conteúdo do Formulário
		$content = View::render('painel/modules/disciplinas/form',[
				'title' => 'Disciplinas > Novo',
				'nome' => '',
				'descricao' => '',
				'statusMessage' => ''

				
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Disciplinas > Cursinho', $content,'disciplinas', self::$hidden);
		
	}
	
	
	//Metodo responsávelpor por cadastrar no banco
	public static function setDisciplinaNew($request){
		//Post vars
		$postVars = $request->getPostVars();
		
		$nome = $postVars['nome'] ?? '';
		
		//Nova instancia
		$ob = new EntityDisciplina();
		$ob->nome = Funcoes::convertePriMaiuscula($nome) ?? $ob->nome;
		
		$ob->cadastrar();
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/disciplinas/'.$ob->id.'/edit?status=created');
		
	}
	
	//Método responsavel por retornar a mensagem de status
	private static function getStatus($request){
		//Query PArams
		$queryParams = $request->getQueryParams();
		
		//Status
		if(!isset($queryParams['statusMessage'])) return '';
		
		//Mensagens de status
		switch ($queryParams['statusMessage']) {
			case 'created':
				return Alert::getSuccess('Disciplina criada com sucesso!');
			break;
			case 'updated':
				return Alert::getSuccess('Disciplina atualizada com sucesso!');
				break;
			case 'deleted':
				return Alert::getSuccess('Disciplina excluída com sucesso!');
				break;
			
		}
	}
	
	
	//Metodo responsávelpor retornar o formulário de Edição 
	public static function getDisciplinaEdit($request,$id){
		//obtém o usuário do banco de dados
		$ob = EntityDisciplina::getDisciplinaById($id);
		
		//Valida a instancia
		if(!$ob instanceof EntityDisciplina){
			$request->getRouter()->redirect('/disciplinas');
		}
		
		//Conteúdo do Formulário
		$content = View::render('painel/modules/disciplinas/form',[
				'title' => 'Disciplinas > Editar',
				'nome' => $ob->nome,
				'statusMessage' => self::getStatus($request),
				
				
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Disciplinas > Editar', $content,'disciplinas', self::$hidden);
		
	}
	
	//Metodo responsável por gravar a atualizacao de um usuário
	public static function setDisciplinaEdit($request,$id){
		//obtém o usuário do banco de dados
		$ob = EntityDisciplina::getDisciplinaById($id);
		
		//Valida a instancia
		if(!$ob instanceof EntityDisciplina){
			$request->getRouter()->redirect('/disciplinas');
		}
		
		
		//Post Vars
		$postVars = $request->getPostVars();
		$nome = $postVars['nome'] ?? '';
		
		//Atualiza a instância
		$ob->nome = $nome;
		$ob->atualizar();
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/disciplinas/'.$ob->id.'/edit?status=updated');
		
		
	}
	
	
	//Metodo responsávelpor retornar o formulário de Exclusão 
	public static function getDisciplinaDelete($request,$id){
		//obtém o registro do banco de dados
	    $ob = EntityDisciplina::getDisciplinaById($id);
		
		//Valida a instancia
	    if(!$ob instanceof EntityDisciplina){
			$request->getRouter()->redirect('/disciplinas');
		}
		
		
		
		//Conteúdo do Formulário
		$content = View::render('painel/modules/disciplinas/delete',[
		        'title' => 'Disciplinas > Excluir',
				'nome' => $ob->nome,
			
				
				
		]);
		
		//Retorna a página completa
		return parent::getPanel('Disciplinas > Excluir', $content,'disciplinas' , self::$hidden);
		
	}
	
	//Metodo responsável por Excluir 
	public static function setDisciplinaDelete($request,$id){
		//obtém o usuário do banco de dados
	    $ob = EntityDisciplina::getDisciplinaById($id);
		
		//Valida a instancia
	    if(!$ob instanceof EntityDisciplina){
			$request->getRouter()->redirect('/disciplinas');
		}
		
			
		//Exclui 
		$ob->excluir($id);
		
		//Redireciona o usuário
		$request->getRouter()->redirect('/disciplinas?statusMessage=deleted');
		
		
	}
	
	
}
