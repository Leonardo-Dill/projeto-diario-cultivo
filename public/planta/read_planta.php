<?php
require_once __DIR__ . '/../../config/db/conexao.php';
$id = $_POST['id'];
$sql = 'SELECT * FROM planta WHERE id = ?';

try {
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$id]);
    $resultado = $stmt->fetch();
} catch (PDOException $e) {
    echo 'houve um erro ao buscar no banco de dados: ' . $e->getMessage();
}
