<?php 

$pdo  = new PDO('mysql:host=localhost;dbname=cursinho', 'root', 'fl4m3ng0');


	if(isset($_POST['send'])){
	    
	    //verifica se a sessao não está ativa
	    if(session_status() != PHP_SESSION_ACTIVE ){
	        session_start();
	    }
	    $user = $_SESSION['usuario']['id'];

		$arr= array();
		//define a data
		//$dataAtual = date('Y-m-d H:i:s');
		$dataAtual = date('Y-m-d');
		//formada a data e hora para registro
		$dataReg = date('Y-m-d H:i:s');
		//verfica se o aluno está exsite e se está ativo
		$stmtAluno = $pdo->prepare('SELECT id as idAluno, matricula, nome,foto, status, turma FROM alunos WHERE matricula= "'.$_POST['matricula'].'"  ');
	//	$stmtAluno->bindValue(1, ($_POST['matricula']), PDO::PARAM_STR);
		//$stmtAluno->bindValue(2, 1, PDO::PARAM_INT);//1 - status ativo
		$stmtAluno->execute();
		
		
		//achou aluno
		if(($stmtAluno) && ($stmtAluno->rowCount() != 0)){
		    $row_aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);
		    $arr['idAluno'] = $row_aluno['idAluno'];
		    $arr['nome'] = $row_aluno['nome'];
		    $arr['matricula'] = $row_aluno['matricula'];
		    $arr['foto'] = $row_aluno['foto'];
		    $arr['status'] = $row_aluno['status'];
		    $arr['turma'] = $row_aluno['turma'];
		    $arr['presenca'] = 'PRESENÇA CONFIRMADA!';
		    
		    //verifica se a aula existe na data atual
		    $stmtAula = $pdo->prepare('SELECT  id as idAula FROM aulas WHERE data = "'.$dataAtual.'" ');
		    $stmtAula->execute();
		    //aula existe
		    if(($stmtAula) && ($stmtAula->rowCount() != 0)){ 
		        
		        $row_aula = $stmtAula->fetch(PDO::FETCH_ASSOC);
		        //armazena id da aula
		        $arr['idAula'] = $row_aula['idAula'];
		        
		        //verifica se o aluno está na frequencia
		        $stmt = $pdo->prepare('SELECT  F.id as idFreq, F.status as status, A.id as idAluno, A.matricula as matricula, A.nome as nome, A.foto as foto, L.diaSemana as diaSemana FROM frequencia F INNER JOIN alunos A ON F.idAluno = A.id INNER JOIN aulas L ON F.idAula=L.id WHERE F.idAluno=? and F.idAula =? ');
		        $stmt->bindValue(1, ((int)$arr['idAluno']), PDO::PARAM_INT);
		        $stmt->bindValue(2, ((int)$arr['idAula']), PDO::PARAM_INT);
		        $stmt->execute();
		        //aluno na frequencia
		        if(($stmt) && ($stmt->rowCount() != 0)){ 
		            
		            $row_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
		            //armazena id da frequencia
		            $arr['idFreq'] = $row_usuario['idFreq'];
		            
		            //verifica se o aluno não está presente
		            if($row_usuario['status'] != 'P'){
		                
		               		                
		                //registra a presença do aluno
		                $stmtUpdate = $pdo->prepare('UPDATE frequencia set status =? , autor =?, dataReg =?  WHERE id =?');
		                $stmtUpdate->bindValue(1, 'P', PDO::PARAM_STR);
		                $stmtUpdate->bindValue(2, $user, PDO::PARAM_INT);//id do usuário logado
		                $stmtUpdate->bindValue(3, $dataReg, PDO::PARAM_STR);
		                $stmtUpdate->bindValue(4, ((int)$arr['idFreq']), PDO::PARAM_INT);
		                if($stmtUpdate->execute()){
		                    //ATIVA O ALUNO SE ESTIVER INATIVO
		                    if( $arr['status'] == 2){
		                        $stmtUpdate = $pdo->prepare('UPDATE alunos set status =? WHERE id =?');
		                        $stmtUpdate->bindValue(1, 1, PDO::PARAM_INT);//status ativo
		                        $stmtUpdate->bindValue(2, ((int)$arr['idAluno']), PDO::PARAM_INT);
		                        $stmtUpdate->execute();
		                    }
		                }
		            }
		        }
		        //aluno não está na frequencia
		        else{
		            
		          		            
		            //insere o aluno na frequencia da Aula
		            $stmtInsert = $pdo->prepare('INSERT INTO frequencia (status, autor, dataReg, idAula, idAluno) VALUES (?, ?, ?, ?, ?)');
		            $stmtInsert->bindValue(1, 'P', PDO::PARAM_STR);
		            $stmtInsert->bindValue(2, $user, PDO::PARAM_INT);//id do usuário logado
		            $stmtInsert->bindValue(3, $dataReg, PDO::PARAM_STR);
		            $stmtInsert->bindValue(4, ((int)$arr['idAula']), PDO::PARAM_INT);
		            $stmtInsert->bindValue(5, ((int)$arr['idAluno']), PDO::PARAM_INT);
		            if($stmtInsert->execute()){
		                //ATIVA O ALUNO SE ESTIVER INATIVO
		                if( $arr['status'] == 2){
		                    $stmtUpdate = $pdo->prepare('UPDATE alunos set status =? WHERE id =?');
		                    $stmtUpdate->bindValue(1, 1, PDO::PARAM_INT);//status ativo
		                    $stmtUpdate->bindValue(2, ((int)$arr['idAluno']), PDO::PARAM_INT);
		                    $stmtUpdate->execute();
		                }
		            }
		            
		            
		        }
		        
		    }else{
		        //aula não existe
		        //insere o aluno na frequencia da Aula
		        $stmtInsert = $pdo->prepare('INSERT INTO aulas (data, diaSemana, turma, professor1, disciplina1, professor2, disciplina2, status, dataReg, autor, obs ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)');
		   
    	        $stmtInsert->bindValue(1, $dataAtual, PDO::PARAM_STR);
		        //busca o dia da semana
		        $diasemana = array('DOM','SEG','TER', 'QUA', 'QUI', 'SEX','SAB');
		        $diasemana_num = date('w',strtotime($dataAtual));
		        $stmtInsert->bindValue(2, $diasemana[$diasemana_num], PDO::PARAM_STR);
		        
		        $stmtInsert->bindValue(3, ((int)$arr['turma']), PDO::PARAM_INT);
		        $stmtInsert->bindValue(4, 917, PDO::PARAM_INT);//professor nao informado
		        $stmtInsert->bindValue(5, 15, PDO::PARAM_INT);//disciplina não informada
		        $stmtInsert->bindValue(6, 9, PDO::PARAM_INT);//professor nao informado
		        $stmtInsert->bindValue(7, 15, PDO::PARAM_INT);//disciplina não informada
		        $stmtInsert->bindValue(8, 1, PDO::PARAM_INT);//status da aula aberta
		        
		        
		        $stmtInsert->bindValue(9, $dataReg, PDO::PARAM_STR);
		        $stmtInsert->bindValue(10, $user, PDO::PARAM_INT);//id do usuário logado
		        $stmtInsert->bindValue(11, '', PDO::PARAM_STR);//campo obs
		        
		        if($stmtInsert->execute()){
		            
		            //busca a aula que acabou de ser criada
		            $stmtAula = $pdo->prepare('SELECT  id as idAula FROM aulas WHERE data = "'.$dataAtual.'" ');
		            $stmtAula->execute();
		            //aula existe
		            if(($stmtAula) && ($stmtAula->rowCount() != 0)){
		                $row_aula = $stmtAula->fetch(PDO::FETCH_ASSOC);
		                //armazena id da aula
		                $arr['idAula'] = $row_aula['idAula'];}
		            
		            
		            //seleciona todos os alunos ativos da turma 
		            $stmtTodosComFalta = $pdo->prepare('SELECT  id as idAluno FROM alunos WHERE turma = "'.$arr['turma'].'" AND status = 1');
		            $stmtTodosComFalta->execute();
		            while($row = $stmtTodosComFalta->fetch(PDO::FETCH_OBJ)) {
		                
		                //insere aluno por aluno na frequencia com Falta
		                $stmtInsert = $pdo->prepare('INSERT INTO frequencia (status, autor, dataReg, idAula, idAluno) VALUES (?, ?, ?, ?, ?)');
		                
		                //Coloca Presença no aluno atual e falta nos demais
		                if($arr['idAluno'] == $row->idAluno){
    		                $stmtInsert->bindValue(1, 'P', PDO::PARAM_STR);
    		               
		                }else{
		                    $stmtInsert->bindValue(1, 'F', PDO::PARAM_STR);
		                }
		                
		                
		                $stmtInsert->bindValue(2, $user, PDO::PARAM_INT);//id do usuário logado
		                $stmtInsert->bindValue(3, $dataReg, PDO::PARAM_STR);
		                
		                
		                    
		                    $stmtInsert->bindValue(4, ((int)$arr['idAula']), PDO::PARAM_INT);
		                    
		                    $stmtInsert->bindValue(5, ((int)$row->idAluno), PDO::PARAM_INT);
		                    $stmtInsert->execute();
		            }
		            
		            //verifica se o aluno atual está inativo. Se tiver, insere na frequencia e Ativa-o.
		            if( $arr['status'] == 2){
		                
		                //insere aluno por aluno na frequencia com Falta
		                $stmtInsert = $pdo->prepare('INSERT INTO frequencia (status, autor, dataReg, idAula, idAluno) VALUES (?, ?, ?, ?, ?)');
		                $stmtInsert->bindValue(1, 'P', PDO::PARAM_STR);
		                $stmtInsert->bindValue(2, $user, PDO::PARAM_INT);//id do usuário logado
		                $stmtInsert->bindValue(3, $dataReg, PDO::PARAM_STR);
		                $stmtInsert->bindValue(4, ((int)$arr['idAula']), PDO::PARAM_INT);
		                $stmtInsert->bindValue(5, ((int)$arr['idAluno']), PDO::PARAM_INT);
		                $stmtInsert->execute();
		                
		                $stmtUpdate = $pdo->prepare('UPDATE alunos set status =? WHERE id =?');
		                $stmtUpdate->bindValue(1, 1, PDO::PARAM_INT);//status ativo
		                $stmtUpdate->bindValue(2, ((int)$arr['idAluno']), PDO::PARAM_INT);
		                $stmtUpdate->execute();
		            }
		            
		            

		            
		        }
		      
		        
		        
		        
		       // $arr['teste'] = 'tudo ok';
		    }
		    
		    
		}
		
		echo json_encode($arr);
	}

?>