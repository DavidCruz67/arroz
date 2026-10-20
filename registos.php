<?php
session_start();
include "DBConnection.php";

if (!isset($_SESSION['idu'])) {
    header("Location: index.php");
    exit();
}

$idu = (int)$_SESSION['idu'];
$idp_filtro = isset($_GET['idp']) ? (int)$_GET['idp'] : 0;
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["GuardarRegisto"])) {
    $descricao = trim($_POST["descricao"]);
    $data = $_POST["data"];
    $idp = (int)$_POST["idp"];

    if (!empty($descricao) && $idp > 0) {
        $stmt = mysqli_prepare($conn, "INSERT INTO Registo_diario (descricao, data, idp) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssi", $descricao, $data, $idp);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $message = "Registo diário adicionado!";
    }
}

if (isset($_GET['delete'])) {
    $idr = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM Registo_diario WHERE idr = $idr");
    header("Location: registos.php");
    exit();
}

$meus_projetos = mysqli_query($conn, "SELECT idp, tema FROM Projeto WHERE idaluno = $idu OR idprofessor = $idu");

$sql = "SELECT r.*, p.tema FROM Registo_diario r JOIN Projeto p ON r.idp = p.idp WHERE (p.idaluno = $idu OR p.idprofessor = $idu)";
if ($idp_filtro > 0) $sql .= " AND r.idp = $idp_filtro";
$sql .= " ORDER BY r.data DESC";
$registos = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Registo Diário - Gestão de Projetos</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>

<header class="top-nav">
    <div class="nav-container">
        <a href="dashboard.php" class="logo">🎯 GestãoPro</a>
        <nav class="nav-links">
            <a href="dashboard.php">Painel</a>
            <a href="projetos.php">Projetos</a>
            <a href="tarefas.php">Tarefas</a>
            <a href="registos.php" class="active">Diário</a>
            <a href="recursos.php">Recursos</a>
            <a href="feedback.php">Feedback</a>
        </nav>
        <div class="user-info">
            <span><strong><?php echo $_SESSION['nome']; ?></strong></span>
            <a href="logout.php" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </div>
</header>

<div class="page-container wide-container">
    <h1 class="page-title">Registo Diário de Atividades</h1>

    <?php if (!empty($message)): ?>
        <p class="message success"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <div class="admin-panel mb-20">
        <h3>Novo Registo de Trabalho</h3>
        <form action="registos.php" method="post" class="form-grid">
            <div class="form-group">
                <label>Projeto *</label>
                <select name="idp" class="input-field" required>
                    <option value="">Selecione o Projeto...</option>
                    <?php while($p = mysqli_fetch_assoc($meus_projetos)): ?>
                        <option value="<?php echo $p['idp']; ?>" <?php echo ($idp_filtro == $p['idp']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['tema']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Data *</label>
                <input type="date" name="data" class="input-field" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group full-width">
                <label>Descrição do Progresso Realizado *</label>
                <textarea name="descricao" class="input-field" rows="3" required placeholder="O que realizou hoje no projeto?"></textarea>
            </div>

            <div class="form-group full-width">
                <input type="submit" name="GuardarRegisto" value="Guardar Registo" class="btn btn-primary">
            </div>
        </form>
    </div>

    <!-- LOG DIÁRIO -->
    <div class="timeline-container">
        <?php if (mysqli_num_rows($registos) > 0): ?>
            <?php while ($r = mysqli_fetch_assoc($registos)): ?>
                <div class="timeline-card">
                    <div class="timeline-header">
                        <strong>Projeto: <?php echo htmlspecialchars($r['tema']); ?></strong>
                        <span class="timeline-date">📅 <?php echo date("d/m/Y", strtotime($r['data'])); ?></span>
                    </div>
                    <p class="timeline-body"><?php echo nl2br(htmlspecialchars($r['descricao'])); ?></p>
                    <div class="timeline-footer">
                        <a href="registos.php?delete=<?php echo $r['idr']; ?>" class="btn btn-sm btn-danger" onclick="return confirmarEliminacao();">Eliminar</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg">Nenhum registo diário encontrado.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>