<?php
require_once __DIR__ . '/../../config/db/conexao.php';

$id = (int) ($_POST['id'] ?? 0);
$planta_id = (int) ($_POST['planta_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
    try {
        $stmt = $conexao->prepare('SELECT imagem FROM fotos WHERE id = ? AND planta_id = ?');
        $stmt->execute([$id, $planta_id]);
        $imagem = $stmt->fetchColumn();

        if ($imagem !== false) {
            $stmt = $conexao->prepare('DELETE FROM fotos WHERE id = ?');
            $stmt->execute([$id]);
            // basename evita sair da pasta de uploads
            $caminho = __DIR__ . '/../uploads/' . basename($imagem);
            if (is_file($caminho)) {
                unlink($caminho);
            }
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}

header('Location: galeria.php?planta_id=' . $planta_id);
exit;
