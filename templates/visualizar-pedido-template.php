<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url(__FILE__); ?>../css/visualizar-pedido-template.css">
<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url(__FILE__); ?>../css/popupWarranty.css">

<?php
// Caminho para o arquivo popupWarranty.html dentro da pasta templates do plugin
$popup_template_path = plugin_dir_path(__FILE__) . '../templates/popupWarranty.html';

// Inclui o arquivo popupWarranty.html no template principal
if (file_exists($popup_template_path)) {
    include $popup_template_path;
} else {
    echo '<!-- Arquivo popupWarranty.html não encontrado -->';
}
?>

<div class="pedido-container">
    <p><strong>Produto:</strong> <?php echo esc_html($csv_data[0][0]); ?></p>
    <p><strong>Nº Pedido:</strong> <?php echo esc_html($csv_data[0][1]); ?></p>
    <p><strong>Garantia:</strong> Válida até - 
    <span id="warranty-valid-until" data-end="2024-08-01" >
        <?php echo esc_html($csv_data[0][4]); ?>
    </span> - <span id="warranty-status"></span></p>
    <hr>
    <h3><?php echo esc_html($csv_data[0][0]); ?>: Quantidade - <?php echo esc_html($csv_data[0][5]); ?> Und.</h3>
    <table class="table-original">
        <thead>
            <tr>
                <th id="checkbox-column"></th>
                <th id="number-column">Nº</th>
                <?php foreach ($csv_data[1] as $header) { ?>
                    <th><?php echo esc_html($header); ?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php 
            for ($i = 2; $i < count($csv_data); $i++) { 
                $is_exchange_requested = false;
                foreach ($csv_data[$i] as $cell) {
                    if (strpos($cell, 'troca_solicitada=') !== false) {
                        $is_exchange_requested = true;
                        break;
                    }
                }
            ?>
                <tr data-index="<?php echo $i; ?>" class="<?php echo $is_exchange_requested ? 'exchange-requested' : ''; ?>">
                    <td class="checkbox-column"><input type="checkbox" class="requestWarranty"></td>
                    <td class="number-column"><?php echo $i - 1; ?></td>
                    <?php 
                    foreach ($csv_data[$i] as $cell) { 
                        if (strpos($cell, 'troca_solicitada=') === false) { // Não exibir a célula "troca_solicitada"
                    ?>
                            <td><?php echo esc_html($cell); ?> <i class="fas fa-copy copy-icon" onclick="copyCell(this)"></i></td>
                    <?php 
                        }
                    } 
                    ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="<?php echo plugin_dir_url(__FILE__); ?>../js/copyCell.js"></script>
<script src="<?php echo plugin_dir_url(__FILE__); ?>../js/checkWarrantyStatus.js"></script>
<script src="<?php echo plugin_dir_url(__FILE__); ?>../js/popupWarranty.js"></script>
