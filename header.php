<?php
// Se a sessão não foi iniciada, inicie-a.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<nav class="main-nav">
    <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"><i class="fa-solid fa-plus"></i> Cadastrar Equipamento</a>
    <a href="listar_equipamentos.php" class="<?php echo ($current_page == 'listar_equipamentos.php') ? 'active' : ''; ?>"><i class="fa-solid fa-list"></i>Equipamentos Cadastrados</a>
    <a href="plano_manutencao.php" class="<?php echo ($current_page == 'plano_manutencao.php') ? 'active' : ''; ?>"><i class="fa-solid fa-calendar-check"></i> Plano de Manutenção</a>
    <a href="relatorios.php" class="<?php echo ($current_page == 'relatorios.php') ? 'active' : ''; ?>"><i class="fa-solid fa-chart-line"></i> MTBF / MTTR</a>
    <a href="auth.php?action=logout" class="btn-sair-nav"><i class="fa-solid fa-right-from-bracket"></i> Log out</a>
</nav>