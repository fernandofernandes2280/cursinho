<?php

namespace App\Controller\Admin;

use App\Utils\View;
use App\Utils\Funcoes;
use Dompdf\Dompdf;
use App\Model\Entity\Bairro;
use App\Model\Entity\Cidade;

class Relatorio  extends Page{
	
	//esconde busca rápida de prontuário no navBar
	private static $hidden = '';
	

	
	//Método responsavel por gerar o PDF de Relatório de Alunos
	public static function getLmePrintPdf($request){

		//Se clicar na pesquisa rápida 
		$postVars = $request->getPostVars();
		$uri = $request->getUri();
		$pront = @$postVars['pront'];
		//retira '/caps' da URI
		$uri = str_replace('/caps','',$uri);
		
		if(isset($pront)){
			$request->getRouter()->redirect('/admin/pacientes/'.$pront.'/lme');
		}
		
		
		
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
		
		$pdf = self::getLmePrint($request);
		
		//carrega o conteúdo do arquivo .php
		$dompdf->loadHtml($pdf);
		
		
		
		//Configura o tamanho do papel
		$dompdf->setPaper("A4");
		
		$dompdf->render();
		
		$dompdf->stream("Lme-Termo.php", ["Attachment" => false]);
		
	}
}