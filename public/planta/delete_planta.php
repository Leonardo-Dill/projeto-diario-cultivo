<?php
require_once __DIR__ . '/../../config/db/conexao.php';
$id = $_POST['id'];
$sql = 'DELETE FROM planta WHERE id = ?';
try{
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$id]);
    echo 'apagado com sucesso!';
}catch(PDOException $e){
    echo 'ocorreu um erro ao apagar: ' . $e->getMessage();
}