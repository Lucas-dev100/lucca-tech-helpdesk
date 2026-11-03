<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'conexao.php';

$usuario_id     = $_SESSION['usuario_id'];
$usuario_perfil = $_SESSION['usuario_perfil'] ?? 'cliente';

// Abertura de chamado rápida via Modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'criar_chamado') {
    $titulo     = $_POST['titulo'] ?? '';
    $categoria  = $_POST['categoria'] ?? '';
    $descricao  = $_POST['descricao'] ?? '';

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
    }
}

// Filtro SQL por perfil: TÉCNICO vê tudo, CLIENTE vê apenas os seus
if ($usuario_perfil === 'tecnico') {
    $sql = "SELECT c.*, u.nome AS usuario_nome 
            FROM chamados c 
            JOIN usuarios u ON c.usuario_id = u.id 
            ORDER BY c.criado_em DESC";
    $stmt = $pdo->query($sql);
} else {
    $sql = "SELECT c.*, u.nome AS usuario_nome 
            FROM chamados c 
            JOIN usuarios u ON c.usuario_id = u.id 
            WHERE c.usuario_id = :usuario_id
            ORDER BY c.criado_em DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);
}

$chamados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cálculo das métricas exibidas no topo
$total_chamados  = count($chamados);
$total_abertos   = count(array_filter($chamados, fn($c) => $c['status'] === 'Aberto'));
$total_andamento = count(array_filter($chamados, fn($c) => $c['status'] === 'Em Andamento'));
$total_fechados  = count(array_filter($chamados, fn($c) => $c['status'] === 'Fechado'));
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lucca Tech - Central de Suporte</title>
    <link rel="stylesheet" href="css/style.css?v=4">
</head>
<body>

<nav class="navbar">
    <div class="brand">
        <div class="brand-logo">LT</div>
        <h1>Lucca<span>Tech</span> Support</h1>
    </div>
    <div class="user-profile">
        <span style="font-size: 14px; color: var(--text-muted);">
            Olá, <strong style="color: #fff;"><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Utilizador') ?></strong> 
            <span class="badge" style="background: rgba(99, 102, 241, 0.2); color: #818cf8; margin-left: 6px;">
                <?= ucfirst($usuario_perfil) ?>
            </span>
        </span>
        <a href="logout.php" class="btn-logout">Sair</a>
    </div>
</nav>

<div class="main-wrapper">

    <div style="margin-bottom: 20px;">
        <?php if ($usuario_perfil === 'tecnico'): ?>
            <p style="color: #4ade80; font-size: 14px; font-weight: 600;">📡 Painel do Técnico: A gerir a caixa de entrada global de chamados.</p>
        <?php else: ?>
            <p style="color: var(--text-muted); font-size: 14px;">📋 Área do Cliente: A acompanhar os seus tickets solicitados.</p>
        <?php endif; ?>
    </div>

    <!-- Cards de Métricas -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-title"><?= $usuario_perfil === 'tecnico' ? 'Total Recebidos' : 'Meus Chamados' ?></div>
            <div class="metric-value"><?= $total_chamados ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-title" style="color: #fbbf24;">Em Aberto</div>
            <div class="metric-value"><?= $total_abertos ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-title" style="color: #38bdf8;">Em Atendimento</div>
            <div class="metric-value"><?= $total_andamento ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-title" style="color: #4ade80;">Concluídos</div>
            <div class="metric-value"><?= $total_fechados ?></div>
        </div>
    </div>

    <div class="toolbar">
        <div class="search-box">
            <input type="text" id="searchTicket" placeholder="🔍 Pesquisar por chamado, cliente ou assunto...">
        </div>
        <button onclick="openModal()" class="btn">+ Abrir Chamado</button>
    </div>

    <!-- Tabela do Helpdesk -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="ticketsTable">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Título / Descrição</th>
                        <th>Categoria</th>
                        <th>Solicitante</th>
                        <th>Status</th>
                        <th>Data</th>
                        <?php if ($usuario_perfil === 'tecnico'): ?>
                            <th>Ações do Técnico</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($chamados) > 0): ?>
                        <?php foreach ($chamados as $chamado): ?>
                            <tr>
                                <td>#<?= $chamado['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($chamado['titulo']) ?></strong>
                                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                        <?= htmlspecialchars(mb_strimwidth($chamado['descricao'], 0, 50, "...")) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($chamado['categoria']) ?></td>
                                <td><?= htmlspecialchars($chamado['usuario_nome']) ?></td>
                                <td>
                                    <span class="badge <?= str_replace(' ', '-', $chamado['status']) ?>">
                                        <?= $chamado['status'] ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($chamado['criado_em'])) ?></td>
                                
                                <?php if ($usuario_perfil === 'tecnico'): ?>
                                    <td>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <form action="atualizar_status.php" method="POST" style="margin: 0;">
                                                <input type="hidden" name="id" value="<?= $chamado['id'] ?>">
                                                <select name="status" onchange="this.form.submit()" style="padding: 4px 8px; font-size: 13px;">
                                                    <option value="Aberto" <?= $chamado['status'] === 'Aberto' ? 'selected' : '' ?>>Aberto</option>
                                                    <option value="Em Andamento" <?= $chamado['status'] === 'Em Andamento' ? 'selected' : '' ?>>Em Andamento</option>
                                                    <option value="Fechado" <?= $chamado['status'] === 'Fechado' ? 'selected' : '' ?>>Fechado</option>
                                                </select>
                                            </form>
                                            <a href="deletar.php?id=<?= $chamado['id'] ?>" 
                                               style="color: #f87171; text-decoration: none; font-weight: bold; padding: 4px 8px; border: 1px solid rgba(248,113,113,0.3); border-radius: 6px;"
                                               onclick="return confirm('Eliminar este chamado permanentemente?')"
                                               title="Eliminar Chamado">
                                               ✕
                                            </a>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $usuario_perfil === 'tecnico' ? '7' : '6' ?>" style="text-align: center; color: var(--text-muted); padding: 28px;">
                                Nenhum chamado encontrado nesta vista.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="ticketModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Abrir Novo Chamado</h2>
            <button class="close-btn" onclick="closeModal()">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="criar_chamado">
            <div class="form-group">
                <label>Título:</label>
                <input type="text" name="titulo" required placeholder="Ex: Falha no servidor">
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
                <textarea name="descricao" required placeholder="Descreva o problema com detalhes..." rows="4"></textarea>
            </div>
            <button type="submit" class="btn" style="width: 100%;">Submeter Ticket</button>
        </form>
    </div>
</div>

<script src="js/app.js"></script>
</body>
</html>