<?php
require __DIR__ . "/../../config/db/conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // germinacao recebe valor vindo do formulário via POST, se não houver dados vindo do formulário "??" vai receber uma string vazia: '';
    $germinacao = $_POST['germinacao'] ?? '';
    $plantinha = $_POST['plantinha'] ?? '';
    $vegetativo = $_POST['vegetativo'] ?? '';
    $floracao = $_POST['floracao'] ?? '';
    $colheita = $_POST['colheita'] ?? '';
    $rendimento = $_POST['rendimento'] ?? '';
    $id = $_POST['id'] ?? '';
    //vai guardar o nome dos campos para o comando sql
    $campos = [];
    //vai guardar os valores dos campos guardados em $campos
    $valores = [];

    if (!empty($germinacao)) {
        $campos[] = 'germinacao = ?';
        $valores[] = $germinacao;
    }
    if (!empty($plantinha)) {
        $campos[] = 'plantinha = ?';
        $valores[] = $plantinha;
    }
    if (!empty($vegetativo)) {
        $campos[] = 'vegetativo = ?';
        $valores[] = $vegetativo;
    }
    if (!empty($floracao)) {
        $campos[] = 'floracao = ?';
        $valores[] = $floracao;
    }
    if (!empty($colheita)) {
        $campos[] = 'colheita = ?';
        $valores[] = $colheita;
    }
    if (!empty($rendimento)) {
        $campos[] = 'rendimento = ?';
        $valores[] = $rendimento;
    }
    if (!empty($campos)) {
        $sql = 'UPDATE planta SET ' . implode(', ', $campos) . ' WHERE id = ?';
        $valores[] = $id;
        try {
            $stmt = $conexao->prepare($sql);
            $stmt->execute($valores);
        } catch (PDOException $e) {
            echo 'Ocorreu um erro ao atualizar: ' . $e->getMessage();
        }
    } else {
        echo "você precisa preencher pelo menos um campo pra atualizar.";
    }
}
?>
