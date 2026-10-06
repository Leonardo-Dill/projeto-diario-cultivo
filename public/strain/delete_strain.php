<?php
/**
 * Apaga uma strain (GET ?id=) e volta para listar.php.
 *
 * Falha se houver plantas ligadas à strain (chave estrangeira).
 */
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