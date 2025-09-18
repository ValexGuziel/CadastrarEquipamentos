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
    die("Falha na conexão: " . $conn->connect_error);
}

$equipamento_id = 0;
$equipamento = null;

// --- Processa o formulário de atualização (quando enviado via POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validação e recebimento dos dados
    $equip_id_post = intval($_POST['id']);
    $nome = $_POST['nome'];
    $tag = $_POST['tag'];
    $setor = $_POST['setor'];
    $descricao = $_POST['descricao'];
    $intervalo_preventiva = !empty($_POST['manutencao_preventiva_intervalo']) ? intval($_POST['manutencao_preventiva_intervalo']) : null;
    $ultima_preventiva = !empty($_POST['ultima_manutencao_preventiva']) ? $_POST['ultima_manutencao_preventiva'] : null;


    // Prepara o SQL para UPDATE
    $sql = "UPDATE equipamentos SET nome = ?, tag = ?, setor = ?, descricao = ?, manutencao_preventiva_intervalo = ?, ultima_manutencao_preventiva = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssisi", $nome, $tag, $setor, $descricao, $intervalo_preventiva, $ultima_preventiva, $equip_id_post);
        
        if ($stmt->execute()) {
            // Redireciona para a lista de equipamentos após o sucesso
            header("Location: listar_equipamentos.php?status=updated");
            exit();
        } else {
            echo "Erro ao atualizar equipamento: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Erro na preparação da query: " . $conn->error;
    }
}

// --- Busca os dados do equipamento para preencher o formulário (quando a página é carregada via GET) ---
if (isset($_GET['id'])) {
    $equipamento_id = intval($_GET['id']);
    $sql_select = "SELECT id, nome, tag, setor, descricao, manutencao_preventiva_intervalo, ultima_manutencao_preventiva FROM equipamentos WHERE id = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $equipamento_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    if ($result->num_rows > 0) {
        $equipamento = $result->fetch_assoc();
    } else {
        die("Equipamento não encontrado.");
    }
    $stmt_select->close();
} else {
    die("ID do equipamento não fornecido.");
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Equipamento</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
    <div class="container form-edicao-container">
        <?php include 'header.php'; ?>
        <div class="page-header">
            <h1>Editar Equipamento</h1>
            <a href="javascript:window.history.back();" class="btn btn-back"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
        </div>


        <?php if ($equipamento) : ?>
            <form action="editar_equipamento.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $equipamento['id']; ?>">

                <div class="form-group">
                    <label for="nome">Nome do Equipamento:</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($equipamento['nome']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="tag">TAG de Identificação:</label>
                    <input type="text" id="tag" name="tag" value="<?php echo htmlspecialchars($equipamento['tag']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="setor">Setor:</label>
                    <input type="text" id="setor" name="setor" value="<?php echo htmlspecialchars($equipamento['setor']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição:</label>
                    <textarea id="descricao" name="descricao" rows="4" required><?php echo htmlspecialchars($equipamento['descricao']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="manutencao_preventiva_intervalo">Intervalo para Preventiva (dias):</label>
                    <input type="number" id="manutencao_preventiva_intervalo" name="manutencao_preventiva_intervalo" value="<?php echo htmlspecialchars($equipamento['manutencao_preventiva_intervalo']); ?>" placeholder="Ex: 30, 90, 180">
                </div>

                <div class="form-group">
                    <label for="ultima_manutencao_preventiva">Data da Última Preventiva:</label>
                    <input type="date" id="ultima_manutencao_preventiva" name="ultima_manutencao_preventiva" value="<?php echo htmlspecialchars($equipamento['ultima_manutencao_preventiva']); ?>">
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn btn-edicao">Salvar Alterações</button>
                    <a href="listar_equipamentos.php" class="btn btn-cancel">Cancelar</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <script>
        // Impede a seleção de datas futuras para a última manutenção preventiva
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const dateInput = document.getElementById('ultima_manutencao_preventiva');
            if(dateInput) {
                dateInput.setAttribute('max', today);
            }
        });
    </script>
</body>
</html>