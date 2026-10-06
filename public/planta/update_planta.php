<?php
/**
 * Edição de planta.
 *
 * GET ?id=: mostra o formulário preenchido. POST: valida strain, datas e
 * rendimento e atualiza todos os campos; campo vazio vira NULL (permite limpar
 * um valor). Redireciona para read_planta.php.
 */
require __DIR__ . "/../../config/db/conexao.php";

const CAMPOS_DATA = [
    'germinacao' => 'Dia da germinação',
    'plantinha' => 'Fase de plantinha',
    'vegetativo' => 'Dia da fase vegetativa',
    'floracao' => 'Dia da fase floração',
    'colheita' => 'Dia da colheita',
];

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $conexao->prepare('SELECT * FROM planta WHERE id = ?');
$stmt->execute([$id]);
$planta = $stmt->fetch();
if (!$planta) {
    http_response_code(404);
    exit('Planta não encontrada.');
}

$strains = $conexao->query('SELECT id, nome FROM strain ORDER BY nome')->fetchAll();
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $strain_id = (int) ($_POST['strain_id'] ?? 0);
    $tipo_cultivo = trim($_POST['tipo_cultivo'] ?? '');
    $rendimento = trim($_POST['rendimento'] ?? '');

    // campo vazio vira null, assim dá para limpar uma data ou o rendimento
    $datas = [];
    foreach (CAMPOS_DATA as $campo => $rotulo) {
        $valor = trim($_POST[$campo] ?? '');
        if ($valor === '') {
            $datas[$campo] = null;
            continue;
        }
        $d = DateTime::createFromFormat('Y-m-d', $valor);
        if (!$d || $d->format('Y-m-d') !== $valor) {
            $erro = "Data inválida em: $rotulo.";
            break;
        }
        $datas[$campo] = $valor;
    }

    $strainExiste = in_array($strain_id, array_column($strains, 'id'));

    if (!$erro && $tipo_cultivo === '') {
        $erro = 'Informe o tipo de cultivo.';
    } elseif (!$erro && !$strainExiste) {
        $erro = 'Escolha uma strain válida.';
    } elseif (!$erro && $rendimento !== '' && (!is_numeric($rendimento) || $rendimento < 0)) {
        $erro = 'O rendimento deve ser um número maior ou igual a zero.';
    }

    if (!$erro) {
        $sql = 'UPDATE planta SET strain_id = ?, tipo_cultivo = ?, germinacao = ?, plantinha = ?, vegetativo = ?, floracao = ?, colheita = ?, rendimento = ? WHERE id = ?';
        try {
            $stmt = $conexao->prepare($sql);
            $stmt->execute([
                $strain_id,
                $tipo_cultivo,
                $datas['germinacao'],
                $datas['plantinha'],
                $datas['vegetativo'],
                $datas['floracao'],
                $datas['colheita'],
                $rendimento === '' ? null : $rendimento,
                $id,
            ]);
            header('Location: read_planta.php');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $erro = 'Ocorreu um erro ao atualizar a planta.';
        }
    }

    // mantém no formulário o que o usuário digitou
    $planta = array_merge($planta, $_POST);
}

/** Escapa um valor para exibir em HTML. */
function h($valor): string
{
    return htmlspecialchars((string) $valor);
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <link rel="stylesheet" href="../css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Planta - Diário</title>
</head>

<body>
    <header>
        <h1>Editar Planta (ID: <?= (int) $planta['id'] ?>)</h1>
    </header>
    <main>
        <?php if ($erro): ?>
            <p class="erro"><?= h($erro) ?></p>
        <?php endif; ?>

        <form method="POST" class="form">
            <input type="hidden" name="id" value="<?= $id ?>">
            <label for="tipo_cultivo">Tipo de cultivo e ambiente</label>
            <input type="text" id="tipo_cultivo" name="tipo_cultivo" value="<?= h($planta['tipo_cultivo']) ?>" required>
            <?php foreach (CAMPOS_DATA as $campo => $rotulo): ?>
                <label for="<?= $campo ?>"><?= $rotulo ?></label>
                <input type="date" id="<?= $campo ?>" name="<?= $campo ?>" value="<?= h($planta[$campo]) ?>">
            <?php endforeach; ?>
            <label for="rendimento">Rendimento em gramas (molhado)</label>
            <input type="number" id="rendimento" name="rendimento" step="any" min="0" value="<?= h($planta['rendimento']) ?>">
            <label for="strain_id">Strain</label>
            <select name="strain_id" id="strain_id" required>
                <option value="">Escolha uma strain</option>
                <?php foreach ($strains as $strain): ?>
                    <option value="<?= $strain['id'] ?>" <?= (int) $planta['strain_id'] === (int) $strain['id'] ? 'selected' : '' ?>><?= h($strain['nome']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Salvar</button>
        </form>
        <div class="btn-voltar">
            <a href="read_planta.php" class="btn">Voltar</a>
        </div>
    </main>
</body>

</html>
