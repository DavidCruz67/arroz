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

// Guardar Tarefa
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["GuardarTarefa"])) {
    $titulo = trim($_POST["titulo"]);
    $descricao = trim($_POST["descricao"]);
    $estado = $_POST["estado"];
    $datafim = $_POST["datafim"];
    $idp = (int)$_POST["idp"];

    if (!empty($titulo) && $idp > 0) {
        $stmt = mysqli_prepare($conn, "INSERT INTO Tarefa (titulo, descricao, estado, datafim, idp) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssi", $titulo, $descricao, $estado, $datafim, $idp);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Tarefa associada com sucesso!";
        } else {
            $message = "Erro ao gravar tarefa: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// Eliminar Tarefa
if (isset($_GET['delete'])) {
    $idt = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM Tarefa WHERE idt = $idt");
    header("Location: tarefas.php" . ($idp_filtro ? "?idp=$idp_filtro" : ""));
    exit();
}

// Obter Projetos do Utilizador para a dropdown
$meus_projetos = mysqli_query($conn, "SELECT idp, tema FROM Projeto WHERE idaluno = $idu OR idprofessor = $idu");

// Obter Lista de Tarefas
$sql_t = "SELECT t.*, p.tema FROM Tarefa t JOIN Projeto p ON t.idp = p.idp WHERE (p.idaluno = $idu OR p.idprofessor = $idu)";
if ($idp_filtro > 0) {
    $sql_t .= " AND t.idp = $idp_filtro";
}
$sql_t .= " ORDER BY t.datafim ASC";
$tarefas = mysqli_query($conn, $sql_t);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tarefas - Gestão de Projetos</title>
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
            <a href="tarefas.php" class="active">Tarefas</a>
            <a href="registos.php">Diário</a>
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
    <h1 class="page-title">Gestão de Tarefas</h1>

    <?php if (!empty($message)): ?>
        <p class="message success"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <div class="admin-panel mb-20">
        <h3>Criar Nova Tarefa</h3>
        <form action="tarefas.php" method="post" class="form-grid">
            <div class="form-group">
                <label>Projeto Pertencente *</label>
                <select name="idp" class="input-field" required>
                    <option value="">Selecione o Projeto...</option>
                    <?php 
                    mysqli_data_seek($meus_projetos, 0);
                    while($p = mysqli_fetch_assoc($meus_projetos)): 
                    ?>
                        <option value="<?php echo $p['idp']; ?>" <?php echo ($idp_filtro == $p['idp']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['tema']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Título da Tarefa *</label>
                <input type="text" name="titulo" class="input-field" required placeholder="Ex: Criar Diagrama ER">
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado" class="input-field">
                    <option value="Por fazer">Por fazer</option>
                    <option value="Em curso">Em curso</option>
                    <option value="Concluída">Concluída</option>
                </select>
            </div>

            <div class="form-group">
                <label>Data Limite</label>
                <input type="date" name="datafim" class="input-field" required>
            </div>

            <div class="form-group full-width">
                <label>Descrição detalhada</label>
                <textarea name="descricao" class="input-field" rows="3"></textarea>
            </div>

            <div class="form-group full-width">
                <input type="submit" name="GuardarTarefa" value="Adicionar Tarefa" class="btn btn-primary">
            </div>
        </form>
    </div>

    <!-- LISTAGEM -->
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Projeto</th>
                    <th>Título</th>
                    <th>Descrição</th>
                    <th>Estado</th>
                    <th>Data Limite</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($tarefas) > 0): ?>
                    <?php while ($t = mysqli_fetch_assoc($tarefas)): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($t['tema']); ?></strong></td>
                            <td><?php echo htmlspecialchars($t['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($t['descricao']); ?></td>
                            <td>
                                <span class="badge status-<?php echo strtolower(str_replace(' ', '-', $t['estado'])); ?>">
                                    <?php echo htmlspecialchars($t['estado']); ?>
                                </span>
                            </td>
                            <td><?php echo date("d/m/Y", strtotime($t['datafim'])); ?></td>
                            <td>
                                <a href="tarefas.php?delete=<?php echo $t['idt']; ?><?php echo $idp_filtro ? "&idp=$idp_filtro" : ""; ?>" class="btn btn-sm btn-danger" onclick="return confirmarEliminacao();">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">Nenhuma tarefa registada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>