<?php
require __DIR__ .'/../../config/db/conexao.php';
$sql = 'SELECT * FROM strain';
try{
    $stmt = $conexao->query($sql);
    $strains = $stmt->fetchAll();
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
    <title>Strains - Diário</title>
</head>
<body>
    <header>
        <h1>Strains cadastradas</h1>
    </header>
    <main>
        <a href="create_strain.php" class="btn">Cadastrar nova strain</a>
        <a href="../index.php" class="btn">Voltar</a>
        <?php foreach ($strains as $strain): ?>
            <div class="card">
                <h2><?= $strain['nome'] ?></h2>
                <p> <?= $strain['caracteristicas'] ?></p>
                <p>Floração: <?= $strain['floracao_semanas'] ?> semanas</p>
                <a href="update_strain.php?id=<?= $strain['id'] ?>" class="btn">Editar</a>
                <a href="delete_strain.php?id=<?= $strain['id'] ?>" class="btn" onclick="return confirm('Tem certeza que quer deletar essa strain? Esta ação não pode ser desfeita.')">Apagar</a>
            </div>
        <?php endforeach; ?>
    </main>
</body>
</html>

