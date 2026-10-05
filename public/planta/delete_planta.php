<?php
require_once __DIR__ . '/../../config/db/conexao.php';
$id = $_GET['id'] ?? '';

try {
    // guarda os nomes das fotos: o ON DELETE CASCADE apaga os registros, mas não os arquivos
    $stmt = $conexao->prepare('SELECT imagem FROM fotos WHERE planta_id = ?');
    $stmt->execute([$id]);
    $imagens = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $conexao->prepare('DELETE FROM planta WHERE id = ?');
    $stmt->execute([$id]);

    foreach ($imagens as $imagem) {
        $caminho = __DIR__ . '/../uploads/' . basename($imagem);
        if (is_file($caminho)) {
            unlink($caminho);
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}
header('Location: read_planta.php');
exit;
