<?php
require __DIR__ .'/../../config/db/conexao.php';

//variáveis
$nome = "teste";
$caracteristicas = "testando as características";
$floracao_semanas = "9";
$id = '2';
try {
//comando sql
$sql = 'UPDATE strain SET nome = ?, caracteristicas = ?, floracao_semanas = ? WHERE id = ?';
$stmt = $conexao-> prepare($sql);
$stmt->execute([
    $nome, $caracteristicas, $floracao_semanas, $id
]);
}catch (PDOException $e) {
    echo "Erro ao atualizar: " . $e->getMessage();
}