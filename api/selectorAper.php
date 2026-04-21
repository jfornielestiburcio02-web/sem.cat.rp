<?php
session_start();

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: login.php");
    exit();
}

echo "Sesión iniciada como: " . htmlspecialchars($_SESSION['usuario']);
?>
