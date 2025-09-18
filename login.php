<?php
session_start();

// --- Lógica para auto-login com o cookie "Lembrar de mim" ---
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    // Conecta ao banco para verificar o token
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "sistema_cadastro_manutencao";
    $conn = new mysqli($servername, $username, $password, $dbname);

    if (!$conn->connect_error) {
        $sql = "SELECT id, nome FROM usuarios WHERE remember_token = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Loga o usuário na sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome'];
        }
        $conn->close();
    }
}

// Se o usuário já estiver logado, redireciona para a página principal
$error_message = ''; // Inicializa a variável de erro
if (isset($_GET['error'])) {
    if ($_GET['error'] == '1') {
        $error_message = 'Usuário ou senha inválidos.';
    } elseif ($_GET['error'] == '2') {
        $error_message = 'Por favor, faça login para acessar.';
    }
}

// Verifica se o usuário está logado
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Manutenção</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="login-body">

    <div class="login-container">
        <div class="login-form-section <?php if ($is_logged_in) echo 'hidden'; ?>">
            <div class="login-header">
                <i class="fa-solid fa-screwdriver-wrench login-icon"></i>
                <h1>Sistema de Manutenção</h1>
                <p>Acesse para gerenciar seus equipamentos.</p>
            </div>

            <?php if (!$is_logged_in): ?>
                <form action="auth.php" method="POST">
                    <input type="hidden" name="action" value="login">

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="senha">Senha:</label>
                        <input type="password" id="senha" name="senha" required>
                    </div>

                    <?php if ($error_message): ?>
                        <p class="login-error"><?php echo $error_message; ?></p>
                    <?php endif; ?>

                    <div class="login-options">
                        <input type="checkbox" id="lembrar" name="lembrar">
                        <label for="lembrar">Lembrar de mim</label>
                    </div>

                    <button type="submit" class="btn btn-login">Entrar</button>
                </form>

                <div class="login-register-link">
                    <p>Não tem uma conta? <a href="registrar_usuario.php">Cadastre-se</a></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="login-menu-section">
            <?php if ($is_logged_in): ?>
                <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
                <p>Selecione uma opção para começar.</p>
            <?php else: ?>
                <h2>Acesso Rápido</h2>
                <p>Faça login para habilitar as opções abaixo.</p>
            <?php endif; ?>

            <div class="login-menu-buttons">
                <a href="index.php" class="btn btn-menu-option <?php if (!$is_logged_in) echo 'disabled'; ?>">
                    <i class="fa-solid fa-plus"></i>
                    <span>Cadastrar Equipamentos</span>
                </a>
                <a href="listar_equipamentos.php" class="btn btn-menu-option <?php if (!$is_logged_in) echo 'disabled'; ?>">
                    <i class="fa-solid fa-list"></i>
                    <span>Manutenção</span>
                </a>
                <a href="plano_manutencao.php" class="btn btn-menu-option <?php if (!$is_logged_in) echo 'disabled'; ?>">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Plano de Manutenção</span>
                </a>
                <a href="relatorios.php" class="btn btn-menu-option <?php if (!$is_logged_in) echo 'disabled'; ?>">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Relatórios</span>
                </a>
            </div>

            <?php if ($is_logged_in): ?>
                <a href="auth.php?action=logout" class="btn-sair-login"><i class="fa-solid fa-right-from-bracket"></i> Log out</a>
            <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>