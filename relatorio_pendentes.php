<?php
// Este arquivo não precisa de conexão com o banco de dados,
// pois os dados ($equipamentos_alerta) são passados pelo arquivo que o inclui (plano_manutencao.php).

if (!isset($equipamentos_alerta) || !is_array($equipamentos_alerta)) {
    // Garante que a variável exista para evitar erros.
    $equipamentos_alerta = [];
}
?>

<?php if (!empty($equipamentos_alerta)): ?>
<!-- Modal de Alerta de Manutenção -->
<div id="alert-modal" class="modal-overlay active">
    <div class="modal-content alert-modal-content">
        <span class="modal-close-btn" onclick="closeModalById('alert-modal')">&times;</span>
        <h2><i class="fa-solid fa-triangle-exclamation" style="color: #f57c00;"></i> Alerta de Manutenção</h2>
        <p>Os seguintes equipamentos possuem manutenção preventiva <strong>vencida</strong> ou <strong>prestes a vencer</strong>:</p>

        <div id="alert-print-area">
            <table class="alert-table">
                <thead>
                    <tr>
                        <th>Equipamento</th>
                        <th>TAG</th>
                        <th>Setor</th>
                        <th>Próxima Manutenção</th>
                        <th>Status</th>
                        <th class="no-print">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($equipamentos_alerta as $equip): ?>
                    <tr class="<?php echo getStatusClass($equip['dias_restantes']); ?>">
                        <td><?php echo htmlspecialchars($equip['nome']); ?></td>
                        <td><?php echo htmlspecialchars($equip['tag']); ?></td>
                        <td><?php echo htmlspecialchars($equip['setor']); ?></td>
                        <td><?php echo date("d/m/Y", strtotime($equip['proxima_manutencao'])); ?></td>
                        <td>
                            <?php
                                if ($equip['dias_restantes'] < 0) {
                                    echo 'Vencido há ' . abs($equip['dias_restantes']) . ' dias';
                                } else {
                                    echo 'Vence em ' . $equip['dias_restantes'] . ' dia(s)';
                                }
                            ?>
                        </td>
                        <td class="no-print">
                            <a href="editar_equipamento.php?id=<?php echo $equip['id']; ?>" class="btn action-btn btn-edit" style="font-size: 12px;">Editar Data</a>
                        </td>                                
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="alert-modal-actions no-print">
            <button onclick="printModalContent('alert-print-area', 'Relatório de Manutenções Pendentes')" class="btn btn-print"><i class="fa-solid fa-print"></i>&nbsp; Imprimir Lista</button>
        </div>
    </div>
</div>
<?php endif; ?>