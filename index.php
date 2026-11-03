<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'conexao.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo     = $_POST['titulo'] ?? '';
    $categoria  = $_POST['categoria'] ?? '';
    $descricao  = $_POST['descricao'] ?? '';
    $usuario_id = $_SESSION['usuario_id'];

    if (!empty($titulo) && !empty($categoria) && !empty($descricao)) {
        $sql = "INSERT INTO chamados (titulo, categoria, descricao, usuario_id, status) 
                VALUES (:titulo, :categoria, :descricao, :usuario_id, 'Aberto')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titulo'     => $titulo,
            ':categoria'  => $categoria,
            ':descricao'  => $descricao,
            ':usuario_id' => $usuario_id
        ]);

        header('Location: listar.php');
        exit;
    } else {
        $mensagem = "Preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Helpdesk - Criar Chamado</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container container-sm">
    <a href="listar.php" style="color: var(--primary); text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block; margin-bottom: 16px;">← Voltar para o Painel</a>
    <h2 style="margin-bottom: 20px;">Abrir Novo Chamado</h2>

    <?php if ($mensagem): ?>
        <div class="alert alert-error"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Título:</label>
            <input type="text" name="titulo" required placeholder="Ex: Problema no Wi-Fi">
        </div>

        <div class="form-group">
            <label>Categoria:</label>
            <select name="categoria" required>
                <option value="">Selecione...</option>
                <option value="Rede">Rede</option>
                <option value="Hardware">Hardware</option>
                <option value="Software">Software</option>
                <option value="Outro">Outro</option>
            </select>
        </div>

        <div class="form-group">
            <label>Descrição:</label>
            <textarea name="descricao" required placeholder="Descreva o problema em detalhe..." rows="4"></textarea>
        </div>

        <button type="submit" class="btn" style="width: 100%;">Guardar Chamado</button>
    </form>
</div>

</body>
</html>