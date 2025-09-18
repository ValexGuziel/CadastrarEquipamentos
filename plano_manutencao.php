<?php
include 'session_check.php';
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

// Lógica para buscar equipamentos com manutenção preventiva
// A query calcula a data da próxima manutenção e a compara com a data atual
$sql = "
    SELECT 
        id, 
        nome, 
        tag, 
        setor,
        ultima_manutencao_preventiva,
        manutencao_preventiva_intervalo,
        DATE_ADD(ultima_manutencao_preventiva, INTERVAL manutencao_preventiva_intervalo DAY) AS proxima_manutencao,
        DATEDIFF(DATE_ADD(ultima_manutencao_preventiva, INTERVAL manutencao_preventiva_intervalo DAY), CURDATE()) AS dias_restantes
    FROM 
        equipamentos
    WHERE 
        manutencao_preventiva_intervalo IS NOT NULL 
        AND manutencao_preventiva_intervalo > 0
    ORDER BY 
        dias_restantes ASC
";

$result = $conn->query($sql);

// --- Lógica para o Modal de Alerta ---
// Busca equipamentos com manutenção vencida ou a 1 dia de vencer.
$sql_alert = "
    SELECT id, nome, tag, setor, manutencao_preventiva_intervalo,
           DATE_ADD(ultima_manutencao_preventiva, INTERVAL manutencao_preventiva_intervalo DAY) AS proxima_manutencao,           
           DATEDIFF(DATE_ADD(ultima_manutencao_preventiva, INTERVAL manutencao_preventiva_intervalo DAY), CURDATE()) AS dias_restantes
    FROM equipamentos
    WHERE manutencao_preventiva_intervalo IS NOT NULL 
      AND manutencao_preventiva_intervalo > 0
      AND DATEDIFF(DATE_ADD(ultima_manutencao_preventiva, INTERVAL manutencao_preventiva_intervalo DAY), CURDATE()) <= 1
    ORDER BY dias_restantes ASC
";
$result_alert = $conn->query($sql_alert);
$equipamentos_alerta = [];
if ($result_alert && $result_alert->num_rows > 0) {
    while($row_alert = $result_alert->fetch_assoc()) {
        $equipamentos_alerta[] = $row_alert;
    }
}

// --- Lógica para exibir o modal de alerta apenas uma vez por sessão ---
$show_alert_modal = false;
if (!empty($equipamentos_alerta) && !isset($_SESSION['preventive_alert_shown'])) {
    $show_alert_modal = true;
    // Marca que o modal foi exibido nesta sessão
    $_SESSION['preventive_alert_shown'] = true;
}

function getStatusClass($dias) {
    if ($dias === null) return '';
    if ($dias < 0) return 'status-vencido';
    if ($dias <= 7) return 'status-urgente';
    if ($dias <= 30) return 'status-atencao';
    return 'status-ok';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plano de Manutenção Preventiva</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .status-vencido { background-color: #ffcdd2; color: #c62828; font-weight: bold; }
        .status-urgente { background-color: #ffecb3; color: #f57f17; }
        .status-atencao { background-color: #cdecf9; }
        .status-ok { background-color: #dcedc8; }
        .table-equipamentos .status-cell { text-align: center; }
        body {
            align-items: flex-start; /* Alinha o conteúdo no topo */
        }
        .container_equipamentos {
            display: flex;
            flex-direction: column;
        }
        .alert-modal-content { max-width: 800px; text-align: left; }
        .alert-modal-content h2 { text-align: center; color: var(--dark-blue); margin-top: 0; }
        .alert-modal-actions { display: flex; justify-content: center; gap: 15px; margin-top: 25px; }
        .alert-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .alert-table th, .alert-table td { padding: 8px 12px; border: 1px solid var(--border-color); text-align: left;}
        .alert-table th { background-color: var(--bg-color); }
    </style>
</head>

<body>
    <div class="container_equipamentos">
        <?php include 'header.php'; ?>
        <div class="page-header">
            <h1>Plano de Manutenção Preventiva</h1>
            <button onclick="window.print()" class="btn btn-print"><i class="fa-solid fa-print"></i> Imprimir Lista</button>
            <a href="javascript:window.history.back();" class="btn btn-back"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
        </div>
        
        <?php if ($show_alert_modal) {
            include 'relatorio_pendentes.php';
        } ?>

        <table class="table-equipamentos">
            <thead>
                <tr>
                    <th>Equipamento (TAG)</th>
                    <th>Setor</th>
                    <th>Última Preventiva</th>
                    <th>Próxima Preventiva</th>
                    <th class="status-cell">Status (Dias Restantes)</th>
                    <th class="no-print">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0) : ?>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr class="<?php echo getStatusClass($row['dias_restantes']); ?>">
                            <td><?php echo htmlspecialchars($row['nome'] . ' (' . $row['tag'] . ')'); ?></td>
                            <td><?php echo htmlspecialchars($row['setor']); ?></td>
                            <td><?php echo $row['ultima_manutencao_preventiva'] ? date("d/m/Y", strtotime($row['ultima_manutencao_preventiva'])) : 'N/A'; ?></td>
                            <td><?php echo $row['proxima_manutencao'] ? date("d/m/Y", strtotime($row['proxima_manutencao'])) : 'N/A'; ?></td>
                            <td class="status-cell">
                                <?php
                                    if ($row['dias_restantes'] === null) {
                                        echo 'Definir';
                                    } elseif ($row['dias_restantes'] < 0) {
                                        echo 'Vencido há ' . abs($row['dias_restantes']) . ' dias';
                                    } else {
                                        echo $row['dias_restantes'] . ' dias';
                                    }
                                ?>
                            </td>
                            <td class="no-print">
                                <a href="adicionar_manutencao.php?equipamento_id=<?php echo $row['id']; ?>" class="btn action-btn btn-maintenance">Registrar</a>
                                <a href="detalhes_equipamento.php?id=<?php echo $row['id']; ?>" class="btn action-btn btn-details">Ver Histórico</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="no-results">Nenhum equipamento com plano de manutenção preventiva configurado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Função genérica para fechar modais
        function closeModalById(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Adiciona listeners para fechar modais ao clicar no overlay
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
            // Listener para o botão de fechar
            const closeBtn = modal.querySelector('.modal-close-btn');
            if(closeBtn && !closeBtn.onclick) { // Evita adicionar listener duplicado
                 closeBtn.addEventListener('click', () => {
                    modal.style.display = 'none';
                });
            }
        });

        // Função para imprimir apenas o conteúdo do modal
        function printModalContent(elementId, title) {
            const printContent = document.getElementById(elementId).innerHTML;
            const printWindow = window.open('', '', 'height=600,width=800');
            const today = new Date().toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });

            printWindow.document.write('<html><head><title>' + title + '</title>');
            // Inclui o CSS para manter a formatação da tabela
            printWindow.document.write('<link rel="stylesheet" href="style.css" type="text/css" />');
            // Estilos específicos para a impressão
            printWindow.document.write(`
                <style>
                    body { padding: 30px; font-family: 'Roboto', sans-serif; align-items: flex-start; }
                    .print-header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #0d47a1; padding-bottom: 20px; }
                    .print-header h1 { font-size: 24px; color: #0d47a1; margin: 0; }
                    .print-header p { font-size: 14px; color: #6c757d; margin: 5px 0 0; }
                    .no-print { display: none; }
                    .alert-table { font-size: 12px; }
                </style>
            `);
            printWindow.document.write('</head><body>');
            printWindow.document.write('<div class="print-header"><h1>' + title + '</h1><p>Relatório gerado em: ' + today + '</p></div>');
            printWindow.document.write(printContent);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.onload = function() { printWindow.print(); printWindow.close(); };
        }
    </script>
</body>

</html>
<?php
$conn->close();
?>
