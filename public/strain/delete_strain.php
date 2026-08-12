<?php
require __DIR__.'/../../config/db/conexao.php';
$id = '2';
$sql = 'DELETE FROM strain WHERE id = ?';
try{
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$id]);
}catch(PDOException $e){
    echo "Ocorreu um erro ao acessar o banco de dados: " . $e->getMessage();
}