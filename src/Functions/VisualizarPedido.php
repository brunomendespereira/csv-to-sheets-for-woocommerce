<?php
namespace GoogleSheetsIntegration\Functions;

class VisualizarPedido {
    
    public function __construct() {
        add_shortcode('visualizar_pedido', [$this, 'render_visualizar_pedido_template']);
    }

    public function render_visualizar_pedido_template($atts) {
        ob_start();
        $this->render_template();
        return ob_get_clean();
    }

    private function render_template() {
        // Pega o número do pedido a partir do parâmetro da URL
        $order_id = isset($_GET['order']) ? intval($_GET['order']) : 0;

        if ($order_id) {
            // Construir a URL do CSV
            $upload_dir = wp_upload_dir();
            $csv_url = $upload_dir['baseurl'] . '/order_csvs/order_' . $order_id . '.csv';

            // Verificar se o arquivo CSV existe
            $csv_path = $upload_dir['basedir'] . '/order_csvs/order_' . $order_id . '.csv';
            if (file_exists($csv_path)) {
                // Ler o CSV
                $csv_data = array_map('str_getcsv', file($csv_path));

                // Passar os dados do CSV para o template
                include plugin_dir_path(__FILE__) . '../../templates/visualizar-pedido-template.php';
            } else {
                echo '<p>Pedido não encontrado.</p>';
            }
        } else {
            echo '<p>Número do pedido não especificado.</p>';
        }
    }
}

new VisualizarPedido();
