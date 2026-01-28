<?php
class ModelExtensionModuleVehicleCoverSection extends Model {
    public function getManufacturers() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer WHERE status = '1' ORDER BY sort_order, name");
        return $query->rows;
    }

    public function getFilters($data = array()) {
        $sql = "SELECT f.filter_id, fd.name, f.filter_group_id FROM " . DB_PREFIX . "filter f LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) WHERE fd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND fd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (isset($data['filter_group_id'])) {
            $sql .= " AND f.filter_group_id = '" . (int)$data['filter_group_id'] . "'";
        }

        $sql .= " ORDER BY fd.name";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * Obtém todos os grupos de filtros disponíveis
     */
    public function getFilterGroups() {
        $sql = "SELECT fg.filter_group_id, fgd.name FROM " . DB_PREFIX . "filter_group fg LEFT JOIN " . DB_PREFIX . "filter_group_description fgd ON (fg.filter_group_id = fgd.filter_group_id) WHERE fgd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY fgd.name";
        
        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * Obtém filtros agrupados por grupos com nomes dos grupos
     */
    public function getFiltersGrouped($data = array()) {
        $sql = "SELECT 
                    f.filter_id, 
                    fd.name as filter_name, 
                    f.filter_group_id,
                    fgd.name as group_name,
                    f.sort_order
                FROM " . DB_PREFIX . "filter f 
                LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) 
                LEFT JOIN " . DB_PREFIX . "filter_group_description fgd ON (f.filter_group_id = fgd.filter_group_id) 
                WHERE fd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
                AND fgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND fd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (isset($data['filter_group_id'])) {
            $sql .= " AND f.filter_group_id = '" . (int)$data['filter_group_id'] . "'";
        }

        if (isset($data['group_name'])) {
            $sql .= " AND fgd.name LIKE '%" . $this->db->escape($data['group_name']) . "%'";
        }

        $sql .= " ORDER BY fgd.name, f.sort_order, fd.name";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 200;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * Busca por grupos de filtros que podem conter marcas de veículos
     */
    public function getVehicleBrandGroups() {
        $sql = "SELECT DISTINCT 
                    fg.filter_group_id, 
                    fgd.name as group_name
                FROM " . DB_PREFIX . "filter_group fg 
                LEFT JOIN " . DB_PREFIX . "filter_group_description fgd ON (fg.filter_group_id = fgd.filter_group_id) 
                WHERE fgd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
                AND (
                    LOWER(fgd.name) LIKE '%marca%' 
                    OR LOWER(fgd.name) LIKE '%brand%' 
                    OR LOWER(fgd.name) LIKE '%fabricante%' 
                    OR LOWER(fgd.name) LIKE '%veículo%' 
                    OR LOWER(fgd.name) LIKE '%carro%' 
                    OR LOWER(fgd.name) LIKE '%auto%'
                    OR fgd.name IN ('Marcas', 'Brands', 'Fabricantes', 'Veículos', 'Carros', 'Automóveis')
                )
                ORDER BY fgd.name";
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
}
