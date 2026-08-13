<?php
require_once __DIR__ . '/../../config/db/conexao.php';
$id = $_POST['id'];
$data = $_POST['data'];
$tipo = $_POST['tipo'];
$obs = $_POST['observacoes'];
$campos = [];
$valores = [];
if(!empty($data)){
    $campos[] = 'data = ?';
    $valores[] = $data;
}
if(!empty($tipo)){
    $campos[] = 'tipo = ?';
    $valores[] = $tipo;
}
if(!empty($obs)){
    $campos[] = 'observacoes = ?';
    $valores[] = $obs;
}
$sql = 'UPDATE manejo SET ' . implode(', ', $campos) . ' WHERE id = ?';
$valores[] = $id;

try{
    $stmt = $conexao->prepare($sql);
    $stmt->execute($valores);
}catch(PDOException $e){
    echo 'erro ao atualizar: ' . $e->getMessage();
}