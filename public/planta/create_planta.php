<?php
require __DIR__ . '/../../config/db/conexao.php';

$strain_id = '';
try {
    $sql = 'INSERT INTO planta (strain_id) VALUES (?)';
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$strain_id]);
} catch (PDOException $e) {
    echo "ocorreu um erro ao criar: " . $e->getMessage();
}
