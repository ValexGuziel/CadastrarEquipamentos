<?php
// Configuração do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sistema_cadastro_manutencao";

// Conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica conexão
if ($conn->connect_error) {
    // Em caso de erro de conexão, retorna um erro genérico
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Falha na conexão com o banco de dados.']);
    exit();
}

$tag = isset($_GET['tag']) ? $_GET['tag'] : '';

$response = ['exists' => false];

if (!empty($tag)) {
    $sql = "SELECT id FROM equipamentos WHERE tag = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $tag);
    $stmt->execute();
    $stmt->store_result(); // Necessário para verificar num_rows
    if ($stmt->num_rows > 0) {
        $response['exists'] = true;
    }
    $stmt->close();
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>