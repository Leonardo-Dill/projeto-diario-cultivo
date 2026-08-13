<?php
require_once __DIR__ . '/../../config/db/conexao.php';
$id = $_POST['id'];
$sql = 'SELECT * FROM manejo WHERE id = ?';
try{
    $stmt = $conexao->prepare($sql);
    $stmt->execute($id);
    $resultado = $stmt->fetch();
}catch(PDOException $e){
    echo 'ocorreu um erro: ' . $e->getMessage();
}