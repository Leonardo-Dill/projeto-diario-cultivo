<?php
require __DIR__ .'/../../config/db/conexao.php';
$id = '2';
$sql = 'SELECT * FROM strain WHERE id = ?';
try{
    $stmt = $conexao->prepare ($sql);
    $stmt->execute([
        $id
    ]);
    $strain = $stmt->fetch();
}catch(PDOException $e){
    echo "Erro ao ler o banco de dados: " . $e->getMessage();
}
echo '<br>Nome: '.$strain["nome"].'<br>Características: '.$strain["caracteristicas"].'<br>Floração: '.$strain["floracao_semanas"].' semanas';