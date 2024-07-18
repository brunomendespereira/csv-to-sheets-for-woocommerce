<?php
namespace GoogleSheetsIntegration\Dokan;

class StockNotification {
    public function __construct() {
        // Adiciona transients quando um novo produto é criado ou atualizado
        add_action('woocommerce_new_product', array($this, 'set_stock_message_transient'), 10, 1);
        add_action('woocommerce_update_product', array($this, 'set_stock_message_transient'), 10, 1);

        // Exibe mensagens de transients no painel do vendedor e na área administrativa
add_action('dokan_product_edit_before_main', array($this, 'display_stock_message'));
add_action('admin_notices', array($this, 'display_stock_message'));

    }

    public function set_stock_message_transient($product_id) {
        $product = wc_get_product($product_id);
        if ($product) {
            $stock_quantity = $product->get_stock_quantity();
            if ($stock_quantity !== null) {
                set_transient('stock_update_message_' . $product_id, "Estoque atualizado: $stock_quantity unidades disponíveis.", 300);
            }
        }
    }

    public function display_stock_message() {
        // Verifica se está na página de edição do produto do Woocommerce
        $product_id = isset($_GET['post']) ? intval($_GET['post']) : null;

        // Verifica se está na página de edição do produto do Dokan
        if (!$product_id) {
            $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : null;
        }

        if ($product_id) {
            $message = get_transient('stock_update_message_' . $product_id);
            if ($message) {
                echo "<div class='notice notice-success is-dismissible'><p>$message</p></div>";
                delete_transient('stock_update_message_' . $product_id);
            }
        }
    }
}

new StockNotification();
