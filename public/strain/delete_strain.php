<?php
require __DIR__.'/../../config/db/conexao.php';
$id = $_GET['id'];
$sql = 'DELETE FROM strain WHERE id = ?';
try{
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$id]);
}catch(PDOException $e){
    echo "Ocorreu um erro ao acessar o banco de dados: " . $e->getMessage();
}
header('Location: listar.php');