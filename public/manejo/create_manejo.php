<?php
/**
 * Registro de manejo em uma planta (?planta_id=).
 *
 * GET: mostra o formulário. POST: valida data e tipo e grava; redireciona para
 * o detalhe da planta.
 */
require_once __DIR__ . '/../../config/db/conexao.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/tipos_manejo.php';

$planta_id = (int) ($_GET['planta_id'] ?? $_POST['planta_id'] ?? 0);

$stmt = $conexao->prepare('SELECT p.id, s.nome AS strain_nome FROM planta p JOIN strain s ON p.strain_id = s.id WHERE p.id = ?');
$stmt->execute([$planta_id]);
$planta = $stmt->fetch();
if (!$planta) {
    http_response_code(404);
    exit('Planta não encontrada.');
}

$erro = '';
$manejo = ['data' => date('Y-m-d'), 'tipo' => '', 'observacoes' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $manejo = [
        'data' => trim($_POST['data'] ?? ''),
        'tipo' => trim($_POST['tipo'] ?? ''),
        'observacoes' => trim($_POST['observacoes'] ?? ''),
    ];

    if (!dataValida($manejo['data'])) {
        $erro = 'Informe uma data válida.';
    } elseif ($manejo['tipo'] === '' || mb_strlen($manejo['tipo']) > 255) {
        $erro = 'Informe o tipo de manejo (até 255 caracteres).';
    } else {
        try {
            $stmt = $conexao->prepare('INSERT INTO manejo (planta_id, data, tipo, observacoes) VALUES (?, ?, ?, ?)');
            $stmt->execute([$planta_id, $manejo['data'], $manejo['tipo'], $manejo['observacoes'] === '' ? null : $manejo['observacoes']]);
            header('Location: ../planta/ver_planta.php?id=' . $planta_id);
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $erro = 'Ocorreu um erro ao registrar o manejo.';
        }
    }
}

$titulo = 'Registrar manejo';
$acaoBotao = 'Registrar';
require __DIR__ . '/form_manejo.php';
