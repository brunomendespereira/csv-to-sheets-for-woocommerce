<?php
namespace GoogleSheetsIntegration\Functions;

class WarrantyDisplay {
    public function __construct() {
        // Registra o shortcode para exibir a garantia do produto
        add_shortcode('display_product_warranty', [$this, 'display_product_warranty_handler']);
    }

    public function display_product_warranty_handler($atts) {
        // Atributos padrões
        $atts = shortcode_atts([
            'product_id' => get_the_ID(), // Pega o ID do produto na página atual se não for passado
        ], $atts, 'display_product_warranty');

        // Busca a garantia do produto
        $product_warranty = get_post_meta($atts['product_id'], '_warranty_hours', true);

        if (!empty($product_warranty)) {
            return "<div class='product-warranty'>$product_warranty</div>";
        }

        return "<div class='product-warranty'>Garantia não especificada</div>";
    }
}

new WarrantyDisplay();
