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
$nome_equipamento = "Equipamento não encontrado";

// Se o ID do equipamento for passado via GET, busca o nome
if (isset($_GET['equipamento_id'])) {
    $equipamento_id = intval($_GET['equipamento_id']);
    $sql_nome = "SELECT nome FROM equipamentos WHERE id = ?";
    $stmt_nome = $conn->prepare($sql_nome);
    $stmt_nome->bind_param("i", $equipamento_id);
    $stmt_nome->execute();
    $result_nome = $stmt_nome->get_result();
    if ($result_nome->num_rows > 0) {
        $equipamento = $result_nome->fetch_assoc();
        $nome_equipamento = $equipamento['nome'];
    }
    $stmt_nome->close();
}

// Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validação e recebimento dos dados
    $equip_id_post = intval($_POST['equipamento_id']);
    $data_manutencao = $_POST['data_manutencao'];
    $tipo_manutencao = $_POST['tipo_manutencao'];
    $descricao = $_POST['descricao'];
    $responsavel = $_POST['responsavel'];
    $custo = !empty($_POST['custo']) ? floatval(str_replace(',', '.', $_POST['custo'])) : 0.0; // Converte vírgula para ponto
    $data_inicio_reparo = !empty($_POST['data_inicio_reparo']) ? $_POST['data_inicio_reparo'] : null;
    $data_fim_reparo = !empty($_POST['data_fim_reparo']) ? $_POST['data_fim_reparo'] : null;


    // Insere no banco
    $sql = "INSERT INTO manutencoes (equipamento_id, data_manutencao, tipo_manutencao, descricao, responsavel, custo, data_inicio_reparo, data_fim_reparo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("issssiss", $equip_id_post, $data_manutencao, $tipo_manutencao, $descricao, $responsavel, $custo, $data_inicio_reparo, $data_fim_reparo);

        // Se a manutenção for Preventiva, atualiza a data no equipamento
        if ($tipo_manutencao === 'Preventiva') {
            $sql_update_equip = "UPDATE equipamentos SET ultima_manutencao_preventiva = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update_equip);
            $stmt_update->bind_param("si", $data_manutencao, $equip_id_post);
            $stmt_update->execute();
            $stmt_update->close();
        }
        
        if ($stmt->execute()) {
            // Redireciona para a página de detalhes do equipamento após o sucesso
            header("Location: detalhes_equipamento.php?id=" . $equip_id_post . "&status=success");
            exit();
        } else {
            echo "Erro ao registrar manutenção: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Erro na preparação da query: " . $conn->error;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Manutenção</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
    <div class="container form-manutencao-container">
        <?php include 'header.php'; ?>
        <div class="page-header">
            <h1>Registrar Manutenção</h1>
            <a href="javascript:window.history.back();" class="btn btn-back"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
        </div>
        <p class="sub-header">Para o equipamento: <strong><?php echo htmlspecialchars($nome_equipamento); ?></strong></p>

        <form action="adicionar_manutencao.php" method="POST">
            <input type="hidden" name="equipamento_id" value="<?php echo $equipamento_id; ?>">

            <div class="form-group">
                <label for="data_manutencao">Data da Manutenção:</label>
                <input type="date" id="data_manutencao" name="data_manutencao" required>
            </div>

            <div class="form-group">
                <label for="tipo_manutencao">Tipo de Manutenção:</label>
                <select id="tipo_manutencao" name="tipo_manutencao" required>
                    <option value="Preventiva">Preventiva</option>
                    <option value="Corretiva">Corretiva</option>
                    <option value="Preditiva">Preditiva</option>
                </select>
            </div>

            <!-- Campos para Corretiva, inicialmente ocultos -->
            <div id="corretiva-fields" style="display: none;">
                <div class="form-group">
                    <label for="data_inicio_reparo">Início do Reparo (Data e Hora):</label>
                    <input type="datetime-local" id="data_inicio_reparo" name="data_inicio_reparo">
                </div>
                <div class="form-group">
                    <label for="data_fim_reparo">Fim do Reparo (Data e Hora):</label>
                    <input type="datetime-local" id="data_fim_reparo" name="data_fim_reparo">
                </div>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição do Serviço:</label>
                <textarea id="descricao" name="descricao" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label for="responsavel">Responsável:</label>
                <input type="text" id="responsavel" name="responsavel" required>
            </div>

            <div class="form-group">
                <label for="custo">Custo (R$):</label>
                <input type="text" id="custo" name="custo" placeholder="Ex: 150,50">
            </div>

            <button type="submit" class="btn submit-btn btn-manutencao">Registrar Manutenção</button>
        </form>
    </div>

    <script>
        document.getElementById('tipo_manutencao').addEventListener('change', function() {
            const corretivaFields = document.getElementById('corretiva-fields');
            const inicioReparoInput = document.getElementById('data_inicio_reparo');
            const fimReparoInput = document.getElementById('data_fim_reparo');

            if (this.value === 'Corretiva') {
                corretivaFields.style.display = 'block';
                inicioReparoInput.required = true;
                fimReparoInput.required = true;
            } else {
                corretivaFields.style.display = 'none';
                inicioReparoInput.required = false;
                fimReparoInput.required = false;
            }
        });
    </script>
</body>
</html>