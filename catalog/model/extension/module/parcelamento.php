<?php
class ModelExtensionModuleParcelamento extends Model {
    public function getDiscountPrice($product_id, $quantity) {
        $query = $this->db->query("
            SELECT price FROM `" . DB_PREFIX . "product_discount`
            WHERE product_id = '" . (int) $product_id . "'
              AND customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "'
              AND quantity <= '" . (int) $quantity . "'
              AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW()))
              ORDER BY quantity DESC, priority ASC, price ASC LIMIT 1
        ");

        if ($query->num_rows) {
            return (float) $query->row['price'];
        }

        return false;
    }
}
