<?php
require_once __DIR__ . '/../../config/db/conexao.php';

$sql = 'SELECT p.*, s.nome as strain_nome FROM planta p JOIN strain s ON p.strain_id = s.id';
try{
    $stmt = $conexao->query($sql);
    $plantas = $stmt->fetchAll();
}catch(PDOException $e){
    echo "Erro ao ler o banco de dados: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="stylesheet" href="../css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plantas - Diário</title>
</head>
<body>
    <header>
        <h1>Minhas plantas</h1>
    </header>
    <main>
        <a href="create_planta.php" class="btn">Cadastrar nova planta</a>
        <a href="../index.php" class="btn">Voltar</a>
        <?php foreach ($plantas as $planta): ?>
            <div class="card">
                <h2><?= $planta['strain_nome'] ?> - ID: <?= $planta['id'] ?></h2>
                <a href="update_planta.php?id=<?= $planta['id'] ?>" class="btn">Editar</a>
                <a href="delete_planta.php?id=<?= $planta['id'] ?>" class="btn" onclick="return confirm('Tem certeza que quer deletar essa planta? Esta ação não pode ser desfeita.')">Apagar</a>
            </div>
        <?php endforeach; ?>
    </main>
</body>
</html>