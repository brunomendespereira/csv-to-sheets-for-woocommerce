<?php
namespace GoogleSheetsIntegration\Admin;

class ProductFields {
    public function __construct() {
        add_action('woocommerce_product_options_general_product_data', [$this, 'add_custom_product_fields']);
        add_action('woocommerce_process_product_meta', [$this, 'save_custom_product_fields']);
    }

    public function add_custom_product_fields() {
        echo '<div class="options_group">';
        
        woocommerce_wp_text_input([
            'id'          => '_warranty_hours',
            'label'       => __('Tempo de Garantia (horas)', 'woocommerce'),
            'description' => __('Insira o tempo de garantia do produto em horas.', 'woocommerce'),
            'desc_tip'    => true,
            'type'        => 'number',
            'custom_attributes' => [
                'step' => 'any',
                'min' => '0'
            ],
            'value'       => '0'  // Define um valor padrão para o campo
        ]);

        echo '</div>';
    }

    public function save_custom_product_fields($post_id) {
        // Verifica se o campo foi enviado e não está vazio, senão atribui '0'
        $warranty_hours = isset($_POST['_warranty_hours']) && !empty($_POST['_warranty_hours']) ? sanitize_text_field($_POST['_warranty_hours']) : '0';
        
        update_post_meta($post_id, '_warranty_hours', $warranty_hours);
    }
}

new ProductFields();
