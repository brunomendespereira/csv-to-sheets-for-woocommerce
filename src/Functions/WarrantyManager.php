<?php
namespace GoogleSheetsIntegration\Functions;

class WarrantyManager {

    public function __construct() {
        add_action('admin_post_process_warranty_request', [$this, 'process_warranty_request']);
    }

    public function process_warranty_request() {
        // Verificar se os dados necessários estão presentes
        if (!isset($_POST['order_id']) || !isset($_POST['items'])) {
            wp_die('Dados inválidos.');
        }

        // Capturar o número do pedido e itens do formulário
        $order_id = intval($_POST['order_id']);
        $items = $_POST['items']; // Array de índices e justificativas

        // Construir o caminho do arquivo CSV
        $upload_dir = wp_upload_dir();
        $csv_path = $upload_dir['basedir'] . '/order_csvs/order_' . $order_id . '.csv';

        if (!file_exists($csv_path)) {
            wp_die('Arquivo CSV não encontrado.');
        }

        // Carregar os dados do CSV
        $csv_data = array_map('str_getcsv', file($csv_path));

        // Atualizar as linhas solicitadas
        foreach ($items as $exchange_item) {
            $index = intval($exchange_item['index']);
            $justification = sanitize_text_field($exchange_item['justification']);
            if (isset($csv_data[$index])) {
                $csv_data[$index][] = 'troca_solicitada=' . $justification;
            }
        }

        // Salvar os dados atualizados de volta no CSV sem aspas desnecessárias
        $handle = fopen($csv_path, 'w');
        foreach ($csv_data as $row) {
            foreach ($row as &$field) {
                if (strpos($field, ' ') !== false) {
                    $field = '"' . $field . '"'; // Adicionar aspas somente se houver espaço
                }
            }
            fputcsv($handle, $row, ',', chr(0)); // Usar chr(0) como delimitador de campo
        }
        fclose($handle);

        // Redirecionar para a URL atual
        wp_redirect($_SERVER['HTTP_REFERER']);
        exit;
    }
}

new WarrantyManager();
