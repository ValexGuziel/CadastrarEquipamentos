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

// --- Lógica de Paginação e Busca ---
$items_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

$search_term = "";
$total_items = 0;

// Primeiro, contamos o total de itens (com ou sem busca)
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];
    $like_term = "%" . $search_term . "%";

    $sql_count = "SELECT COUNT(id) FROM equipamentos WHERE nome LIKE ? OR tag LIKE ?";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param("ss", $like_term, $like_term);
    $stmt_count->execute();
    $stmt_count->bind_result($total_items);
    $stmt_count->fetch();
    $stmt_count->close();

    // Agora, busca os itens da página atual com filtro
    $sql = "SELECT id, nome, tag, setor, descricao, foto, manutencao_preventiva_intervalo FROM equipamentos WHERE nome LIKE ? OR tag LIKE ? ORDER BY nome ASC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $like_term, $like_term, $items_per_page, $offset);
} else {
    // Conta todos os itens
    $result_count = $conn->query("SELECT COUNT(id) FROM equipamentos");
    $total_items = $result_count->fetch_row()[0];

    // Busca os itens da página atual sem filtro
    $sql = "SELECT id, nome, tag, setor, descricao, foto, manutencao_preventiva_intervalo FROM equipamentos ORDER BY nome ASC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $items_per_page, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$total_pages = ceil($total_items / $items_per_page);

// Adiciona uma verificação para garantir que a query foi executada com sucesso
if ($result === false) {
    die("Erro ao executar a consulta: " . $conn->error);
}

// --- Lógica para Mensagem de Status (Modal) ---
$status_message = '';
$status_class = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'deleted':
            $status_message = 'Equipamento excluído com sucesso!';
            $status_class = 'success';
            break;
        case 'updated':
            $status_message = 'Equipamento atualizado com sucesso!';
            $status_class = 'success';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Equipamentos</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
    <div class="container_equipamentos">
        <?php include 'header.php'; ?>

        <?php if (!empty($status_message)) : ?>
            <!-- Modal de Status -->
            <div id="status-modal" class="modal-overlay active">
                <div class="modal-content <?php echo $status_class; ?>">
                    <span class="modal-close-btn" onclick="closeModalById('status-modal')">&times;</span>
                    <div class="modal-icon">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <p><?php echo $status_message; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulário de Busca -->
        <form action="listar_equipamentos.php" method="GET" class="search-form">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
            <button type="submit" class="btn search-btn"><i class="fa fa-search"></i> Buscar</button>
        </form>


        <table class="table-equipamentos">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Foto</th>
                    <th>TAG</th>
                    <th>Nome</th>
                    <th>Setor</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0) : ?>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td>
                                <?php if (!empty($row['foto'])) : ?>
                                    <img src="uploads/<?php echo htmlspecialchars($row['foto']); ?>" alt="Foto do Equipamento" class="equip-photo-thumb">
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['tag']); ?></td>
                            <td><?php echo htmlspecialchars($row['nome']); ?></td>
                            <td><?php echo htmlspecialchars($row['setor']); ?></td>
                            <td>
                                <a href="detalhes_equipamento.php?id=<?php echo $row['id']; ?>" class="btn action-btn btn-details">Histórico</a>
                                <a href="adicionar_manutencao.php?equipamento_id=<?php echo $row['id']; ?>" class="btn action-btn btn-maintenance">Manutenção</a>
                                <a href="editar_equipamento.php?id=<?php echo $row['id']; ?>" class="btn action-btn btn-edit">Editar</a>
                                <a href="excluir_equipamento.php?id=<?php echo $row['id']; ?>" class="btn action-btn btn-delete" onclick="return confirm('Tem certeza que deseja excluir este equipamento?');">Excluir</a>
                                <?php if (!empty($row['manutencao_preventiva_intervalo']) && $row['manutencao_preventiva_intervalo'] > 0) : ?>
                                    <span class="preventiva-indicator"><i class="fa-solid fa-shield-alt"></i> Preventiva</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="no-results">Nenhum equipamento encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Navegação da Paginação -->
        <div class="pagination">
            <?php if ($page > 1) : ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_term); ?>" class="page-link">Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>" class="page-link <?php if ($i == $page) echo 'active'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages) : ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_term); ?>" class="page-link">Próxima</a>
            <?php endif; ?>
        </div>

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
    </script>
</body>

</html>
<?php
// O statement principal agora sempre existe
$stmt->close();
$conn->close();
?>