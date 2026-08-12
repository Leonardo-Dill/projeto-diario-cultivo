<?php
require __DIR__ .'/../../config/db/conexao.php';

//variáveis que receberão os dados do formulário
$nome = '';
$caracteristicas = '';
$floracao_semanas = '';

//define o comando sql
$sql = "INSERT INTO strain (nome, caracteristicas, floracao_semanas) VALUES (?, ?, ?)";
//prepara e executa o comando
$stmt = $conexao-> prepare($sql);
$stmt->execute([
    $nome, $caracteristicas, $floracao_semanas
]);
?>