<?php
session_start();

// Bloquear acesso não autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'conexao.php';

//  Capturar e validar se o ID é um número inteiro
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    // Executar eliminação de forma segura com Prepared Statement
    $sql = "DELETE FROM chamados WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
}

//  Redirecionar de volta para o painel
header('Location: listar.php');
exit;
?>