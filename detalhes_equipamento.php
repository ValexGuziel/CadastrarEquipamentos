<?php
// Verifica se o ID foi passado na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID do equipamento não fornecido.");
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

// --- Busca dados do equipamento ---
$sql_equip = "SELECT id, nome, tag, setor, descricao, foto FROM equipamentos WHERE id = ?";
$stmt_equip = $conn->prepare($sql_equip);
$stmt_equip->bind_param("i", $equipamento_id);
$stmt_equip->execute();
$result_equip = $stmt_equip->get_result();

if ($result_equip->num_rows === 0) {
    die("Equipamento não encontrado.");
}
$equipamento = $result_equip->fetch_assoc();

// --- Busca histórico de manutenções ---
$sql_manut = "SELECT data_manutencao, tipo_manutencao, descricao, responsavel, custo, data_inicio_reparo, data_fim_reparo,
                     TIMESTAMPDIFF(HOUR, data_inicio_reparo, data_fim_reparo) as duracao_reparo_horas
              FROM manutencoes WHERE equipamento_id = ? ORDER BY data_manutencao DESC";
$stmt_manut = $conn->prepare($sql_manut);
$stmt_manut->bind_param("i", $equipamento_id);
$stmt_manut->execute();
$result_manut = $stmt_manut->get_result();

// --- Lógica para botões de Navegação (Anterior/Próximo) ---
$sql_nav = "
    (SELECT id FROM equipamentos WHERE nome < ? ORDER BY nome DESC LIMIT 1)
    UNION ALL
    (SELECT id FROM equipamentos WHERE nome > ? ORDER BY nome ASC LIMIT 1)
";
$stmt_nav = $conn->prepare($sql_nav);
$stmt_nav->bind_param("ss", $equipamento['nome'], $equipamento['nome']);
$stmt_nav->execute();
$result_nav = $stmt_nav->get_result();
$nav_ids = $result_nav->fetch_all(MYSQLI_NUM);
 
// A primeira query (nome < ?) sempre retorna o ID anterior.
// A segunda query (nome > ?) sempre retorna o ID próximo.
// O resultado pode ter 0, 1 ou 2 linhas.
 
$id_anterior = null;
$id_proximo = null;
 
foreach ($nav_ids as $row) {
    $current_id = $row[0];
    // Precisamos re-consultar para saber se é anterior ou posterior
    $check_sql = $conn->query("SELECT nome FROM equipamentos WHERE id = $current_id")->fetch_assoc()['nome'];
    if ($check_sql < $equipamento['nome']) $id_anterior = $current_id;
    if ($check_sql > $equipamento['nome']) $id_proximo = $current_id;
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Equipamento: <?php echo htmlspecialchars($equipamento['nome']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
    <div class="modal-overlay" id="modal-overlay">
        <div class="container details-container" id="details-container">
            <?php include 'header.php'; ?>

            <!-- Botões de Ação -->
            <div class="details-actions">
                <?php if ($id_anterior): ?>
                    <a href="detalhes_equipamento.php?id=<?php echo $id_anterior; ?>" class="btn action-btn btn-nav"><i class="fa fa-arrow-left"></i> Anterior</a>
                <?php endif; ?>
                
                <button onclick="window.print()" class="btn action-btn btn-print"><i class="fa fa-print"></i> Imprimir</button>
                
                <?php if ($id_proximo): ?>
                    <a href="detalhes_equipamento.php?id=<?php echo $id_proximo; ?>" class="btn action-btn btn-nav">Próximo <i class="fa fa-arrow-right"></i></a>
                <?php endif; ?>
                <a href="javascript:window.history.back();" class="btn action-btn btn-back"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
                
            </div>
            
            <h1 class="details-title">Detalhes do Equipamento</h1>
    
            <div class="details-grid">
                <div>
                    <?php if (!empty($equipamento['foto'])) : ?>
                        <img src="uploads/<?php echo htmlspecialchars($equipamento['foto']); ?>" alt="Foto do Equipamento" class="equip-photo">
                    <?php endif; ?>
                </div>
                <div>
                    <div class="info-item">
                        <strong>TAG:</strong>
                        <span><?php echo htmlspecialchars($equipamento['tag']); ?></span>
                      
                    </div>
                    <div class="info-item">
                        <strong>Nome:</strong>
                        <span><?php echo htmlspecialchars($equipamento['nome']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Setor:</strong>
                        <span><?php echo htmlspecialchars($equipamento['setor']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Descrição:</strong>
                        <span><?php echo htmlspecialchars($equipamento['descricao']); ?></span>
                    </div>
                </div>
            </div>
    
            <h2>Histórico de Manutenções</h2>
            <table class="table-manutencoes">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Descrição do Serviço</th>
                        <th>Responsável</th>
                        <th>Custo (R$)</th>
                        <th>Tempo de Reparo (h)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_manut->num_rows > 0) : ?>
                        <?php while ($manut = $result_manut->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo date("d/m/Y", strtotime($manut['data_manutencao'])); ?></td>
                                <td><?php echo htmlspecialchars($manut['tipo_manutencao']); ?></td>
                                <td><?php echo htmlspecialchars($manut['descricao']); ?></td>
                                <td><?php echo htmlspecialchars($manut['responsavel']); ?></td>
                                <td><?php echo number_format($manut['custo'], 2, ',', '.'); ?></td>
                                <td><?php echo $manut['tipo_manutencao'] === 'Corretiva' && $manut['duracao_reparo_horas'] !== null ? htmlspecialchars($manut['duracao_reparo_horas']) : 'N/A'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="no-results">Nenhuma manutenção registrada para este equipamento.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Fecha a janela se o clique for no overlay (fundo)
        document.getElementById('modal-overlay').addEventListener('click', function(event) {
            // Verifica se o clique foi diretamente no overlay e não em um de seus filhos (o container)
            if (event.target === this) {
                window.history.back();
            }
        });
    </script>
</body>
</html>
<?php
$stmt_equip->close();
$stmt_nav->close();
$stmt_manut->close();
$conn->close();
?>