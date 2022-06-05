<?php

namespace App\Controller\File;



use CoffeeCode\Uploader\Image;
use \App\Model\Entity\User as EntityUser;
use \App\Model\Entity\Aluno as EntityAluno;
use \App\Model\Entity\Professor as EntityProfessor;

use \App\Controller\Admin\Resize;
use App\Utils\Funcoes;

class Upload{
	
	//nome do arquivo sem a extensão
	private $name;
	
	//extensão do arquivo (sem ponto)
	private $extension;
	
	//type do arquivo
	private $type;
	
	//nome temporário/ caminho temporário do arquivo 
	private $tmpName;
	
	//código de erro do upload
	private $error; 
	
	//tamanho do arquivo
	private $size;
	
	//contador de duplicacao de arquivo
	private $duplicate = 0;
	
	public function  __construct($file){
		$this->type = $file['type'];
		$this->tmpName = $file['tmp_name'];
		$this->error = $file['error'];
		$this->size = $file['size'];
		$info = pathinfo($file['name']);
		$this->name = $info['filename'];
		$this->extension = $info['extension'];
	}
	
	//método responsável por alterar o nome do arquivo
	public function setName($name){
		$this->name = $name;
		
	}
	
	//Método responsavel por gerar um novo nome aleatório
	public function generateNewName(){
		
		$this->name = time().'-'.rand(100000,999999).'-'.uniqid();
		
	}
	
	//método responsável por retornar o nome do arquivo com asua extensão
	public function getBaseName(){
		//valida extensao
		$extension = strlen($this->extension) ? '.'.$this->extension : '';
		
		//Valida duplicação
		$duplicates = $this->duplicate > 0 ? '-'.$this->duplicate : '';
		
		//retorna o nome completo
		return $this->name.$duplicates.$extension;
		
	}
	
	//Método responsávelm por obter o nome possível para o arquivo
	private function getPossibleBasename($dir,$overwrite){
		
		//Sobrescrever o arquivo
		if($overwrite) return $this->getBaseName();
		
		//Náo pode sobrescrever arquivo
		$basename = $this->getBaseName();
		
		//vericar duplicacao
		if(!file_exists($dir.'/'.$basename)){
			return $basename; 
		}
		
		//Incrementar duplicacoes
		$this->duplicate++;
		
		//Retorno o próprio método
		return $this->getPossibleBasename($dir, $overwrite);
	}
	
	//método responsável por mover o arquivo de upload
	public function upload($dir, $overwrite = true ){
		//verificar erro
		if($this->error != 0) return false;
		
		//caminho completo de destino
		$path = $dir.'/'.$this->getPossibleBaseName($dir,$overwrite);
		
	//	var_dump($path);exit;
		
		
		//move o arquivo para a pasta de destino
		return move_uploaded_file($this->tmpName, $path);

	}
	
	
	//método responsável por mover o arquivo de upload
	public function nomeArquivo($matricula,$nome,$extensão){
	    
	    //caminho completo de destino
	    $name = $matricula.$nome.$extensão;
	    
	    return $name;
	    
	}
	//método responsável por mover o arquivo de upload
	public function uploadFotoAluno($dir, $overwrite = true,$nomeArquivo ){
	        
	    //verificar erro
	    if($this->error != 0) return false;
	    
	    
	    //caminho completo de destino
	    $path = $dir.'/'.$nomeArquivo;
	    
	    //	var_dump($path);exit;
	    
	    
	    //move o arquivo para a pasta de destino
	    return move_uploaded_file($this->tmpName, $path);
	    
	}
	
	//MÉTODO RESPONSÁVEL POR FAZER O UPLOADO DA IMAGE VINDA DA WEB CAM DO ALUNO
	public static function setUploadImagesWebCamAluno($request){
	    
	    $postVars = $request->getPostVars();
	    $obAluno = EntityAluno::getAlunoById($postVars['id']);
	    $img = $postVars['image'];
	    $folderPath = __DIR__."/files/fotos/";
	    $image_parts = explode(";base64,", $img);
	    $image_type_aux = explode("image/", $image_parts[0]);
	    $image_type = $image_type_aux[1];
	    $image_base64 = base64_decode($image_parts[1]);
	    $nome =  str_replace(' ', '',$obAluno->nome);
	    $matricula = $obAluno->matricula;
	    $fileName = $matricula.$nome . '.png';
	    $obAluno->foto = $fileName;
	    $obAluno->atualizar();
	    $file = $folderPath . $fileName;
	    file_put_contents($file, $image_base64);
	  //  chmod($file, 0777); //Corrige a permissão do arquivo.
	    
	    $img = new Resize();
	    $config = array();
	    $config['source_image'] = $file;
	    $config['width'] = 195;
	    $config['height'] = 230;
	    $img->initialize($config);
	    $img->crop();
	}
	
	//MÉTODO RESPONSÁVEL POR FAZER O UPLOADO DA IMAGE VINDA DA WEB CAM DO PROFESSOR
	public static function setUploadImagesWebCamProfessor($request){
	    
	    $postVars = $request->getPostVars();
	    $obProfessor = EntityProfessor::getProfessorById($postVars['id']);
	    $img = $postVars['image'];
	    $folderPath = __DIR__."/files/fotos/";
	    $image_parts = explode(";base64,", $img);
	    $image_type_aux = explode("image/", $image_parts[0]);
	    $image_type = $image_type_aux[1];
	    $image_base64 = base64_decode($image_parts[1]);
	    $nome =  str_replace(' ', '',$obProfessor->nome);
	    $matricula = $obProfessor->id;
	    $fileName = $matricula.$nome . '.png';
	    $obProfessor->foto = $fileName;
	    $obProfessor->atualizar();
	    $file = $folderPath . $fileName;
	    file_put_contents($file, $image_base64);
	    //  chmod($file, 0777); //Corrige a permissão do arquivo.
	    
	    $img = new Resize();
	    $config = array();
	    $config['source_image'] = $file;
	    $config['width'] = 195;
	    $config['height'] = 230;
	    $img->initialize($config);
	    $img->crop();
	}
	

	//MÉTODO RESPONSÁVEL POR FAZER O UPLOADO DA IMAGE VINDA DA WEB CAM DO PROFESSOR
	public static function setUploadImagesWebCamUser($request){
	    
	    $postVars = $request->getPostVars();
	    $obUser = EntityUser::getUserById($postVars['id']);
	    $img = $postVars['image'];
	    $folderPath = __DIR__."/files/fotos/";
	    $image_parts = explode(";base64,", $img);
	    $image_type_aux = explode("image/", $image_parts[0]);
	    $image_type = $image_type_aux[1];
	    $image_base64 = base64_decode($image_parts[1]);
	    $nome =  str_replace(' ', '',$obUser->nome);
	    $matricula = $obUser->id;
	    $fileName = $matricula.$nome . '.png';
	    $obUser->foto = $fileName;
	    $obUser->atualizar();
	    $file = $folderPath . $fileName;
	    file_put_contents($file, $image_base64);
	    //  chmod($file, 0777); //Corrige a permissão do arquivo.
	    
	    $img = new Resize();
	    $config = array();
	    $config['source_image'] = $file;
	    $config['width'] = 195;
	    $config['height'] = 230;
	    $img->initialize($config);
	    $img->crop();
	}
	
	
	
	public static function setUploadImages($request){
		
		//Post Vars
		$postVars = $request->getPostVars();
			
		//busca Aluno no banco
		$obAluno = EntityAluno::getAlunoById($postVars['id']);
		
		$upload = new Image(__DIR__.'/files', '/fotos');
		
		$files = $request->getFileVars();
	
		if(!empty($files['fImage'])){
			$file = $files['fImage'];
			
			//verifica se o arquivo existe e se o tipo é permitido
			if(empty($file['type']) || !in_array($file['type'], $upload::isAllowed())  ){
				
			    //$request->getRouter()->redirect('/admin/pacientes'); 
				
			}else{
				//faz o upload da imagem
				
				//instancia de upload
				$obUpload = new Upload($files['fImage']);

					//gera um nome aleatório pro arquivo
					//$obUpload->generateNewName();
					
					//Move os arquivos de upload
					//$sucesso = $obUpload->upload(__DIR__.'/files/fotos',false);
				$nameFile = $obUpload->nomeArquivo($obAluno->matricula, str_replace(' ', '',$obAluno->nome), '.png');	
				$sucesso = $obUpload->uploadFotoAluno(__DIR__.'/files/fotos',false,$nameFile);
			//	chmod(__DIR__."/files/fotos/".$nameFile, 0777); //Corrige a permissão do arquivo.
				//corta a foto
				$img = new Resize();
				$config = array();
				$config['source_image'] = __DIR__.'/files/fotos/'.$nameFile;
				$config['width'] = 195;
				$config['height'] = 230;
				$img->initialize($config);
				$img->crop();
				
				
					if($sucesso){

						
						//caminho da imagem completo gravada no banco
					//	$filename = __DIR__.'/files/fotos/'.$obAluno->foto;
						//verifica se o arquivo existe, se existir, atribui as permissoes e apaga o arquivo anterior
				//		if (file_exists($filename)){
					//		chmod($filename, 0777);
				//			unlink($filename);}
						//salva o nome do arquivo no banco
							$obAluno->foto = $nameFile;
							$obAluno->Atualizar();
							
						
							
							
						//exit;
					}
				//	echo 'Problemas ao enviar o arquivo <br>';
					
				
			//	exit;
			//  $uploaded = $upload->upload($file, pathinfo($file['name'], PATHINFO_FILENAME),350);
			
			//  chmod(__DIR__."/File/files/images/" . $file['name'], 0777); //Corrige a permissão do arquivo.
				
			}
			//mkdir(__DIR__.'/files/teste',0777,true);
			
		//	var_dump(pathinfo($file['name']));exit;
		}
		
	}
	
	
	
	public static function setUploadArquivos($request){
		
		$fileVars = $request->getFileVars();
		
		if(isset($fileVars['arquivo'])){
			
			
			$uploads = Upload::createMultiploUpload($fileVars['arquivo']);
			
			foreach ($uploads as $obUpload){
				
				//Move os arquivos de upload
				$sucesso = $obUpload->upload(__DIR__.'/files',false);
				if($sucesso){
					echo 'Arquivo <strong>'.$obUpload->getBaseName(). '</strong> enviado com sucesso!<br>';
					continue;
				}
				echo 'Problemas ao enviar o arquivo <br>';
				
			}
			exit;
/*			
			//instancia de upload
			$obUpload = new Upload($fileVars['arquivo']);
			
			//Altera o nome do arquivo
		//	$obUpload->setName('novo-arquivo-com-nome-alterado');
			
			
			//gera um nome aleatório pro arquivo
			$obUpload->generateNewName();
			
			//Move os arquivos de upload
			$sucesso = $obUpload->upload(__DIR__.'/files',false);
			if($sucesso){
				echo 'Arquivo <strong>'.$obUpload->getBaseName(). '</strong> enviado com sucesso';
				exit;
			}
				die('Problemas ao enviar o arquivo');
			
			//var_dump($fileVars['type']);
	*/		
		}
	
	}
	
	//método responsável por criar instancias de uploads para multiplos arquivos
	public static function createMultiploUpload($files) {
		$uploads =[];
		
		foreach ($files['name'] as $key => $value){
			//array de arquivos
			$file = [
				'name' => $files['name'][$key],
				'type' => $files['type'][$key],
				'tmp_name' => $files['tmp_name'][$key],
				'error' => $files['error'][$key],
				'size' => $files['size'][$key],
				
			];
			
			//Nova instancia
			$uploads[] = new Upload($file);
		}
		
		return $uploads;
		
	}
	
	public static function setUploadImagesProfessor($request){
	    
	    //Post Vars
	    $postVars = $request->getPostVars();
	    
	    //busca Aluno no banco
	    $obProfessor = EntityProfessor::getProfessorById($postVars['id']);
	    
	    $upload = new Image(__DIR__.'/files', '/fotos');
	    
	    $files = $request->getFileVars();
	    
	    if(!empty($files['fImage'])){
	        $file = $files['fImage'];
	        
	        //verifica se o arquivo existe e se o tipo é permitido
	        if(empty($file['type']) || !in_array($file['type'], $upload::isAllowed())  ){
	            
	            //$request->getRouter()->redirect('/admin/pacientes');
	            
	        }else{
	            //faz o upload da imagem
	            
	            //instancia de upload
	            $obUpload = new Upload($files['fImage']);
	            
	            //gera um nome aleatório pro arquivo
	            //$obUpload->generateNewName();
	            
	            //Move os arquivos de upload
	            //$sucesso = $obUpload->upload(__DIR__.'/files/fotos',false);
	            $nameFile = $obUpload->nomeArquivo($obProfessor->id, str_replace(' ', '',$obProfessor->nome), '.png');
	            $sucesso = $obUpload->uploadFotoAluno(__DIR__.'/files/fotos',false,$nameFile);
	            chmod(__DIR__."/files/fotos/".$nameFile, 0777); //Corrige a permissão do arquivo.
	            $img = new Resize();
	            $config = array(); 
	            $config['source_image'] = __DIR__.'/files/fotos/'.$nameFile;
	            $config['width'] = 195;
	            $config['height'] = 230;
	            $img->initialize($config);
	            $img->crop();  
	            
	          
	            if($sucesso){
	                
	                    //salva o nome do arquivo no banco
	                $obProfessor->foto = $nameFile;
	                $obProfessor->Atualizar();
	            }
	        }
	    }
	    
	}
	
	//MÉTODO RESPONSÁVEL POR FAZER O UPLOAD DA FOTO DO USUÁRIO
	public static function setUploadImagesUser($request){
	    
	    //Post Vars
	    $postVars = $request->getPostVars();
	    
	    //busca Aluno no banco
	    $obUser = EntityUser::getUserById($postVars['id']);
	    
	    $upload = new Image(__DIR__.'/files', '/fotos');
	    
	    $files = $request->getFileVars();
	    
	    if(!empty($files['fImage'])){
	        $file = $files['fImage'];
	        
	        //verifica se o arquivo existe e se o tipo é permitido
	        if(empty($file['type']) || !in_array($file['type'], $upload::isAllowed())  ){
	            
	            //$request->getRouter()->redirect('/admin/pacientes');
	            
	        }else{
	            //faz o upload da imagem
	            
	            //instancia de upload
	            $obUpload = new Upload($files['fImage']);
	            
	            //gera um nome aleatório pro arquivo
	            //$obUpload->generateNewName();
	            
	            //Move os arquivos de upload
	            //$sucesso = $obUpload->upload(__DIR__.'/files/fotos',false);
	            $nameFile = $obUpload->nomeArquivo($obUser->id, str_replace(' ', '',$obUser->nome), '.png');
	            $sucesso = $obUpload->uploadFotoAluno(__DIR__.'/files/fotos',false,$nameFile);
	            chmod(__DIR__."/files/fotos/".$nameFile, 0777); //Corrige a permissão do arquivo.
	            $img = new Resize();
	            $config = array();
	            $config['source_image'] = __DIR__.'/files/fotos/'.$nameFile;
	            $config['width'] = 195;
	            $config['height'] = 230;
	            $img->initialize($config);
	            $img->crop();
	            
	            
	            if($sucesso){
	                
	                //salva o nome do arquivo no banco
	                $obUser->foto = $nameFile;
	                $obUser->Atualizar();
	                $_SESSION['admin']['usuario']['foto'] = $nameFile;
	            }
	        }
	    }
	    
	}
	
	//FAZ O UPLOAD DA IMAGEM VINDA DO FORMULÁRIO DE ATUALIZAÇÃO CADASTRAL DO ALUNO
	public static function setUploadImagesUpdateAluno($request){
	    
	    
	    Funcoes::init();
	    $idAluno = $_SESSION['idAluno'];
	    
	    //busca Aluno no banco
	    $obAluno = EntityAluno::getAlunoById($idAluno);
	    
	    $upload = new Image(__DIR__.'/files', '/fotos');
	    
	    $files = $request->getFileVars();
	    
	    if(!empty($files['imagem'])){
	        $file = $files['imagem'];
	        
	        //verifica se o arquivo existe e se o tipo é permitido
	        if(empty($file['type']) || !in_array($file['type'], $upload::isAllowed())  ){
	            
	            //$request->getRouter()->redirect('/admin/pacientes');
	            
	        }else{
	            //faz o upload da imagem
	            
	            //instancia de upload
	            $obUpload = new Upload($files['imagem']);
	            
	            //gera um nome aleatório pro arquivo
	            //$obUpload->generateNewName();
	            
	            //Move os arquivos de upload
	            //$sucesso = $obUpload->upload(__DIR__.'/files/fotos',false);
	            $nameFile = $obUpload->nomeArquivo($obAluno->matricula, str_replace(' ', '',$obAluno->nome), '.png');
	            $sucesso = $obUpload->uploadFotoAluno(__DIR__.'/files/fotos',false,$nameFile);
	            //	chmod(__DIR__."/files/fotos/".$nameFile, 0777); //Corrige a permissão do arquivo.
	            //corta a foto
	            $img = new Resize();
	            $config = array();
	            $config['source_image'] = __DIR__.'/files/fotos/'.$nameFile;
	            $config['width'] = 190;
	            $config['height'] = 230;
	            $img->initialize($config);
	            $img->crop();
	            
	            
	            if($sucesso){
	                
	                
	                //caminho da imagem completo gravada no banco
	                //	$filename = __DIR__.'/files/fotos/'.$obAluno->foto;
	                //verifica se o arquivo existe, se existir, atribui as permissoes e apaga o arquivo anterior
	                //		if (file_exists($filename)){
	                //		chmod($filename, 0777);
	                    //			unlink($filename);}
	                    //salva o nome do arquivo no banco
	                    $obAluno->foto = $nameFile;
	                    $obAluno->Atualizar();
	                    
	                    
	                    
	                    
	                    //exit;
	            }
	        }
	    }
	    
	}
	
	
}