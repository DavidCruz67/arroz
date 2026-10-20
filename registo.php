<?php
session_start();
include "DBConnection.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["Registar"])) {
    $nome = trim($_POST["nome"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $pass = $_POST["pass"] ?? '';
    $pass_confirm = $_POST["pass_confirm"] ?? '';
    
    // Verificação do tipo de utilizador através do código secreto
    $tipo_opcao = $_POST["tipo_opcao"] ?? 'Aluno';
    $codigo_professor = trim($_POST["codigo_professor"] ?? '');

    // Se selecionar Professor e o código for 1234, fica como Professor. Caso contrário, Aluno.
    if ($tipo_opcao === 'Professor' && $codigo_professor === '1234') {
        $tipo = 'Professor';
    } else {
        $tipo = 'Aluno';
    }

    if (!empty($nome) && !empty($email) && !empty($pass) && !empty($pass_confirm)) {
        if ($pass !== $pass_confirm) {
            $message = "As passwords não coincidem!";
        } else {
            $stmt_check = mysqli_prepare($conn, "SELECT idu FROM Utilizador WHERE email = ?");
            mysqli_stmt_bind_param($stmt_check, "s", $email);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $message = "Este e-mail já se encontra registado.";
            } else {
                $pass_hashed = password_hash($pass, PASSWORD_DEFAULT);

                $stmt = mysqli_prepare($conn, "INSERT INTO Utilizador (nome, email, password, tipo) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ssss", $nome, $email, $pass_hashed, $tipo);

                if (mysqli_stmt_execute($stmt)) {
                    header("Location: index.php?sucesso=1");
                    exit();
                } else {
                    $message = "Erro ao efetuar registo: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            }
            mysqli_stmt_close($stmt_check);
        }
    } else {
        $message = "Por favor, preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registo - Gestão de Projetos</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body class="bg-center">

<div class="page-container small-container">
    <div class="admin-panel">
        <div class="brand-header">
            <h2>Criar Nova Conta</h2>
            <p>Preencha os dados para se registar</p>
        </div>

        <?php if (!empty($message)): ?>
            <p class="message error"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form class="login-form" method="post" action="registo.php" onsubmit="return validarRegisto(this);">
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" class="input-field" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" class="input-field" required>
            </div>

            <!-- Pergunta com Checkboxes -->
            <div class="form-group">
                <label>Você é um:</label>
                <div style="display: flex; gap: 20px; margin-top: 8px;">
                    <label style="font-weight: normal; cursor: pointer;">
                        <input type="checkbox" id="chk_aluno" name="tipo_opcao" value="Aluno" checked onclick="selecionarTipo('Aluno')"> Aluno
                    </label>
                    <label style="font-weight: normal; cursor: pointer;">
                        <input type="checkbox" id="chk_professor" name="tipo_opcao" value="Professor" onclick="selecionarTipo('Professor')"> Professor
                    </label>
                </div>
            </div>

            <!-- Campo do Código Secreto (visível apenas para Professor) -->
            <div class="form-group" id="grupo_codigo" style="display: none;">
                <label for="codigo_professor">Código Secreto de Professor</label>
                <input type="password" id="codigo_professor" name="codigo_professor" class="input-field" placeholder="Insira o código de validação">
            </div>

            <div class="form-group">
                <label for="pass">Password</label>
                <input type="password" id="pass" name="pass" class="input-field" minlength="4" required>
            </div>

            <div class="form-group">
                <label for="pass_confirm">Confirmar Password</label>
                <input type="password" id="pass_confirm" name="pass_confirm" class="input-field" minlength="4" required>
            </div>

            <div class="button-group">
                <input type="submit" name="Registar" value="Registar" class="btn btn-primary btn-full">
            </div>

            <div class="auth-link">
                <p>Já tem uma conta? <a href="index.php">Faça Login</a></p>
            </div>
        </form>
    </div>
</div>

</body>
</html>