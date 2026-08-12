<?php
include_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

try{
    $dsn = 'mysql:host='.$_ENV["db_host"].';dbname='.$_ENV["db_name"].';charset=utf8mb4';
    $conexao = new PDO($dsn, $_ENV["db_user"],$_ENV["db_pass"]);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexão realizada com sucesso";
}
catch(PDOException $e){
    echo "Ocorreu um erro ao conectar com o banco: " . $e->getMessage();
    die;
}