<?php
require_once __DIR__ . '/../../config/db/conexao.php';

const PASTA_UPLOADS = __DIR__ . '/../uploads/';
const TAMANHO_MAXIMO = 8 * 1024 * 1024; // 8 MB
const TIPOS_PERMITIDOS = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

$planta_id = (int) ($_GET['planta_id'] ?? $_POST['planta_id'] ?? 0);

$stmt = $conexao->prepare('SELECT p.id, s.nome AS strain_nome FROM planta p JOIN strain s ON p.strain_id = s.id WHERE p.id = ?');
$stmt->execute([$planta_id]);
$planta = $stmt->fetch();
if (!$planta) {
    http_response_code(404);
    exit('Planta não encontrada.');
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $arquivo = $_FILES['foto'] ?? null;
    $data = $_POST['data'] ?? '';
    $dataValida = DateTime::createFromFormat('Y-m-d', $data);

    if (!$arquivo || $arquivo['error'] === UPLOAD_ERR_NO_FILE) {
        $erro = 'Escolha uma foto.';
    } elseif ($arquivo['error'] === UPLOAD_ERR_INI_SIZE || $arquivo['error'] === UPLOAD_ERR_FORM_SIZE || $arquivo['size'] > TAMANHO_MAXIMO) {
        $erro = 'A foto é grande demais (máximo 8 MB).';
    } elseif ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $erro = 'Falha ao enviar a foto. Tente novamente.';
    } elseif (!$dataValida || $dataValida->format('Y-m-d') !== $data) {
        $erro = 'Informe uma data válida.';
    } else {
        // confere o tipo real do arquivo, sem confiar na extensão enviada
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
        if (!isset(TIPOS_PERMITIDOS[$mime]) || @getimagesize($arquivo['tmp_name']) === false) {
            $erro = 'Formato não permitido. Use JPG, PNG ou WEBP.';
        } else {
            $nome = bin2hex(random_bytes(16)) . '.' . TIPOS_PERMITIDOS[$mime];
            if (!move_uploaded_file($arquivo['tmp_name'], PASTA_UPLOADS . $nome)) {
                $erro = 'Não foi possível salvar a foto.';
            } else {
                try {
                    $stmt = $conexao->prepare('INSERT INTO fotos (planta_id, data, imagem) VALUES (?, ?, ?)');
                    $stmt->execute([$planta_id, $data, $nome]);
                    header('Location: galeria.php?planta_id=' . $planta_id);
                    exit;
                } catch (PDOException $e) {
                    unlink(PASTA_UPLOADS . $nome);
                    error_log($e->getMessage());
                    $erro = 'Erro ao registrar a foto.';
                }
            }
        }
    }
}

$stmt = $conexao->prepare('SELECT id, data, imagem FROM fotos WHERE planta_id = ? ORDER BY data DESC, id DESC');
$stmt->execute([$planta_id]);
$fotos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <link rel="stylesheet" href="../css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fotos - Diário</title>
</head>

<body>
    <header>
        <h1>Fotos - <?= htmlspecialchars($planta['strain_nome']) ?> (ID: <?= $planta['id'] ?>)</h1>
    </header>
    <main>
        <div class="btn-voltar">
            <a href="../planta/read_planta.php" class="btn">Voltar</a>
        </div>

        <?php if ($erro): ?>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form">
            <input type="hidden" name="planta_id" value="<?= $planta['id'] ?>">
            <label for="data">Data da foto</label>
            <input type="date" id="data" name="data" value="<?= date('Y-m-d') ?>" required>
            <label for="foto">Foto (JPG, PNG ou WEBP, até 8 MB)</label>
            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" required>
            <button type="submit" class="btn">Enviar foto</button>
        </form>

        <?php if (!$fotos): ?>
            <p class="vazio">Nenhuma foto enviada ainda.</p>
        <?php endif; ?>
        <div class="galeria">
            <?php foreach ($fotos as $foto): ?>
                <div class="card">
                    <a href="../uploads/<?= htmlspecialchars($foto['imagem']) ?>" target="_blank">
                        <img src="../uploads/<?= htmlspecialchars($foto['imagem']) ?>" alt="Foto de <?= htmlspecialchars($foto['data']) ?>" loading="lazy">
                    </a>
                    <p><?= htmlspecialchars(date('d/m/Y', strtotime($foto['data']))) ?></p>
                    <form method="POST" action="delete_foto.php" onsubmit="return confirm('Apagar esta foto? Esta ação não pode ser desfeita.')">
                        <input type="hidden" name="id" value="<?= $foto['id'] ?>">
                        <input type="hidden" name="planta_id" value="<?= $planta['id'] ?>">
                        <button type="submit" class="btn">Apagar</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>

</html>
