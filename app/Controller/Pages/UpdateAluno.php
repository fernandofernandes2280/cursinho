<?php

namespace App\Controller\Pages;

use \App\Utils\View;
use App\Controller\Admin;
use \App\Model\Entity\Aluno as EntityAluno;
use \App\Model\Entity\Bairro as EntityBairro;
use \App\Model\Entity\Escolaridade as EntityEscolaridade;
use \App\Model\Entity\EstadoCivil as EntityEstadoCivil;
use \App\Model\Entity\Turma as EntityTurma;
use \App\Model\Entity\Status as EntityStatus;
use Bissolli\ValidadorCpfCnpj\CPF;
use App\Controller\Admin\Alert;
use App\Utils\Funcoes;
use CoffeeCode\Uploader\Image;
use App\Controller\File\Upload;
use App\Controller\Admin\Resize;

class UpdateAluno extends Page{
	
    public static function validaRecaptcha($request){
        
        $postVars = $request->getPostVars();
        //CURL
        $curl = curl_init();
        
        //DEFINIÇÕES DA REQUISIÇÃO
        curl_setopt_array($curl, [
           
           CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify', 
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_CUSTOMREQUEST => 'POST',
           CURLOPT_POSTFIELDS => [
                'secret' => '6LdTbDwgAAAAABanS5an0LyWQK0vrKGjsE1XtrQv',
               'response' => $postVars['g-recaptcha-response'] ?? ''
            ]
            
        ]);
        
        //EXECUTA A REQUISIÇÃO
        $response = curl_exec($curl);
        
        //FECHA A CONEXÇÃO CURL
        curl_close($curl);
        
        //RESPONSE EM ARRAY
        $responseArray = json_decode($response,true);
        
        //SUCESSO DO RECAPTCHA
        $sucesso = $responseArray['success'] ?? false;
        
        return $sucesso;
        
    }
    
    
    
    //Metodo responsável por retornar o formulário de Captura de foto do aluno
    public static function getUpdateFoto($request,$id){
        Funcoes::init();
       
        //Conteúdo do Formulário
        $content = View::render('pages/updateAluno/formPhoto',[
            'id' => $_SESSION['idAluno'],
            'title' => 'Tire uma selfie',
            
        ]);
        
        //Retorna a página completa
        
        return parent::getPageUpdateAlunoForm('Prepara Santana', $content);
    }
    
    
    
    //Metodo responsável por gravar a atualiacao da foto do aluno
    public static function setUpdateFoto($request){
        
        //Post Vars
        $postVars = $request->getPostVars();

        
        if ($postVars['image'] != ''){
            
            
            //MÉTODO RESPONSÁVEL POR FAZER O UPLOADO DA IMAGE VINDA DA WEB CAM DO PROFESSOR
            Upload::setUploadImagesWebCamAluno($request);
            
            
            //Redireciona o aluno para a carteira de estudante
            $request->getRouter()->redirect('/aluno/carteira');
        }else{
            //Redireciona o aluno para o form de foto caso o aluno não tenha tirado a foto
            $request->getRouter()->redirect('/aluno/update/foto');
        }
        
       // $request->getRouter()->redirect('/admin/alunos/'.$obAluno->id.'/edit?statusMessage=semfoto');
        
        
    }
    
    
    
    
	//retorna o conteudo (view) Para o Aluno atualizar seu cadastro
    public static function getUpdate($request,$id){
        Funcoes::init();
        //VERIFICA SE A SESSAO ALUNO EXISTE, SE NÃO EXISTE REDIRECIONA PARA O INDEX
        if(!isset($_SESSION['idAluno'])) $request->getRouter()->redirect('/aluno');
        
        $idAluno = $_SESSION['idAluno'];
        //busca usuário pelo CPF sem a maskara
        $obAluno = EntityAluno::getAlunoById($idAluno);
        
        $obAluno->sexo == 'MAS' ? $selectedSexoM = 'selected' : $selectedSexoM = '';
        $obAluno->sexo == 'FEM' ? $selectedSexoF = 'selected' : $selectedSexoF = '';
        
	    $content = View::render('pages/updateAluno/form',[
	        'title' => 'Curso Prepara Santana - Atualização Cadastral do Aluno',
	        'id' => $obAluno->id ?? '',
	        'nome' => $obAluno->nome ?? '',
	        'cep' => $obAluno->cep ?? '',
	        'endereco' => $obAluno->endereco ??'',
	        'numero' => $obAluno->numero ??'',
	        'naturalidade' => $obAluno->naturalidade ?? '',
	        'fone' => $obAluno->fone ?? '',
	        'mae' => $obAluno->mae ?? '',
	        'dataNasc' => date('Y-m-d', strtotime($obAluno->dataNasc)),
	        'statusMessage' => self::getStatus($request),
	        'optionBairros' => self::getSelectBairros($obAluno->bairro),
	        'optionEscolaridade' => self::getSelectEscolaridade($obAluno->escolaridade),
	        'optionEstadoCivil' => self::getSelectEstadoCivil($obAluno->estadoCivil),
	        'optionTurma' => self::getSelectTurmas($obAluno->turma),
	        'foto' => 'profile.png',
	        'ponteiro' => 'pointer-events: none;',
	        'foto' => $obAluno->foto.'?var='.rand(),
	        'selectedSexoM' => $selectedSexoM  ,
	        'selectedSexoF' => $selectedSexoF

	        
	    ]);
	    
	    return parent::getPageUpdateAlunoForm('Prepara Santana', $content);
	    
	}
	
	//retorna o conteudo (view) Para o Aluno atualizar seu cadastro
	public static function setUpdate($request){
	    
	    
	    Funcoes::init();
	  
	    
	    //VERIFICA SE A SESSAO ALUNO EXISTE, SE NÃO EXISTE REDIRECIONA PARA O INDEX
	    @$_SESSION['idAluno'] ? $idAluno = $_SESSION['idAluno'] : $request->getRouter()->redirect('/aluno');
	    
	    //busca usuário pelo CPF sem a maskara
	    $obAluno = EntityAluno::getAlunoById($idAluno);
	    
	    
	    $postVars = $request->getPostVars();
	    
	  
	    
	    //Atualiza a instância
	    $obAluno->nome = Funcoes::convertePriMaiuscula($postVars['nome']);
	    $obAluno->cep = $postVars['cep'];
	    $obAluno->endereco = Funcoes::convertePriMaiuscula($postVars['endereco']);
	    $obAluno->numero =  $postVars['numero'];
	    $obAluno->bairro =  $postVars['bairro'];
	    $obAluno->dataNasc = implode("-",array_reverse(explode("/",$postVars['dataNasc'])));
	    $obAluno->sexo = $postVars['sexo'] ?? $obAluno->sexo;
	    $obAluno->naturalidade = $postVars['naturalidade'];
	    $obAluno->escolaridade = $postVars['escolaridade'];
	    $obAluno->fone =str_replace('-', '', $postVars['fone']);
	    $obAluno->mae = Funcoes::convertePriMaiuscula($postVars['mae']);
	    $obAluno->estadoCivil = $postVars['estadoCivil'];
	    $obAluno->turma = $postVars['turma'];

	    
	  
    	    $obAluno->atualizar();
    	    //FAZ O ULPOAD DA FOTO DO ALUNO
    //	    Upload::setUploadImagesUpdateAluno($request);
    	    unset($_SESSION['naoCompleto']);
    	    $_SESSION['updated'] = true;
    	  //  $_SESSION['statusMessage'] = 'ConfirmUpdated';
    	 
    	    $request->getRouter()->redirect('/aluno/update/foto');
    	    //$request->getRouter()->redirect('/aluno/carteira');
	  
	}
	
	
	//retorna a tela de cpf para o aluno informar o seu
	public static function getIndex($request){
	    Funcoes::init();
	   
	    unset($_SESSION['idAluno']);
	    unset($_SESSION['updated']);
	    unset($_SESSION['naoCompleto']);
	    
	    $content = View::render('pages/updateAluno/index',[
	        'title' => 'Curso Prepara Santana',
	        'subtitle'=> 'Validação da Carteira Digital do Estudante',
	        'statusMessage' => self::getStatus($request),
	       
	       
	        
	    ]);
	    unset($_SESSION['statusMessage']);
	    return parent::getPageUpdateAluno('Prepara Santana', $content);
	    
	}
	
	//FAZ A VERIFICAÇÃO DO CPF DO ALUNO
	public static function setIndex($request){
	    
	    
	    $postVars = $request->getPostVars();
	    
	    //instancia classe pra verificar CPF
	    $validaCpf = new CPF($postVars['cpfAluno']);
	    
	    //busca usuário pelo CPF sem a maskara
	    $obUser = EntityAluno::getAlunoByCpf($validaCpf->getValue());
	    
	   
	    //VERIFICA SE O ALUNO EXISTE
	    if(!$obUser instanceof EntityAluno){
	        Funcoes::init();
	        $_SESSION['statusMessage'] = 'unknown';
	        $request->getRouter()->redirect('/aluno');
	    }
	   
	    
	    
	    //VERIFICA SE O ALUNO ESTÁ INATIVO
	    if($obUser->status == '2'){
	        Funcoes::init();
	        $_SESSION['statusMessage'] = 'inactive';
	        $request->getRouter()->redirect('/aluno');
	    }
	   
	  
    	    //VERIFICA SE O ALUNO JÁ COMPLETOU O SEU CADASTRO
    	    if(empty($obUser->mae)){
    	        
    	            //REDIRECIONA PARA O FORMULÁRIO DE ATUALIZAÇÃO CADASTRAL
    	            Funcoes::init();
    	            $_SESSION['idAluno'] = $obUser->id;
    	            $_SESSION['naoCompleto'] = true;
    	            $request->getRouter()->redirect('/aluno/update');
    	    }
    
	       //SE JA TIVER ATUALIZADO, REDIRECIONA PARA A CARTEIRA
	        Funcoes::init();
	        $_SESSION['idAluno'] = $obUser->id;
	        	        
	        $request->getRouter()->redirect('/aluno/carteira');
	    
	    
	    
	    

	    
	}
	
	
	
	
	
	//Método responsavel por retornar a mensagem de status
	private static function getStatus($request){
	    Funcoes::init();
	    //Query PArams
	    $queryParams = $request->getQueryParams();
	    
	    //Status
	    if(!isset($_SESSION['statusMessage'])) return '';
	    
	    //Mensagens de status
	    switch ($_SESSION['statusMessage']) {
	        case 'unknown':
	            return Alert::getError('CPF não encontrado!');
	            break;
	        case 'updated':
	            return Alert::getSuccess('Cadastro Atualizado com Sucesso! Informe novamente seu CPF para gerar sua Carteira.');
	            break;
	        case 'inactive':
	            return Alert::getError('ALUNO INATIVO! Procure a coordenação para regularizar sua situação.');
	            break;
	        case 'recaptchaInvalido':
	            return Alert::getError('Sua Sessão Expirou!.');
	            break;
	        case 'success':
	            return Alert::getSuccess('Carteira Validada com Sucesso!.');
	            break;
	      
	    }
	}
	
	
	//Método responsavel por listar os Bairros no select option, selecionando o do paciente
	public static function getSelectBairros($id){
	    $resultados = '';
	    $results =  EntityBairro::getBairros('nome != "Não Informado" ','nome asc',null);
	    //verifica se o id não é nulo e obtém o Procedencia do banco de dados
	    if (!is_null($id)) {
	        $selected = '';
	        while ($ob = $results -> fetchObject(self::class)) {
	            
	            //seleciona o Procedencia do paciente
	            $ob->id == $id ? $selected = 'selected' : $selected = '';
	            //View de Procedencia
	            $resultados .= View::render('admin/modules/selectOption/itemSelect',[
	                'id' => $ob ->id,
	                'nome' => $ob->nome,
	                'selecionado' => $selected
	            ]);
	        }
	        //retorna
	        return $resultados;
	    }else{ //se for nulo, lista todos e seleciona um em branco
	        while ($ob = $results -> fetchObject(self::class)) {
	           
	            $resultados .= View::render('admin/modules/selectOption/itemSelect',[
	                'id' => $ob ->id,
	                'nome' => $ob->nome,
	               
	            ]);
	        }
	        //retorna a listagem
	        return $resultados;
	    }
	}
	//Método responsavel por listar as Escolaridades
	public static function getSelectEscolaridade($id){
	    $resultados = '';
	    $results =  EntityEscolaridade::getEscolaridades('nome != "Não Informado" ','nome asc',null);
	    //verifica se o id não é nulo e obtém a Escolaridade do banco de dados
	    if (!is_null($id)) {
	        $selected = '';
	        while ($obEscolaridade = $results -> fetchObject(self::class)) {
	            
	            //seleciona as Escolaridades do paciente
	            $obEscolaridade->id == $id ? $selected = 'selected' : $selected = '';
	            //View de as Escolaridades
	            $resultados .= View::render('admin/modules/selectOption/itemSelect',[
	                'id' => $obEscolaridade ->id,
	                'nome' => $obEscolaridade->nome,
	                'selecionado' => $selected
	            ]);
	        }
	        //retorna os as Escolaridades
	        return $resultados;
	    }else{ //se as Escolaridades for nulo, lista todos e seleciona um em branco
	        while ($obEscolaridade = $results -> fetchObject(self::class)) {
	            $resultados .= View::render('admin/modules/selectOption/itemSelect',[
	                'id' => $obEscolaridade ->id,
	                'nome' => $obEscolaridade->nome,
	            ]);
	        }
	        //retorna os as Escolaridades
	        return $resultados;
	    }
	}
	
	//Método responsavel por listar os Estados Civis, 
	public static function getSelectEstadoCivil($id){
	    $resultados = '';
	    $results =  EntityEstadoCivil::getEstadoCivils('nome != "Não Informado" ','nome asc',null);
	    //verifica se o id não é nulo e obtém o Estado Civil do banco de dados
	    if (!is_null($id)) {
	        $selected = '';
	        while ($obEstadoCivil = $results -> fetchObject(self::class)) {
	            
	            //seleciona o Estado Civil do paciente
	            $obEstadoCivil->id == $id ? $selected = 'selected' : $selected = '';
	            //View de Estados Civil
	            $resultados .= View::render('admin/modules/selectOption/itemSelect',[
	                'id' => $obEstadoCivil ->id,
	                'nome' => $obEstadoCivil->nome,
	                'selecionado' => $selected
	            ]);
	        }
	        //retorna os Estados Civis
	        return $resultados;
	    }else{ //se for nulo, lista todos e seleciona um em branco
	        while ($obEstadoCivil = $results -> fetchObject(self::class)) {
	            
	            $resultados .= View::render('admin/modules/selectOption/itemSelect',[
	                'id' => $obEstadoCivil ->id,
	                'nome' => $obEstadoCivil->nome,
	            
	            ]);
	        }
	        //retorna a listagem
	        return $resultados;
	    }
	}
	//Método responsavel por listar As Turmas no select option, 
	public static function getSelectTurmas($id){
	    $resultados = '';
	    $results =  EntityTurma::getTurmas(null,'nome asc',null);
	    //verifica se o id não é nulo e obtém o Procedencia do banco de dados
	    if (!is_null($id)) {
	        $selected = '';
	        while ($ob = $results -> fetchObject(self::class)) {
	            
	            //seleciona a Turma do aluno
	            $ob->id == $id ? $selected = 'selected' : $selected = '';
	            //View de Turmas
	            $resultados .= View::render('admin/modules/selectOption/itemSelect',[
	                'id' => $ob ->id,
	                'nome' => $ob->nome,
	                'selecionado' => $selected
	            ]);
	        }
	        //retorna
	        return $resultados;
	    }else{ //se for nulo, lista todos e seleciona um em branco
	        while ($ob = $results -> fetchObject(self::class)) {
	           
	            $resultados .= View::render('admin/modules/selectOption/itemSelect',[
	                'id' => $ob ->id,
	                'nome' => $ob->nome,
	           
	            ]);
	        }
	        //retorna a listagem
	        return $resultados;
	    }
	}
	
}