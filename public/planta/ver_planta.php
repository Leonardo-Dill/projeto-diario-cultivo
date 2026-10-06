<?php
/**
 * Detalhe de uma planta (GET ?id=).
 *
 * Mostra cultivo, fase atual, dias de vida, datas das fases, prévia das fotos e
 * a lista de manejo, com os botões para registrar, editar e apagar manejo.
 */
require_once __DIR__ . '/../../config/db/conexao.php';
require_once __DIR__ . '/../../config/helpers.php';

const FASES = [
    'germinacao' => 'Germinação',
    'plantinha' => 'Plantinha',
    'vegetativo' => 'Vegetativo',
    'floracao' => 'Floração',
    'colheita' => 'Colheita',
];
const FOTOS_NA_PREVIA = 4;

/**
 * Formata uma data do banco (Y-m-d) como d/m/Y.
 *
 * @return string A data formatada, ou "—" se for nula ou vazia.
 */
function formatarData(?string $data): string
{
    return $data ? date('d/m/Y', strtotime($data)) : '—';
}

$id = (int) ($_GET['id'] ?? 0);

try {
    $stmt = $conexao->prepare('SELECT p.*, s.nome AS strain_nome, s.floracao_semanas FROM planta p JOIN strain s ON p.strain_id = s.id WHERE p.id = ?');
    $stmt->execute([$id]);
    $planta = $stmt->fetch();
    if (!$planta) {
        http_response_code(404);
        exit('Planta não encontrada.');
    }

    $stmt = $conexao->prepare('SELECT COUNT(*) FROM fotos WHERE planta_id = ?');
    $stmt->execute([$id]);
    $totalFotos = (int) $stmt->fetchColumn();

    $stmt = $conexao->prepare('SELECT imagem, data FROM fotos WHERE planta_id = ? ORDER BY data DESC, id DESC LIMIT ' . FOTOS_NA_PREVIA);
    $stmt->execute([$id]);
    $fotos = $stmt->fetchAll();

    $stmt = $conexao->prepare('SELECT id, data, tipo, observacoes FROM manejo WHERE planta_id = ? ORDER BY data DESC, id DESC');
    $stmt->execute([$id]);
    $manejos = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Ocorreu um erro ao carregar a planta.');
}

// fase atual = a fase mais avançada que já tem data preenchida
$faseAtual = 'Sem fase registrada';
foreach (FASES as $campo => $rotulo) {
    if (!empty($planta[$campo])) {
        $faseAtual = $rotulo;
    }
}

// dias de vida: da germinação até hoje, ou até a colheita se já foi colhida
$diasDeVida = null;
if (!empty($planta['germinacao'])) {
    $inicio = new DateTime($planta['germinacao']);
    $fim = !empty($planta['colheita']) ? new DateTime($planta['colheita']) : new DateTime('today');
    $diasDeVida = max(0, (int) $inicio->diff($fim)->format('%r%a'));
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <link rel="stylesheet" href="../css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planta - Diário</title>
</head>

<body>
    <header>
        <h1><?= h($planta['strain_nome']) ?> - ID: <?= (int) $planta['id'] ?></h1>
        <p><?= h($faseAtual) ?><?= $diasDeVida !== null ? ' · ' . $diasDeVida . ' dias de vida' : '' ?></p>
    </header>
    <main>
        <div class="btn-voltar">
            <a href="read_planta.php" class="btn">Voltar</a>
            <a href="update_planta.php?id=<?= (int) $planta['id'] ?>" class="btn">Editar</a>
        </div>

        <div class="card">
            <h2>Cultivo</h2>
            <p><?= nl2br(h($planta['tipo_cultivo'])) ?></p>
            <p>Strain: <?= h($planta['strain_nome']) ?><?= $planta['floracao_semanas'] ? ' (floração de ' . (int) $planta['floracao_semanas'] . ' semanas)' : '' ?></p>
            <p>Rendimento (molhado): <?= $planta['rendimento'] !== null ? h($planta['rendimento']) . ' g' : '—' ?></p>
        </div>

        <div class="card">
            <h2>Fases</h2>
            <?php foreach (FASES as $campo => $rotulo): ?>
                <p><?= $rotulo ?>: <?= formatarData($planta[$campo]) ?></p>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h2>Fotos (<?= $totalFotos ?>)</h2>
            <?php if (!$fotos): ?>
                <p>Nenhuma foto enviada ainda.</p>
            <?php else: ?>
                <div class="galeria">
                    <?php foreach ($fotos as $foto): ?>
                        <div>
                            <img src="../uploads/<?= h($foto['imagem']) ?>" alt="Foto de <?= h($foto['data']) ?>" loading="lazy">
                            <p><?= formatarData($foto['data']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <a href="../foto/galeria.php?planta_id=<?= (int) $planta['id'] ?>" class="btn">Ver galeria e enviar fotos</a>
        </div>

        <div class="card">
            <h2>Manejo (<?= count($manejos) ?>)</h2>
            <a href="../manejo/create_manejo.php?planta_id=<?= (int) $planta['id'] ?>" class="btn">Registrar manejo</a>
            <?php if (!$manejos): ?>
                <p>Nenhum manejo registrado ainda.</p>
            <?php endif; ?>
            <?php foreach ($manejos as $manejo): ?>
                <div class="manejo-item">
                    <strong><?= formatarData($manejo['data']) ?> — <?= h($manejo['tipo']) ?></strong>
                    <?php if (!empty($manejo['observacoes'])): ?>
                        <p><?= nl2br(h($manejo['observacoes'])) ?></p>
                    <?php endif; ?>
                    <div class="acoes">
                        <a href="../manejo/update_manejo.php?id=<?= (int) $manejo['id'] ?>" class="btn">Editar</a>
                        <form method="POST" action="../manejo/delete_manejo.php" onsubmit="return confirm('Apagar este manejo? Esta ação não pode ser desfeita.')">
                            <input type="hidden" name="id" value="<?= (int) $manejo['id'] ?>">
                            <input type="hidden" name="planta_id" value="<?= (int) $planta['id'] ?>">
                            <button type="submit" class="btn">Apagar</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>

</html>
