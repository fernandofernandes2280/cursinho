<?php 

$pdo  = new PDO('mysql:host=localhost;dbname=cursinho', 'root', 'fl4m3ng0');


	if(isset($_POST['send'])){

		$arr= array();
		//define a data
		$dataAtual = date('Y-m-d H:i:s');
		
		//verfica se o aluno está exsite e se está ativo
		$stmtAluno = $pdo->prepare('SELECT id as idAluno FROM alunos WHERE matricula= "'.$_POST['matricula'].'"  ');
	//	$stmtAluno->bindValue(1, ($_POST['matricula']), PDO::PARAM_STR);
		//$stmtAluno->bindValue(2, 1, PDO::PARAM_INT);//1 - status ativo
		$stmtAluno->execute();
		
		
		//achou aluno
		if(($stmtAluno) && ($stmtAluno->rowCount() != 0)){
		    $row_aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);
		    $_POST['idAluno'] = $row_aluno['idAluno'];
		    //aluno existe 
		    $arr['successExiste'] = true;
		    
		    //verfica se aluno está ativo
		    $stmtAlunoAtivo = $pdo->prepare('SELECT id, nome, matricula, foto FROM alunos WHERE id=? AND status=?');
		    $stmtAlunoAtivo->bindValue(1, ((int)$_POST['idAluno']), PDO::PARAM_INT);
		    $stmtAlunoAtivo->bindValue(2, 1, PDO::PARAM_INT);//1 - status ativo
		    $stmtAlunoAtivo->execute();
		    
		    //achou aluno Ativo
		    if(($stmtAlunoAtivo) && ($stmtAlunoAtivo->rowCount() != 0)){
		        //aluno está Ativo
		        $arr['successAtivo'] = true;
		        
		        //dados do aluno Ativo
		        $row_usuario = $stmtAlunoAtivo->fetch(PDO::FETCH_ASSOC);
		        $arr['idAluno'] = $row_usuario['id'];
		        $arr['nome'] = $row_usuario['nome'];
		        $arr['matricula'] = $row_usuario['matricula'];
		        $arr['foto'] = $row_usuario['foto'];
		        
		        
		        //verifica se o aluno está na frequencia
		        $stmt = $pdo->prepare('SELECT  F.id as idFreq, F.status as status, A.id as idAluno, A.matricula as matricula, A.nome as nome, A.foto as foto, L.diaSemana as diaSemana FROM frequencia F INNER JOIN alunos A ON F.idAluno = A.id INNER JOIN aulas L ON F.idAula=L.id WHERE F.idAluno=? and F.idAula =? AND A.status=? ');
		        $stmt->bindValue(1, ((int)$_POST['idAluno']), PDO::PARAM_INT);
		        $stmt->bindValue(2, ((int)$_POST['idAula']), PDO::PARAM_INT);
		        $stmt->bindValue(3, 1, PDO::PARAM_INT);//1 - status ativo
		        $stmt->execute();
		        
		        //verifica se o aluno está na frequencia da aula
		        if(($stmt) && ($stmt->rowCount() != 0)){//aluno está na vinculado na aula
		            //aluno vinculado na frequencia
		            $arr['successVinculo'] = true;
		            
		            $row_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
		            $arr['idAluno'] = $row_usuario['idAluno'];
		            $arr['nome'] = $row_usuario['nome'];
		            $arr['matricula'] = $row_usuario['matricula'];
		            $arr['foto'] = $row_usuario['foto'];
		            //armazena id da frequencia
		            $arr['idFreq'] = $row_usuario['idFreq'];
		            
		            //verifica se o aluno já está com a presença confirmada
		            if($row_usuario['status'] == 'P'){
		                //aluno ja registrou sua presença
		                $arr['successPresenca'] = true;
		                
		            }else{ //se não tiver atualiza a presença
		            
    		            $stmtUpdate = $pdo->prepare('UPDATE frequencia set status =? , autor =?, dataReg =?  WHERE id =?');
    		            $stmtUpdate->bindValue(1, 'P', PDO::PARAM_STR);
    		            $stmtUpdate->bindValue(2, 2, PDO::PARAM_INT);//id do usuário logado
    		            $stmtUpdate->bindValue(3, $dataAtual, PDO::PARAM_STR);
    		            $stmtUpdate->bindValue(4, ((int)$arr['idFreq']), PDO::PARAM_INT);
    		            if($stmtUpdate->execute()){
    		                $arr['successUpdate'] = true;
    		            }else{
    		                $arr['successUpdate'] = false;
    		            }
		            
		            }
		            
		        }else{
		            //aluno não vinculado na frequencia
		            $arr['successVinculo'] = false;
		            
		            //insere o aluno na frequencia da Aula
		            $stmtInsert = $pdo->prepare('INSERT INTO frequencia (status, autor, dataReg, idAula, idAluno) VALUES (?, ?, ?, ?, ?)');
		            $stmtInsert->bindValue(1, 'P', PDO::PARAM_STR);
		            $stmtInsert->bindValue(2, 2, PDO::PARAM_INT);//id do usuário logado
		            $stmtInsert->bindValue(3, $dataAtual, PDO::PARAM_STR);
		            $stmtInsert->bindValue(4, ((int)$_POST['idAula']), PDO::PARAM_INT);
		            $stmtInsert->bindValue(5, ((int)$_POST['idAluno']), PDO::PARAM_INT);
		            if($stmtInsert->execute()){
		                //Aluno inserido com sucesso na frequencia
		                $arr['successInsert'] = true;
		                
		                
		                
		            }else{
		                $arr['successInsert'] = false;
		            }
		            
		            
		            
		            
		        }
		        
		        
		        
		    }else{
		        //aluno não está Inativo
		        $arr['successAtivo'] = false;
		        //verfica se aluno está ativo
		        $stmtAlunoAtivo = $pdo->prepare('SELECT id, nome, matricula, foto FROM alunos WHERE id=? AND status=?');
		        $stmtAlunoAtivo->bindValue(1, ((int)$_POST['idAluno']), PDO::PARAM_INT);
		        $stmtAlunoAtivo->bindValue(2, 2, PDO::PARAM_INT);//2 - status Inativo
		        $stmtAlunoAtivo->execute();
		        //dados do aluno Inativo
		        $row_usuario = $stmtAlunoAtivo->fetch(PDO::FETCH_ASSOC);
		        $arr['idAluno'] = $row_usuario['id'];
		        $arr['nome'] = $row_usuario['nome'];
		        $arr['matricula'] = $row_usuario['matricula'];
		        $arr['foto'] = $row_usuario['foto'];
		    }
		    
		    
		    
		    

		    
		}else{
		    //aluno não existe
		    $arr['successExiste'] = false;
		}
		
		echo json_encode($arr);
	}

?>