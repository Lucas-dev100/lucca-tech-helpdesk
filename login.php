<?php
session_start();
require_once 'conexao.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (!empty($email) && !empty($senha)) {
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id']     = $usuario['id'];
            $_SESSION['usuario_nome']   = $usuario['nome'];
            $_SESSION['usuario_perfil'] = $usuario['perfil'] ?? 'tecnico'; // Guarda o perfil
            
            header('Location: listar.php');
            exit;
        } else {
            $mensagem = "E-mail ou palavra-passe incorretos.";
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
    <title>Lucca Tech - Iniciar Sessão</title>
    <link rel="stylesheet" href="css/style.css?v=3">
</head>
<body>

<div style="width: 100%; display: flex; justify-content: center; align-items: center; min-height: 100vh;">
    <div class="modal-content" style="max-width: 420px; width: 100%;">
        <div class="brand" style="text-align: center; margin-bottom: 24px;">
            <div class="brand-logo" style="margin: 0 auto 12px auto;">LT</div>
            <h1>Lucca<span>Tech</span> Support</h1>
            <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Aceda ao seu painel corporativo</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="alert alert-error" style="background: rgba(248, 113, 113, 0.15); color: #f87171; padding: 10px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; border: 1px solid rgba(248, 113, 113, 0.3);">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>E-mail Corporativo:</label>
                <input type="email" name="email" required placeholder="seu@luccatech.com">
            </div>

            <div class="form-group">
                <label>Palavra-passe:</label>
                <input type="password" name="senha" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Entrar no Sistema</button>
        </form>

        <p style="margin-top: 20px; text-align: center; font-size: 14px; color: var(--text-muted);">
            Ainda não tem conta? <a href="cadastro.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Registar-se</a>
        </p>
    </div>
</div>

</body>
</html>