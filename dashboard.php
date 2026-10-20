<?php
session_start();
include "DBConnection.php";

if (!isset($_SESSION['idu'])) {
    header("Location: index.php");
    exit();
}

$idu = (int)$_SESSION['idu'];
$nome_user = htmlspecialchars($_SESSION['nome']);
$tipo_user = $_SESSION['tipo'];

// Consultas estatísticas de acordo com o perfil
if ($tipo_user == 'Aluno') {
    $res_p = mysqli_query($conn, "SELECT COUNT(*) as total FROM Projeto WHERE idaluno = $idu");
    $res_t = mysqli_query($conn, "SELECT COUNT(*) as total FROM Tarefa t JOIN Projeto p ON t.idp = p.idp WHERE p.idaluno = $idu");
    $res_r = mysqli_query($conn, "SELECT COUNT(*) as total FROM Registo_diario r JOIN Projeto p ON r.idp = p.idp WHERE p.idaluno = $idu");
    $res_f = mysqli_query($conn, "SELECT COUNT(*) as total FROM Feedback f JOIN Projeto p ON f.idp = p.idp WHERE p.idaluno = $idu");
} else {
    $res_p = mysqli_query($conn, "SELECT COUNT(*) as total FROM Projeto WHERE idprofessor = $idu");
    $res_t = mysqli_query($conn, "SELECT COUNT(*) as total FROM Tarefa t JOIN Projeto p ON t.idp = p.idp WHERE p.idprofessor = $idu");
    $res_r = mysqli_query($conn, "SELECT COUNT(*) as total FROM Registo_diario r JOIN Projeto p ON r.idp = p.idp WHERE p.idprofessor = $idu");
    $res_f = mysqli_query($conn, "SELECT COUNT(*) as total FROM Feedback WHERE idprofessor = $idu");
}

$total_projetos = mysqli_fetch_assoc($res_p)['total'];
$total_tarefas  = mysqli_fetch_assoc($res_t)['total'];
$total_registos = mysqli_fetch_assoc($res_r)['total'];
$total_feedback = mysqli_fetch_assoc($res_f)['total'];

// Buscar Projetos Recentes
if ($tipo_user == 'Aluno') {
    $sql_proj = "SELECT p.*, u.nome as prof_nome FROM Projeto p LEFT JOIN Utilizador u ON p.idprofessor = u.idu WHERE p.idaluno = $idu ORDER BY p.idp DESC";
} else {
    $sql_proj = "SELECT p.*, u.nome as aluno_nome FROM Projeto p LEFT JOIN Utilizador u ON p.idaluno = u.idu WHERE p.idprofessor = $idu ORDER BY p.idp DESC";
}
$result_projetos = mysqli_query($conn, $sql_proj);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Principal - Gestão de Projetos</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>

<header class="top-nav">
    <div class="nav-container">
        <a href="dashboard.php" class="logo">🎯 GestãoPro</a>
        <nav class="nav-links">
            <a href="dashboard.php" class="active">Painel</a>
            <a href="projetos.php">Projetos</a>
            <a href="tarefas.php">Tarefas</a>
            <a href="registos.php">Diário</a>
            <a href="recursos.php">Recursos</a>
            <a href="feedback.php">Feedback</a>
        </nav>
        <div class="user-info">
            <span><strong><?php echo $nome_user; ?></strong> (<?php echo $tipo_user; ?>)</span>
            <a href="logout.php" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </div>
</header>

<div class="page-container wide-container">
    <div class="dashboard-header">
        <h1>Bem-vindo, <?php echo $nome_user; ?>! 👋</h1>
        <p>Visão geral da sua atividade e projetos atribuídos.</p>
    </div>

    <!-- CARDS DE ESTATÍSTICAS -->
    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📁</div>
            <div class="stat-info">
                <h3><?php echo $total_projetos; ?></h3>
                <p>Projetos</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3><?php echo $total_tarefas; ?></h3>
                <p>Tarefas Registadas</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📖</div>
            <div class="stat-info">
                <h3><?php echo $total_registos; ?></h3>
                <p>Registos Diários</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div class="stat-info">
                <h3><?php echo $total_feedback; ?></h3>
                <p>Feedbacks</p>
            </div>
        </div>
    </section>

    <!-- LISTAGEM RÁPIDA DE PROJETOS -->
    <div class="card-section">
        <div class="section-title-bar">
            <h2>Os Seus Projetos</h2>
            <a href="projetos.php" class="btn btn-primary">+ Gerir Projetos</a>
        </div>

        <div class="cards-grid">
            <?php if (mysqli_num_rows($result_projetos) > 0): ?>
                <?php while ($proj = mysqli_fetch_assoc($result_projetos)): ?>
                    <div class="content-card">
                        <div class="card-header">
                            <span class="badge badge-info"><?php echo htmlspecialchars($proj['area']); ?></span>
                            <span class="card-date">Fim: <?php echo date("d/m/Y", strtotime($proj['datafim'])); ?></span>
                        </div>
                        <h3><?php echo htmlspecialchars($proj['tema']); ?></h3>
                        <p class="card-meta">
                            <?php if ($tipo_user == 'Aluno'): ?>
                                <strong>Orientador:</strong> <?php echo htmlspecialchars($proj['prof_nome'] ?? 'Não atribuído'); ?>
                            <?php else: ?>
                                <strong>Aluno:</strong> <?php echo htmlspecialchars($proj['aluno_nome'] ?? 'Não atribuído'); ?>
                            <?php endif; ?>
                        </p>

                        <div class="card-actions">
                            <a href="tarefas.php?idp=<?php echo $proj['idp']; ?>" class="btn btn-secondary btn-sm">Tarefas</a>
                            <a href="registos.php?idp=<?php echo $proj['idp']; ?>" class="btn btn-secondary btn-sm">Diário</a>
                            <a href="recursos.php?idp=<?php echo $proj['idp']; ?>" class="btn btn-secondary btn-sm">Recursos</a>
                            <a href="feedback.php?idp=<?php echo $proj['idp']; ?>" class="btn btn-secondary btn-sm">Feedback</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty-msg">Nenhum projeto encontrado. Comece por criar um projeto no menu "Projetos".</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>