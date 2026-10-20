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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["GuardarRecurso"])) {
    $titulo = trim($_POST["titulo"]);
    $descricao = trim($_POST["descricao"]);
    $link = trim($_POST["link"]);
    $data = $_POST["data"];
    $idp = (int)$_POST["idp"];

    if (!empty($titulo) && $idp > 0) {
        $stmt = mysqli_prepare($conn, "INSERT INTO Recurso (titulo, descricao, link, data, idp) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssi", $titulo, $descricao, $link, $data, $idp);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $message = "Recurso adicionado com sucesso!";
    }
}

if (isset($_GET['delete'])) {
    $idr = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM Recurso WHERE idr = $idr");
    header("Location: recursos.php");
    exit();
}

$meus_projetos = mysqli_query($conn, "SELECT idp, tema FROM Projeto WHERE idaluno = $idu OR idprofessor = $idu");

$sql = "SELECT rec.*, p.tema FROM Recurso rec JOIN Projeto p ON rec.idp = p.idp WHERE (p.idaluno = $idu OR p.idprofessor = $idu)";
if ($idp_filtro > 0) $sql .= " AND rec.idp = $idp_filtro";
$sql .= " ORDER BY rec.data DESC";
$recursos = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Recursos - Gestão de Projetos</title>
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
            <a href="registos.php">Diário</a>
            <a href="recursos.php" class="active">Recursos</a>
            <a href="feedback.php">Feedback</a>
        </nav>
        <div class="user-info">
            <span><strong><?php echo $_SESSION['nome']; ?></strong></span>
            <a href="logout.php" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </div>
</header>

<div class="page-container wide-container">
    <h1 class="page-title">Recursos & Documentos</h1>

    <?php if (!empty($message)): ?>
        <p class="message success"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <div class="admin-panel mb-20">
        <h3>Adicionar Novo Recurso</h3>
        <form action="recursos.php" method="post" class="form-grid">
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
                <label>Título do Recurso *</label>
                <input type="text" name="titulo" class="input-field" required placeholder="Ex: Documentação oficial PHP">
            </div>

            <div class="form-group">
                <label>Link / URL</label>
                <input type="url" name="link" class="input-field" placeholder="https://exemplo.com">
            </div>

            <div class="form-group">
                <label>Data</label>
                <input type="date" name="data" class="input-field" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group full-width">
                <label>Descrição do Recurso</label>
                <textarea name="descricao" class="input-field" rows="2"></textarea>
            </div>

            <div class="form-group full-width">
                <input type="submit" name="GuardarRecurso" value="Adicionar Recurso" class="btn btn-primary">
            </div>
        </form>
    </div>

    <!-- CARDS DE RECURSOS -->
    <div class="cards-grid">
        <?php if (mysqli_num_rows($recursos) > 0): ?>
            <?php while ($r = mysqli_fetch_assoc($recursos)): ?>
                <div class="content-card">
                    <div class="card-header">
                        <span class="badge badge-info"><?php echo htmlspecialchars($r['tema']); ?></span>
                        <span class="card-date"><?php echo date("d/m/Y", strtotime($r['data'])); ?></span>
                    </div>
                    <h3>🔗 <?php echo htmlspecialchars($r['titulo']); ?></h3>
                    <p><?php echo htmlspecialchars($r['descricao']); ?></p>
                    
                    <?php if (!empty($r['link'])): ?>
                        <p><a href="<?php echo htmlspecialchars($r['link']); ?>" target="_blank" class="resource-link">Abrir Link Externo ↗</a></p>
                    <?php endif; ?>

                    <div class="card-actions">
                        <a href="recursos.php?delete=<?php echo $r['idr']; ?>" class="btn btn-sm btn-danger" onclick="return confirmarEliminacao();">Eliminar</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg">Nenhum recurso partilhado.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>