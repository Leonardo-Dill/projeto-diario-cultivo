<?php
// impede o acesso direto: só funciona incluído por create/update_manejo.php
if (!isset($titulo)) {
    http_response_code(404);
    exit;
}
// formulário compartilhado por create_manejo.php e update_manejo.php
// espera: $titulo, $acaoBotao, $planta, $manejo (data, tipo, observacoes), $erro e, na edição, $id
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <link rel="stylesheet" href="../css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($titulo) ?> - Diário</title>
</head>

<body>
    <header>
        <h1><?= h($titulo) ?></h1>
        <p><?= h($planta['strain_nome']) ?> - ID: <?= (int) $planta['id'] ?></p>
    </header>
    <main>
        <?php if ($erro): ?>
            <p class="erro"><?= h($erro) ?></p>
        <?php endif; ?>

        <form method="POST" class="form">
            <input type="hidden" name="planta_id" value="<?= (int) $planta['id'] ?>">
            <?php if (isset($id)): ?>
                <input type="hidden" name="id" value="<?= (int) $id ?>">
            <?php endif; ?>
            <label for="data">Data</label>
            <input type="date" id="data" name="data" value="<?= h($manejo['data']) ?>" required>
            <label for="tipo">Tipo de manejo</label>
            <input type="text" id="tipo" name="tipo" list="tipos" maxlength="255" value="<?= h($manejo['tipo']) ?>" placeholder="Ex.: Rega, Poda, Adubação" required>
            <datalist id="tipos">
                <?php foreach (TIPOS_MANEJO as $tipo): ?>
                    <option value="<?= h($tipo) ?>">
                <?php endforeach; ?>
            </datalist>
            <label for="observacoes">Observações</label>
            <textarea id="observacoes" name="observacoes" placeholder="Quantidades, produtos, o que você notou..."><?= h($manejo['observacoes']) ?></textarea>
            <button type="submit" class="btn"><?= h($acaoBotao) ?></button>
        </form>
        <div class="btn-voltar">
            <a href="../planta/ver_planta.php?id=<?= (int) $planta['id'] ?>" class="btn">Voltar</a>
        </div>
    </main>
</body>

</html>
