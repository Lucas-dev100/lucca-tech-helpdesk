<?php
session_start();

// Bloquear acesso não autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';

    // Lista de status permitidos (Whitelist)
    $status_permitidos = ['Aberto', 'Em Andamento', 'Fechado'];

    if ($id && in_array($status, $status_permitidos)) {
        $sql = "UPDATE chamados SET status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':status' => $status,
            ':id'     => $id
        ]);
    }
}

header('Location: listar.php');
exit;
?>