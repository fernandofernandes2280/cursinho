<?php
$pdo  = new PDO('mysql:host=localhost;dbname=cursinho', 'root', 'fl4m3ng0');
$stmt = $pdo->prepare('select cidadeid, descricao from cidade where ufid=? order by descricao');
$stmt->bindValue(1, ((int)$_POST['ufid']), PDO::PARAM_INT);
$stmt->execute();
$resultCidade = $stmt->fetchAll();
$stmt->closeCursor();
echo json_encode($resultCidade);