<?php
require_once __DIR__ . '/../../config/db/conexao.php';

$planta = $_POST['planta_id'];
$data = $_POST['data'];
$tipo = $_POST['tipo'];
$obs = $_POST['observacoes'];

$sql = 'INSERT INTO manejo (planta_id, data, tipo, observacoes) VALUES (?, ?, ?, ?)';

try{
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$planta, $data, $tipo, $obs]);
} catch(PDOException $e) {
    echo 'ocorreu um erro ao registrar: ' . $e->getMessage();
}