<?php
require __DIR__ . '/../../config/db/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $caracteristicas = $_POST['caracteristicas'];
    $floracao_semanas = $_POST['floracao_semanas'];

    //define o comando sql
    $sql = "INSERT INTO strain (nome, caracteristicas, floracao_semanas) VALUES (?, ?, ?)";
    try {
        $stmt = $conexao->prepare($sql);
        $stmt->execute([$nome, $caracteristicas, $floracao_semanas]);
        header('Location: listar.php');
        exit;
    } catch (PDOException $e) {
        echo "Erro ao criar nova strain: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="stylesheet" href="../css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Strain - Diário</title>
</head>
<body>
    <header>
        <h1>Cadastrar nova Strain</h1>
    </header>
    <main>
        <form method="POST" class="form">
            <input type="text" name="nome" placeholder="Nome da Strain" required>
            <textarea name="caracteristicas" placeholder="Características" required></textarea>
            <input type="number" name="floracao_semanas" placeholder="Semanas de Floração" required>
            <button type="submit" class="btn">Salvar</button>
        </form>
        <div class="btn-voltar">
        <a href="listar.php" class="btn">Voltar</a>
        </div>
    </main>
</body>
</html>
