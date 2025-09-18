<?php
session_start();

// Verifica se o usuário não está logado
if (!isset($_SESSION['user_id'])) {
    // Redireciona para a página de login com uma mensagem de erro
    header('Location: login.php?error=2');
    exit();
}
?>