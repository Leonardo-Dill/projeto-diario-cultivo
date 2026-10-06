<?php
/**
 * Apaga um manejo. Só aceita POST (id e planta_id).
 *
 * A condição planta_id impede apagar o manejo de outra planta.
 */
require_once __DIR__ . '/../../config/db/conexao.php';

$id = (int) ($_POST['id'] ?? 0);
$planta_id = (int) ($_POST['planta_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
    try {
        $stmt = $conexao->prepare('DELETE FROM manejo WHERE id = ? AND planta_id = ?');
        $stmt->execute([$id, $planta_id]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}

header('Location: ../planta/ver_planta.php?id=' . $planta_id);
exit;
