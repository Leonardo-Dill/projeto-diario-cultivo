<?php
require __DIR__ . '/../../config/db/conexao.php';
$id = $_GET['id'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $caracteristicas = $_POST['caracteristicas'];
    $floracao_semanas = $_POST['floracao_semanas'];
    $id = $_POST['id'];
    try {
        //comando sql
        $sql = 'UPDATE strain SET nome = ?, caracteristicas = ?, floracao_semanas = ? WHERE id = ?';
        $stmt = $conexao->prepare($sql);
        $stmt->execute([
            $nome,
            $caracteristicas,
            $floracao_semanas,
            $id
        ]);
        header('Location: listar.php');
        exit;
    } catch (PDOException $e) {
        echo "Erro ao atualizar: " . $e->getMessage();
    }
}
$sql = 'SELECT * FROM strain WHERE id = ?';
$stmt = $conexao->prepare($sql);
$stmt->execute([$id]);
$strain = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <link rel="stylesheet" href="../css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar - Diário</title>
</head>

<body>
    <header>
        <h1>Editar Strain</h1>
    </header>
    <main>
        <form method="post" class="form">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="text" name="nome" value="<?= $strain['nome'] ?>" required>
            <textarea name="caracteristicas" required><?= $strain['caracteristicas'] ?></textarea>
            <input type="number" name="floracao_semanas" value="<?= $strain['floracao_semanas'] ?>" required>
            <button type="submit" class="btn">Salvar</button>
        </form>
        <div class="btn-voltar">
            <a href="listar.php" class="btn">Voltar</a>
        </div>
    </main>

</body>

</html>