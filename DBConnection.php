<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "11TGPSIBD_04"; // Altere se o nome da sua BD for diferente

$conn = mysqli_connect($host, $user, $pass, $db_name);

if (!$conn) {
    die("Falha na conexão à base de dados: " . mysqli_connect_error());
}

// Definir codificação para acentos e carateres especiais
mysqli_set_charset($conn, "utf8mb4");
?>