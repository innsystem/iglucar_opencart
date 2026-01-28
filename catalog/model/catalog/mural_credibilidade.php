<?php
class ModelCatalogMuralCredibilidade extends Model {
    public function getActiveMuralImages() {
        $query = $this->db->query("SELECT m.*, mi.image, mi.sort_order FROM " . DB_PREFIX . "mural_credibilidade m LEFT JOIN " . DB_PREFIX . "mural_credibilidade_image mi ON (m.mural_id = mi.mural_id) WHERE m.status = '1' ORDER BY m.sort_order ASC, mi.sort_order ASC");

        return $query->rows;
    }
}
