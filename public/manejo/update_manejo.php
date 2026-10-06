<?php
/**
 * Edição de manejo (?id=).
 *
 * A planta é sempre a do registro salvo, nunca a enviada pelo formulário.
 * Redireciona para o detalhe da planta depois de salvar.
 */
require_once __DIR__ . '/../../config/db/conexao.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/tipos_manejo.php';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $conexao->prepare('SELECT m.*, s.nome AS strain_nome FROM manejo m JOIN planta p ON m.planta_id = p.id JOIN strain s ON p.strain_id = s.id WHERE m.id = ?');
$stmt->execute([$id]);
$registro = $stmt->fetch();
if (!$registro) {
    http_response_code(404);
    exit('Manejo não encontrado.');
}

// a planta vem sempre do registro salvo, nunca do formulário
$planta = ['id' => $registro['planta_id'], 'strain_nome' => $registro['strain_nome']];
$erro = '';
$manejo = ['data' => $registro['data'], 'tipo' => $registro['tipo'], 'observacoes' => $registro['observacoes']];

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
            $stmt = $conexao->prepare('UPDATE manejo SET data = ?, tipo = ?, observacoes = ? WHERE id = ?');
            $stmt->execute([$manejo['data'], $manejo['tipo'], $manejo['observacoes'] === '' ? null : $manejo['observacoes'], $id]);
            header('Location: ../planta/ver_planta.php?id=' . $planta['id']);
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $erro = 'Ocorreu um erro ao atualizar o manejo.';
        }
    }
}

$titulo = 'Editar manejo';
$acaoBotao = 'Salvar';
require __DIR__ . '/form_manejo.php';
