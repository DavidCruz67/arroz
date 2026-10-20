<?php
session_start();
include "DBConnection.php";

if (!isset($_SESSION['idu'])) {
    header("Location: index.php");
    exit();
}

$idu = (int)$_SESSION['idu'];
$tipo_user = $_SESSION['tipo'];
$idp_filtro = isset($_GET['idp']) ? (int)$_GET['idp'] : 0;
$message = "";

// Apenas Professores criam feedback
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["GuardarFeedback"])) {
    $comentario = trim($_POST["comentario"]);
    $data = $_POST["data"];
    $idp = (int)$_POST["idp"];

    if (!empty($comentario) && $idp > 0) {
        $stmt = mysqli_prepare($conn, "INSERT INTO Feedback (comentario, data, idp, idprofessor) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssii", $comentario, $data, $idp, $idu);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $message = "Feedback enviado com sucesso!";
    }
}

if (isset($_GET['delete'])) {
    $idf = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM Feedback WHERE idf = $idf");
    header("Location: feedback.php");
    exit();
}

$meus_projetos = mysqli_query($conn, "SELECT idp, tema FROM Projeto WHERE idprofessor = $idu");

$sql = "SELECT f.*, p.tema, u.nome as professor_nome FROM Feedback f 
        JOIN Projeto p ON f.idp = p.idp 
        JOIN Utilizador u ON f.idprofessor = u.idu 
        WHERE (p.idaluno = $idu OR p.idprofessor = $idu)";

if ($idp_filtro > 0) $sql .= " AND f.idp = $idp_filtro";
$sql .= " ORDER BY f.data DESC";
$feedbacks = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Feedback - Gestão de Projetos</title>
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
            <a href="recursos.php">Recursos</a>
            <a href="feedback.php" class="active">Feedback</a>
        </nav>
        <div class="user-info">
            <span><strong><?php echo $_SESSION['nome']; ?></strong></span>
            <a href="logout.php" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </div>
</header>

<div class="page-container wide-container">
    <h1 class="page-title">Comentários & Feedback de Orientação</h1>

    <?php if (!empty($message)): ?>
        <p class="message success"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($tipo_user == 'Professor'): ?>
        <div class="admin-panel mb-20">
            <h3>Enviar Feedback ao Aluno</h3>
            <form action="feedback.php" method="post" class="form-grid">
                <div class="form-group">
                    <label>Projeto Orientado *</label>
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
                    <label>Data</label>
                    <input type="date" name="data" class="input-field" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group full-width">
                    <label>Comentário de Avaliação / Orientações *</label>
                    <textarea name="comentario" class="input-field" rows="3" required placeholder="Insira o seu parecer pedagógico..."></textarea>
                </div>

                <div class="form-group full-width">
                    <input type="submit" name="GuardarFeedback" value="Submeter Feedback" class="btn btn-primary">
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- LISTA DE FEEDBACKS -->
    <div class="feedback-list">
        <?php if (mysqli_num_rows($feedbacks) > 0): ?>
            <?php while ($f = mysqli_fetch_assoc($feedbacks)): ?>
                <div class="feedback-card">
                    <div class="feedback-header">
                        <div>
                            <strong>Prof. <?php echo htmlspecialchars($f['professor_nome']); ?></strong>
                            <span class="badge badge-info"><?php echo htmlspecialchars($f['tema']); ?></span>
                        </div>
                        <span class="card-date"><?php echo date("d/m/Y", strtotime($f['data'])); ?></span>
                    </div>
                    <div class="feedback-body">
                        <p><?php echo nl2br(htmlspecialchars($f['comentario'])); ?></p>
                    </div>
                    <?php if ($tipo_user == 'Professor' && $f['idprofessor'] == $idu): ?>
                        <div class="card-actions">
                            <a href="feedback.php?delete=<?php echo $f['idf']; ?>" class="btn btn-sm btn-danger" onclick="return confirmarEliminacao();">Eliminar</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg">Ainda não existem feedbacks registados.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>