<?php
/**
 * Plugin Name: CSV to Sheets for WooCommerce
 * Description: Facilita a venda de dados através de arquivos CSV e utiliza produtos baixáveis do WooCommerce para gerar planilhas no Google Sheets.
 * Version: 1.2
 * Author: Bruno Mendes e Diogo Costa
 * Plugin URI: [URL do seu site ou página do plugin, se disponível]
 */

// Verifica se o arquivo foi chamado diretamente
if (!defined('WPINC')) {
    die;
}

// Adiciona o shortcode de status à área administrativa
add_shortcode('shortcode_status_connection', function() {
    return \GoogleSheetsIntegration\Functions\StatusConnection::statusConnection();
    exit;
});


// Carrega o JavaScript nas páginas de edição do produto Dokan para verificar se arquivo é csv
function enqueue_dashboard_script() {
    if (strpos($_SERVER['REQUEST_URI'], 'dashboard-vendedor') !== false) {
        wp_enqueue_script('validate-csv-upload-js', plugin_dir_url(__FILE__) . 'js/validate-csv-upload.js', array('jquery'), null, true);
        wp_enqueue_script('popup-js', plugin_dir_url(__FILE__) . 'js/popup.js', array('jquery'), null, true);
        wp_enqueue_script('auto-check-options-js', plugin_dir_url(__FILE__) . 'js/auto-check-options.js', array('jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_dashboard_script');


// Carrega o HTML e o CSS do popup a partir da pasta templates
add_action('wp_footer', 'add_csv_preview_popup');
function add_csv_preview_popup() {
    if (strpos($_SERVER['REQUEST_URI'], 'dashboard-vendedor') !== false) {
        include plugin_dir_path(__FILE__) . 'templates/popup.html';
    }
}


// Registra diretamente o hook do WooCommerce
add_action('woocommerce_order_status_pending_to_processing', ['GoogleSheetsIntegration\Functions\OrderHandler', 'process_woocommerce_order'], 10, 1);



// Inclui a classe StockNotification para exibir a notificação de estoque na página de edição do produto do Dokan
require_once plugin_dir_path(__FILE__) . 'src/Dokan/StockNotification.php';

// Inclui a classe ProductFields para criar o campo personalizado "Tempo de Garantia" na página de edição do produto do Woocommerce
require_once plugin_dir_path(__FILE__) . 'src/Admin/ProductFields.php';

// Inclui a classe ProductFieldsDokan para criar o campo personalizado "Tempo de Garantia" na página de edição do produto do Dokan
require_once plugin_dir_path(__FILE__) . 'src/Dokan/ProductFieldsDokan.php';

// Inclui a classe WarrantyDisplay para exibir a informação do tempo de garantia através de um shortcode
require_once plugin_dir_path(__FILE__) . 'src/Functions/WarrantyDisplay.php';

// Inclui a classe OrderHandler para criar a planilha do pedido
require_once plugin_dir_path(__FILE__) . 'src/Functions/OrderHandler.php';

// Inclui o arquivo WarrantyManager.php
require_once plugin_dir_path(__FILE__) . 'src/Functions/WarrantyManager.php';


// Filtrar os itens de download do WooCommerce
add_filter('woocommerce_order_get_downloadable_items', 'substituir_links_download', 10, 2);

function substituir_links_download($downloads, $order) {
    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();
        $download_link = wc_get_order_item_meta($item_id, '_csv_download_link', true);

        if ($download_link) {
            foreach ($downloads as &$download) {
                if ($download['product_id'] == $product_id) {
                    $download['download_url'] = $download_link;
                }
            }
        }
    }
    return $downloads;
}

// Adiciona um display customizado para o link do CSV nos detalhes do pedido na área administrativa
add_filter('woocommerce_order_item_display_meta_key', function($display_key, $meta, $item) {
    if ($meta->key === '_csv_download_link') {
        $display_key = 'Link para Download do CSV';
    }
    return $display_key;
}, 10, 3);

add_filter('woocommerce_order_item_display_meta_value', function($display_value, $meta, $item) {
    if ($meta->key === '_csv_download_link') {
        $display_value = '<a href="' . esc_url($meta->value) . '">' . esc_html($meta->value) . '</a>';
    }
    return $display_value;
}, 10, 3);

// Atualiza a página de agradecimento após a confirmação do pagamento com JavaScript
add_action('woocommerce_thankyou', 'add_autorefresh_script');
function add_autorefresh_script($order_id) {
    if (!isset($order_id)) return;
    ?>
    <script type="text/javascript">
    (function($){
        var reloadedKey = 'order-' + <?php echo $order_id; ?> + '-reloaded'; // Chave única por pedido
        var isReloaded = localStorage.getItem(reloadedKey); // Verifica o estado salvo no LocalStorage

        var checkOrderStatus = setInterval(function() {
            if (!isReloaded) { // Se a página ainda não foi recarregada após completar
                $.ajax({
                    url: '/wp-json/wc/v3/orders/<?php echo $order_id; ?>',
                    method: 'GET',
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('Authorization', 'Basic ' + btoa('<?php echo 'ck_4f8c874fefa4950359698e8c45c7b50d8813f16d'; ?>:<?php echo 'cs_f9e02fcd15cfe09a7451c1bfbc39a4c4b7fe20d2'; ?>'));
                    },
                    success: function (data) {
                        if (data.status === 'processing') {
                            clearInterval(checkOrderStatus); // Cancela o intervalo de verificação
                            localStorage.setItem(reloadedKey, 'true'); // Salva o estado de recarregado no LocalStorage
                            window.location.reload(); // Recarrega a página
                        }
                    }
                });
            } else {
                clearInterval(checkOrderStatus); // Cancela o intervalo se a página já foi recarregada
                $('.mp-details-pix, .mp-details-title').css('display', 'none'); // Esconde os elementos após o recarregamento
            }
        }, 5000);
    })(jQuery);
    </script>
    <?php
}


// Inclui a classe para o shortcode visualizar_pedido
require_once plugin_dir_path(__FILE__) . 'src/Functions/VisualizarPedido.php';