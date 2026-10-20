<?php
session_start();
include "DBConnection.php";

if (!isset($_SESSION['idu'])) {
    header("Location: index.php");
    exit();
}

$idu = (int)$_SESSION['idu'];
$tipo_user = $_SESSION['tipo'];
$message = "";

// Criar Projeto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["CriarProjeto"])) {
    $tema = trim($_POST["tema"]);
    $area = trim($_POST["area"]);
    $datafim = $_POST["datafim"];
    $idaluno = ($tipo_user == 'Aluno') ? $idu : (int)$_POST["idaluno"];
    $idprofessor = ($tipo_user == 'Professor') ? $idu : (int)$_POST["idprofessor"];

    if (!empty($tema) && !empty($area) && !empty($datafim)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO Projeto (tema, area, datafim, idaluno, idprofessor) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssii", $tema, $area, $datafim, $idaluno, $idprofessor);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Projeto criado com sucesso!";
        } else {
            $message = "Erro ao criar projeto: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// Eliminar Projeto
if (isset($_GET['delete'])) {
    $id_del = (int)$_GET['delete'];
    $stmt_del = mysqli_prepare($conn, "DELETE FROM Projeto WHERE idp = ?");
    mysqli_stmt_bind_param($stmt_del, "i", $id_del);
    mysqli_stmt_execute($stmt_del);
    header("Location: projetos.php");
    exit();
}

// Obter Alunos e Professores para os selects
$alunos = mysqli_query($conn, "SELECT idu, nome FROM Utilizador WHERE tipo = 'Aluno'");
$professores = mysqli_query($conn, "SELECT idu, nome FROM Utilizador WHERE tipo = 'Professor'");

// Listar Projetos
if ($tipo_user == 'Aluno') {
    $sql = "SELECT p.*, u.nome AS professor_nome FROM Projeto p LEFT JOIN Utilizador u ON p.idprofessor = u.idu WHERE p.idaluno = $idu";
} else {
    $sql = "SELECT p.*, u.nome AS aluno_nome FROM Projeto p LEFT JOIN Utilizador u ON p.idaluno = u.idu WHERE p.idprofessor = $idu";
}
$projetos = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Projetos - Gestão de Projetos</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>

<header class="top-nav">
    <div class="nav-container">
        <a href="dashboard.php" class="logo">🎯 GestãoPro</a>
        <nav class="nav-links">
            <a href="dashboard.php">Painel</a>
            <a href="projetos.php" class="active">Projetos</a>
            <a href="tarefas.php">Tarefas</a>
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
    <h1 class="page-title">Gestão de Projetos</h1>

    <?php if (!empty($message)): ?>
        <p class="message success"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- FORMULÁRIO DE CRIAÇÃO -->
    <div class="admin-panel mb-20">
        <h3>Novo Projeto</h3>
        <form action="projetos.php" method="post" class="form-grid">
            <div class="form-group">
                <label>Tema do Projeto *</label>
                <input type="text" name="tema" class="input-field" required placeholder="Ex: Sistema de Gestão de Loja">
            </div>

            <div class="form-group">
                <label>Área de Estudo *</label>
                <input type="text" name="area" class="input-field" required placeholder="Ex: Programação Web / TGPSI">
            </div>

            <div class="form-group">
                <label>Data de Conclusão *</label>
                <input type="date" name="datafim" class="input-field" required>
            </div>

            <?php if ($tipo_user == 'Aluno'): ?>
                <div class="form-group">
                    <label>Professor Orientador</label>
                    <select name="idprofessor" class="input-field">
                        <option value="">Selecione um Professor...</option>
                        <?php while($p = mysqli_fetch_assoc($professores)): ?>
                            <option value="<?php echo $p['idu']; ?>"><?php echo htmlspecialchars($p['nome']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label>Aluno Atribuído</label>
                    <select name="idaluno" class="input-field">
                        <option value="">Selecione um Aluno...</option>
                        <?php while($a = mysqli_fetch_assoc($alunos)): ?>
                            <option value="<?php echo $a['idu']; ?>"><?php echo htmlspecialchars($a['nome']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="form-group full-width">
                <input type="submit" name="CriarProjeto" value="Criar Projeto" class="btn btn-primary">
            </div>
        </form>
    </div>

    <!-- TABELA DE PROJETOS -->
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tema</th>
                    <th>Área</th>
                    <th>Data Fim</th>
                    <th><?php echo ($tipo_user == 'Aluno') ? 'Orientador' : 'Aluno'; ?></th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($projetos) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($projetos)): ?>
                        <tr>
                            <td>#<?php echo $row['idp']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['tema']); ?></strong></td>
                            <td><span class="badge badge-info"><?php echo htmlspecialchars($row['area']); ?></span></td>
                            <td><?php echo date("d/m/Y", strtotime($row['datafim'])); ?></td>
                            <td><?php echo htmlspecialchars(($tipo_user == 'Aluno') ? ($row['professor_nome'] ?? 'Sem Orientador') : ($row['aluno_nome'] ?? 'Sem Aluno')); ?></td>
                            <td>
                                <a href="tarefas.php?idp=<?php echo $row['idp']; ?>" class="btn btn-sm btn-secondary">Tarefas</a>
                                <a href="projetos.php?delete=<?php echo $row['idp']; ?>" class="btn btn-sm btn-danger" onclick="return confirmarEliminacao();">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">Nenhum projeto registado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>