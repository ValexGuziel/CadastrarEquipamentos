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

// Define o período padrão como o ano corrente
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-01-01');
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-12-31');

// --- Lógica para calcular MTBF e MTTR por equipamento ---
$sql = "
    SELECT
        e.id,
        e.nome,
        e.tag,
        (
            SELECT AVG(TIMESTAMPDIFF(HOUR, m_prev.data_fim_reparo, m_curr.data_inicio_reparo))
            FROM manutencoes m_curr
            LEFT JOIN manutencoes m_prev ON m_prev.id = (
                SELECT id FROM manutencoes 
                WHERE equipamento_id = e.id AND tipo_manutencao = 'Corretiva' AND data_fim_reparo < m_curr.data_inicio_reparo
                ORDER BY data_fim_reparo DESC LIMIT 1
            )
            WHERE m_curr.equipamento_id = e.id
              AND m_curr.tipo_manutencao = 'Corretiva'
              AND m_curr.data_manutencao BETWEEN ? AND ?
        ) AS mtbf,
        (
            SELECT AVG(TIMESTAMPDIFF(HOUR, data_inicio_reparo, data_fim_reparo))
            FROM manutencoes
            WHERE equipamento_id = e.id
              AND tipo_manutencao = 'Corretiva'
              AND data_manutencao BETWEEN ? AND ?
        ) AS mttr
    FROM
        equipamentos e
    HAVING mtbf IS NOT NULL OR mttr IS NOT NULL
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $data_inicio, $data_fim, $data_inicio, $data_fim);
$stmt->execute();
$result = $stmt->get_result();

$dados_relatorio = [];
$labels_grafico = [];
$dados_mtbf = [];
$dados_mttr = [];

while ($row = $result->fetch_assoc()) {
    $dados_relatorio[] = $row;
    $labels_grafico[] = $row['nome'] . ' (' . $row['tag'] . ')';
    // Converte para horas e arredonda, tratando valores nulos
    $dados_mtbf[] = $row['mtbf'] !== null ? round($row['mtbf'], 2) : 0;
    $dados_mttr[] = $row['mttr'] !== null ? round($row['mttr'], 2) : 0;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório MTBF / MTTR</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-filters {
            display: flex;
            flex-direction:row;
            gap: 20px;
            align-items: center;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-bottom: 30px;
            margin-top: 30px;
            
        }
        .report-filters label {
            font-weight: bold;
            color: #355c7d;
            margin-right: 10px;
        }
        .report-filters input[type="date"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }       
        .chart-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 50px;
            margin-left: auto;
            margin-right: auto;
        }
        .container h1 {
            text-align: center;
            
        }
        @media (max-width: 900px) {
            .chart-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container container-large">
        
        <h1>Relatório de MTBF / MTTR</h1>
        <?php include 'header.php'; ?>
         <!-- Filtros -->
        <form action="relatorios.php" method="GET" class="report-filters">
            <div>
                <label for="data_inicio">Data Início:</label>
                <input type="date" name="data_inicio" id="data_inicio" value="<?php echo $data_inicio; ?>">
            </div>
            <div>
                <label for="data_fim">Data Fim:</label>
                <input type="date" name="data_fim" id="data_fim" value="<?php echo $data_fim; ?>">
            </div>
            <div>
                <button type="submit" class="btn report-filter-btn"><i class="fa fa-filter"></i> Filtrar</button>                
            </div>
        </form>

        <!-- Gráficos -->
        <div class="chart-container">
            <div>
                <canvas id="mtbfChart"></canvas>
            </div>
            <div>
                <canvas id="mttrChart"></canvas>
            </div>
        </div>

        <!-- Tabela de Dados -->
        <h2>Dados de Confiabilidade por Equipamento</h2>
        <table class="table-manutencoes">
            <thead>
                <tr>
                    <th>Equipamento</th>
                    <th>TAG</th>
                    <th>MTBF (horas)</th>
                    <th>MTTR (horas)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($dados_relatorio) > 0) : ?>
                    <?php foreach ($dados_relatorio as $dado) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dado['nome']); ?></td>
                            <td><?php echo htmlspecialchars($dado['tag']); ?></td>
                            <td><?php echo $dado['mtbf'] !== null ? number_format($dado['mtbf'], 2, ',', '.') : 'N/A'; ?></td>
                            <td><?php echo $dado['mttr'] !== null ? number_format($dado['mttr'], 2, ',', '.') : 'N/A'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4" class="no-results">Nenhum dado de manutenção corretiva encontrado para o período selecionado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        const chartOptions = {
            data: {
                labels: <?php echo json_encode($labels_grafico); ?>,
            },
            options: {
                // indexAxis: 'y', // Removido para o gráfico ficar na vertical
                plugins: {
                    title: {
                        display: true,
                        font: { size: 18 }
                    },
                    legend: {
                        display: false // Oculta a legenda, pois o título do gráfico já é descritivo
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + ' horas';
                            }
                        }
                    }
                }
            }
        };

        // Gráfico MTBF
        const ctxMtbf = document.getElementById('mtbfChart');
        const mtbfChartOptions = JSON.parse(JSON.stringify(chartOptions)); // Deep copy
        mtbfChartOptions.data.datasets = [{
            label: 'MTBF',
            data: <?php echo json_encode($dados_mtbf); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }];
        mtbfChartOptions.options.plugins.title.text = 'MTBF - Tempo Médio Entre Falhas (horas)';
        new Chart(ctxMtbf, {
            type: 'bar',
            ...mtbfChartOptions
        });

        // Gráfico MTTR
        const ctxMttr = document.getElementById('mttrChart');
        const mttrChartOptions = JSON.parse(JSON.stringify(chartOptions)); // Deep copy
        mttrChartOptions.data.datasets = [{
            label: 'MTTR',
            data: <?php echo json_encode($dados_mttr); ?>,
            backgroundColor: 'rgba(255, 99, 132, 0.8)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }];
        mttrChartOptions.options.plugins.title.text = 'MTTR - Tempo Médio Para Reparo (horas)';
        new Chart(ctxMttr, {
            type: 'bar',
            ...mttrChartOptions
        });
    </script>

</body>
</html>