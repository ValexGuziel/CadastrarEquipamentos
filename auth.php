<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        // A conexão com o banco é necessária para o login
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "sistema_cadastro_manutencao";
        $conn = new mysqli($servername, $username, $password, $dbname);
        handleLogin($conn);
        $conn->close();
    }
}
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    handleLogout();
}
function handleLogin($conn) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    if ($conn->connect_error) {
        // Em um ambiente de produção, logue o erro em vez de exibi-lo.
        die("Falha na conexão: " . $conn->connect_error);
    }

    // 1. Busca o usuário pelo email
    $sql = "SELECT id, nome, senha FROM usuarios WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // 2. Verifica se a senha fornecida corresponde ao hash no banco de dados
        if (password_verify($senha, $user['senha'])) {
            // Login bem-sucedido
            session_regenerate_id(true); // Previne session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome'];

            // 3. Lógica do "Lembrar de mim"
            if (isset($_POST['lembrar'])) {
                $token = bin2hex(random_bytes(32));
                $user_id = $user['id'];

                // Armazena o token no banco de dados
                $sql_token = "UPDATE usuarios SET remember_token = ? WHERE id = ?";
                $stmt_token = $conn->prepare($sql_token);
                $stmt_token->bind_param("si", $token, $user_id);
                $stmt_token->execute();

                // Define o cookie (válido por 30 dias)
                setcookie('remember_me', $token, time() + (86400 * 30), "/", "", true, true); // 86400 = 1 dia
            }

            header('Location: login.php');
            exit();
        }
    }

    // Se chegou até aqui, o login falhou (usuário não encontrado ou senha incorreta)
    header('Location: login.php?error=1');
    exit();
}

function handleLogout() {
    // --- Lógica para limpar o cookie "Lembrar de mim" ---
    if (isset($_SESSION['user_id'])) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "sistema_cadastro_manutencao";
        $conn = new mysqli($servername, $username, $password, $dbname);

        if (!$conn->connect_error) {
            // Limpa o token do usuário no banco de dados
            $sql_clear_token = "UPDATE usuarios SET remember_token = NULL WHERE id = ?";
            $stmt_clear = $conn->prepare($sql_clear_token);
            $stmt_clear->bind_param("i", $_SESSION['user_id']);
            $stmt_clear->execute();
            $conn->close();
        }
    }
    // Limpa o cookie do navegador
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}
?>