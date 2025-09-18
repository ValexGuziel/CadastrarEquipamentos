<?php
// Verifica se o ID foi passado na URL e se é um número válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID do equipamento inválido ou não fornecido.");
}

$equipamento_id = intval($_GET['id']);

// Configuração do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sistema_cadastro_manutencao";

// Conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Prepara o SQL para DELETE
$sql = "DELETE FROM equipamentos WHERE id = ?";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $equipamento_id);

    if ($stmt->execute()) {
        // Redireciona para a lista de equipamentos com status de sucesso
        header("Location: listar_equipamentos.php?status=deleted");
        exit();
    } else {
        // Exibe erro caso a exclusão falhe
        die("Erro ao excluir o equipamento: " . $stmt->error);
    }
    $stmt->close();
} else {
    die("Erro na preparação da query: " . $conn->error);
}

$conn->close();
?>