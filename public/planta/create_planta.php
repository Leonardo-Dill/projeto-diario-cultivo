<?php
require __DIR__ . '/../../config/db/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $strain_id = $_POST['strain_id'];
    $tipo_cultivo = $_POST['tipo_cultivo'];
    $germinacao = $_POST['germinacao'] ?? null;
    $plantinha = $_POST['plantinha'] ?? null;
    $vegetativo = $_POST['vegetativo'] ?? null;
    $floracao = $_POST['floracao'] ?? null;
    $colheita = $_POST['colheita'] ?? null;
    $rendimento = $_POST['rendimento'] ?? null;

    $sql = 'INSERT INTO planta (strain_id, tipo_cultivo, germinacao, plantinha, vegetativo, floracao, colheita, rendimento) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

    try {
        $stmt = $conexao->prepare($sql);
        $stmt->execute([$strain_id, $tipo_cultivo, $germinacao, $plantinha, $vegetativo, $floracao, $colheita, $rendimento]);
        header('Location: read_planta.php');

    } catch (PDOException $e) {
        echo "Erro ao criar planta: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <link rel="stylesheet" href="../css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Planta - Diário</title>
</head>

<body>
    <header>
        <h1>Cadastrar nova Planta</h1>
    </header>
    <main>
        <form method="POST" class="form">
            <input type="text" name="tipo_cultivo" placeholder="Tipo de cultivo e ambiente" required>
            <label for="germinacao">Dia da germinação</label>
            <input type="date" id="germinacao" name="germinacao">
            <label for="plantinha">Fase de plantinha</label>
            <input type="date" id="plantinha" name="plantinha">
            <label for="vegetativo">Dia da fase vegetativa</label>
            <input type="date" id="vegetativo" name="vegetativo">
            <label for="floracao">Dia da fase floração</label>
            <input type="date" id="floracao" name="floracao">
            <label for="colheita">Dia da colheita</label>
            <input type="date" id="colheita" name="colheita">
            <input type="number" name="rendimento" placeholder="Rendimento em gramas (molhado)">
            <label for="strain_id">Escolha a strain</label>
            <select name="strain_id" id="strain_id" required>
                <option value="">Escolha uma strain</option>
                <?php
                $sql = 'SELECT id, nome FROM strain';
                $resp = $conexao->query($sql);
                $strains = $resp->fetchAll();
                foreach ($strains as $strain): ?>
                    <option value="<?= $strain['id'] ?>"><?= $strain['nome'] ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn">Salvar</button>
        </form>
        <div class="btn-voltar">
            <a href="listar.php" class="btn">Voltar</a>
        </div>
    </main>
</body>

</html>