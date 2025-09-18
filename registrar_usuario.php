<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sistema_cadastro_manutencao";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // 1. Validação dos dados
    if ($senha !== $confirmar_senha) {
        $error_message = 'As senhas não coincidem.';
    } elseif (strlen($senha) < 6) {
        $error_message = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        // 2. Verificar se o e-mail já existe
        $sql_check = "SELECT id FROM usuarios WHERE email = ? LIMIT 1";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $error_message = 'Este endereço de e-mail já está em uso.';
        } else {
            // 3. Inserir novo usuário
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql_insert = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sss", $nome, $email, $senha_hash);

            if ($stmt_insert->execute()) {
                $success_message = 'Usuário registrado com sucesso! Você já pode fazer o login.';
            } else {
                $error_message = 'Erro ao registrar o usuário. Tente novamente.';
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Novo Usuário</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="login-body">

    <div class="login-container" style="max-width: 500px;">
        <div class="login-form-section">
            <div class="login-header">
                <i class="fa-solid fa-user-plus login-icon"></i>
                <h1>Criar Nova Conta</h1>
                <p>Preencha os dados para se registrar.</p>
            </div>

            <?php if ($success_message): ?>
                <p class="login-success"><?php echo $success_message; ?></p>
                <a href="login.php" class="btn btn-login">Ir para Login</a>
            <?php else: ?>
                <form action="registrar_usuario.php" method="POST">
                    <div class="form-group">
                        <label for="nome">Nome Completo:</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="senha">Senha (mín. 6 caracteres):</label>
                        <input type="password" id="senha" name="senha" required>
                    </div>

                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Senha:</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                    </div>

                    <?php if ($error_message): ?>
                        <p class="login-error"><?php echo $error_message; ?></p>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-login">Cadastrar</button>
                </form>
                <div class="login-register-link">
                    <p>Já tem uma conta? <a href="login.php">Faça o login</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>