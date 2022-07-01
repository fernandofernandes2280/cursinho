<?php 

$pdo  = new PDO('mysql:host=localhost;dbname=cursinho', 'root', 'fl4m3ng0');


	if(isset($_POST['send'])){

		$arr= array();
		//define a data
		$dataAtual = date('Y-m-d H:i:s');
		
		//verfica se o aluno está exsite e se está ativo
		$stmtAluno = $pdo->prepare('SELECT id as idAluno, matricula, nome,foto FROM alunos WHERE matricula= "'.$_POST['matricula'].'"  ');
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
		    
		    //aluno existe 
		    $arr['successExiste'] = true;
		    
		    //verifica se o aluno está na frequencia
		    $stmt = $pdo->prepare('SELECT  F.id as idFreq, F.status as status, A.id as idAluno, A.matricula as matricula, A.nome as nome, A.foto as foto, L.diaSemana as diaSemana FROM frequencia F INNER JOIN alunos A ON F.idAluno = A.id INNER JOIN aulas L ON F.idAula=L.id WHERE F.idAluno=? and F.idAula =? ');
		    $stmt->bindValue(1, ((int)$arr['idAluno']), PDO::PARAM_INT);
		    $stmt->bindValue(2, ((int)$_POST['idAula']), PDO::PARAM_INT);
		    $stmt->execute();
		    //verifica se o aluno está na frequencia da aula
		    if(($stmt) && ($stmt->rowCount() != 0)){//aluno está vinculado na aula
		        //aluno vinculado na frequencia
		        $arr['successVinculo'] = true;
		        $row_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
		        
		      
		        //armazena id da frequencia
		        $arr['idFreq'] = $row_usuario['idFreq'];
		        
		        //verifica se o aluno já está com a presença confirmada
		        if($row_usuario['status'] == 'P'){
		            //aluno ja registrou sua presença
		            $arr['successPresenca'] = true;
		            
		        }else{ //se não tiver atualiza a presença
		            
		            //verifica se a sessao não está ativa
		            if(session_status() != PHP_SESSION_ACTIVE ){
		                session_start();
		            }
		            $user = $_SESSION['usuario']['id'];
		            
		            $stmtUpdate = $pdo->prepare('UPDATE frequencia set status =? , autor =?, dataReg =?  WHERE id =?');
		            $stmtUpdate->bindValue(1, 'P', PDO::PARAM_STR);
		            $stmtUpdate->bindValue(2, $user, PDO::PARAM_INT);//id do usuário logado
		            $stmtUpdate->bindValue(3, $dataAtual, PDO::PARAM_STR);
		            $stmtUpdate->bindValue(4, ((int)$arr['idFreq']), PDO::PARAM_INT);
		            $stmtUpdate->execute();
		        }
		    }else{
		        //aluno não vinculado na frequencia
		        $arr['successVinculo'] = false;
		        
		        //verifica se a sessao não está ativa
		        if(session_status() != PHP_SESSION_ACTIVE ){
		            session_start();
		        }
		        $user = $_SESSION['usuario']['id'];
		        
		        //insere o aluno na frequencia da Aula
		        $stmtInsert = $pdo->prepare('INSERT INTO frequencia (status, autor, dataReg, idAula, idAluno) VALUES (?, ?, ?, ?, ?)');
		        $stmtInsert->bindValue(1, 'P', PDO::PARAM_STR);
		        $stmtInsert->bindValue(2, $user, PDO::PARAM_INT);//id do usuário logado
		        $stmtInsert->bindValue(3, $dataAtual, PDO::PARAM_STR);
		        $stmtInsert->bindValue(4, ((int)$_POST['idAula']), PDO::PARAM_INT);
		        $stmtInsert->bindValue(5, ((int)$arr['idAluno']), PDO::PARAM_INT);
		        if($stmtInsert->execute()){
		            //Aluno inserido com sucesso na frequencia
		            $arr['successInsert'] = true;
		            
		            //ATIVA O ALUNO SE ESTIVER INATIVO
		            $stmtUpdate = $pdo->prepare('UPDATE alunos set status =? WHERE id =?');
		            $stmtUpdate->bindValue(1, 1, PDO::PARAM_INT);//status ativo
		            $stmtUpdate->bindValue(2, ((int)$arr['idAluno']), PDO::PARAM_INT);
		            $stmtUpdate->execute();
		        }else{
		            $arr['successInsert'] = false;
		        }
		           }
		}else{
		    //aluno não existe
		    $arr['successExiste'] = false;
		}
		echo json_encode($arr);
	}

?>