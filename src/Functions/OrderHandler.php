<?php
namespace GoogleSheetsIntegration\Functions;

class OrderHandler {

    public static function process_woocommerce_order($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('OrderHandler: Pedido não encontrado com ID ' . $order_id);
            return;
        }

        $all_files_processed = true;

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $download = $product->get_downloads();
            $download_url = reset($download)->get_file();
            $file_path = self::convert_url_to_path($download_url);

            if ($file_path && self::is_csv_file($file_path)) {
                $quantity = $item->get_quantity();
                $copied_rows = self::copy_rows_from_csv($file_path, $quantity);

                if ($copied_rows) {
                    $new_csv_path = self::create_csv_for_order($order_id, $copied_rows, $product, $order);
                    if ($new_csv_path) {
                        $link = self::convert_path_to_url($new_csv_path);
                        wc_update_order_item_meta($item->get_id(), '_csv_download_link', $link);
                        self::remove_rows_from_csv($file_path, $quantity);
                    } else {
                        $all_files_processed = false;
                        error_log('OrderHandler: Falha ao criar o CSV para o pedido ' . $order_id);
                        break;
                    }
                } else {
                    $all_files_processed = false;
                    error_log('OrderHandler: Não foi possível copiar dados do CSV para o produto.');
                    break;
                }
            } else {
                $all_files_processed = false;
                error_log('OrderHandler: O caminho do arquivo CSV não foi convertido corretamente ou não é um arquivo CSV.');
                break;
            }
        }

        if ($all_files_processed) {
            // Não altera o status do pedido aqui, apenas adiciona uma nota ao pedido
            $order->add_order_note('Pedido processado e todos os arquivos CSV criados com sucesso. Aguardando período de garantia.');
        }
    }

    private static function is_csv_file($file_path) {
        return strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) === 'csv';
    }

    private static function convert_url_to_path($url) {
        $upload_dir = wp_upload_dir();
        $base_url = $upload_dir['baseurl'];
        $base_dir = $upload_dir['basedir'];
        if (strpos($url, $base_url) === 0) {
            return $base_dir . substr($url, strlen($base_url));
        }
        return false;
    }

    private static function convert_path_to_url($path) {
        $upload_dir = wp_upload_dir();
        $base_url = $upload_dir['baseurl'];
        $base_dir = $upload_dir['basedir'];
        if (strpos($path, $base_dir) === 0) {
            return $base_url . substr($path, strlen($base_dir));
        }
        return false;
    }

    private static function copy_rows_from_csv($file_path, $num_rows) {
        $rows = [];
        if (($handle = fopen($file_path, 'r')) !== FALSE) {
            $header = fgetcsv($handle); // Captura o cabeçalho
            for ($i = 0; $i < $num_rows && ($data = fgetcsv($handle)) !== FALSE; $i++) {
                $rows[] = $data;
            }
            fclose($handle);
        }
        return ['header' => $header, 'rows' => $rows];
    }

    private static function create_csv_for_order($order_id, $data, $product, $order) {
        $upload_dir = wp_upload_dir();
        $order_dir = $upload_dir['basedir'] . '/order_csvs';
        if (!file_exists($order_dir)) {
            mkdir($order_dir, 0755, true);
        }
        $new_csv_path = $order_dir . '/order_' . $order_id . '.csv';
        if (($handle = fopen($new_csv_path, 'w')) !== FALSE) {
            // Adiciona as informações do pedido na primeira linha
            $warranty_hours = get_post_meta($product->get_id(), '_warranty_hours', true);
            $purchase_date = $order->get_date_created()->date('d/m/Y H:i:s'); // Formato brasileiro
            $warranty_valid_until = date('d/m/Y H:i:s', strtotime("$purchase_date +$warranty_hours hours")); // Formato brasileiro
            $info = [
                $product->get_name(),
                $order_id,
                $warranty_hours,
                $purchase_date,
                $warranty_valid_until,
                $data['rows'] ? count($data['rows']) : 0
            ];
            fputcsv($handle, $info);
            // Adiciona o cabeçalho e as linhas de dados
            fputcsv($handle, $data['header']);
            foreach ($data['rows'] as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
            return $new_csv_path;
        }
        return false;
    }

    private static function remove_rows_from_csv($file_path, $num_rows) {
        $temp_file_path = $file_path . '.tmp';
        if (($input_handle = fopen($file_path, 'r')) !== FALSE && ($output_handle = fopen($temp_file_path, 'w')) !== FALSE) {
            $header = fgetcsv($input_handle); // Captura o cabeçalho
            fputcsv($output_handle, $header); // Adiciona o cabeçalho ao novo arquivo

            for ($i = 0; $i < $num_rows; $i++) {
                fgetcsv($input_handle); // Remove as linhas do arquivo original
            }

            while (($data = fgetcsv($input_handle)) !== FALSE) {
                fputcsv($output_handle, $data);
            }

            fclose($input_handle);
            fclose($output_handle);
            rename($temp_file_path, $file_path);
        }
    }
}

new OrderHandler();
