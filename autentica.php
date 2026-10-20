<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include "DBConnection.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_digitado = trim($_POST["email"] ?? '');
    $senha_digitada = $_POST["pass"] ?? '';

    if (empty($email_digitado) || empty($senha_digitada)) {
        header("Location: index.php?error=1");
        exit();
    }

    $stmt = mysqli_prepare($conn, "SELECT idu, nome, email, password, tipo FROM Utilizador WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email_digitado);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($senha_digitada, $row['password'])) {
            $_SESSION["idu"] = $row["idu"];
            $_SESSION["nome"] = $row["nome"];
            $_SESSION["email"] = $row["email"];
            $_SESSION["tipo"] = $row["tipo"];

            header("Location: dashboard.php");
            exit();
        }
    }

    header("Location: index.php?error=1");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>