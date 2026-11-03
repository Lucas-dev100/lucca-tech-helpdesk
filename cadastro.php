<?php
require_once 'conexao.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (!empty($nome) && !empty($email) && !empty($senha)) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome'  => $nome,
                ':email' => $email,
                ':senha' => $senha_hash
            ]);
            
            header('Location: login.php?registado=1');
            exit;
        } catch (PDOException $e) {
            $mensagem = "Erro: Este e-mail já está em uso.";
        }
    } else {
        $mensagem = "Preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lucca Tech - Criar Conta</title>
    <link rel="stylesheet" href="css/style.css?v=3">
</head>
<body>

<div style="width: 100%; display: flex; justify-content: center; align-items: center; min-height: 100vh;">
    <div class="modal-content" style="max-width: 420px; width: 100%;">
        <div class="brand" style="text-align: center; margin-bottom: 24px;">
            <div class="brand-logo" style="margin: 0 auto 12px auto;">LT</div>
            <h1>Lucca<span>Tech</span> Support</h1>
            <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Criação de nova conta corporativa</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="alert alert-error" style="background: rgba(248, 113, 113, 0.15); color: #f87171; padding: 10px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; border: 1px solid rgba(248, 113, 113, 0.3);">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nome Completo:</label>
                <input type="text" name="nome" required placeholder="Ex: João Silva">
            </div>

            <div class="form-group">
                <label>E-mail Corporativo:</label>
                <input type="email" name="email" required placeholder="seu@luccatech.com">
            </div>

            <div class="form-group">
                <label>Palavra-passe:</label>
                <input type="password" name="senha" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Criar Conta</button>
        </form>

        <p style="margin-top: 20px; text-align: center; font-size: 14px; color: var(--text-muted);">
            Já tem conta? <a href="login.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Iniciar Sessão</a>
        </p>
    </div>
</div>

</body>
</html>