<?php
namespace GoogleSheetsIntegration\Dokan;

class ProductFieldsDokan {
    public function __construct() {
        add_action('dokan_product_edit_after_main', [$this, 'add_warranty_field']);
        add_action('dokan_new_product_added', [$this, 'save_warranty_field'], 10, 2);
        add_action('dokan_product_updated', [$this, 'save_warranty_field'], 10, 2);
    }

    public function add_warranty_field() {
        // Obtenha o valor atual ou defina como '0' se não estiver definido
        $warranty_hours = get_post_meta(get_the_ID(), '_warranty_hours', true);
        $warranty_hours = ($warranty_hours === '') ? '0' : $warranty_hours;
        ?>
        <div class="dokan-form-group">
            <label for="warranty_hours"><?php esc_html_e('Tempo de Garantia (em horas)', 'dokan'); ?></label>
            <input type="number" id="warranty_hours" name="warranty_hours" class="dokan-form-control" placeholder="<?php esc_attr_e('Ex: 24', 'dokan'); ?>" value="<?php echo esc_attr($warranty_hours); ?>" min="0" step="1">
        </div>
        <?php
    }

    public function save_warranty_field($product_id) {
        // Se o campo estiver vazio, atribua '0'
        $warranty_hours = isset($_POST['warranty_hours']) && $_POST['warranty_hours'] !== '' ? sanitize_text_field($_POST['warranty_hours']) : '0';
        update_post_meta($product_id, '_warranty_hours', $warranty_hours);
    }
}

new ProductFieldsDokan();
