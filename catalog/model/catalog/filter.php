<?php
class ModelCatalogFilter extends Model {
    public function getFilterAutocomplete($data = array()) {
        $sql = "SELECT DISTINCT fd.name FROM " . DB_PREFIX . "filter f 
                LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) 
                LEFT JOIN " . DB_PREFIX . "filter_group fg ON (f.filter_group_id = fg.filter_group_id) 
                LEFT JOIN " . DB_PREFIX . "filter_group_description fgd ON (fg.filter_group_id = fgd.filter_group_id) 
                WHERE fd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
                AND fgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        
        if (!empty($data['filter_name'])) {
            $search_term = $this->db->escape($data['filter_name']);
            // Buscar por nome completo que comece com o termo OU que contenha o termo em qualquer parte (case-insensitive)
            $sql .= " AND (LOWER(fd.name) LIKE LOWER('" . $search_term . "%') OR LOWER(fd.name) LIKE LOWER('%" . $search_term . "%'))";
        }
        
        $sql .= " ORDER BY 
                CASE 
                    WHEN LOWER(fd.name) LIKE LOWER('" . $this->db->escape($data['filter_name']) . "%') THEN 1 
                    ELSE 2 
                END, 
                fd.name ASC";
        
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
    
    public function getProductsByFilter($filter_name) {
        $sql = "SELECT DISTINCT p.product_id, pd.name, p.image, p.price 
                FROM " . DB_PREFIX . "product p 
                LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
                LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p.product_id = pf.product_id) 
                LEFT JOIN " . DB_PREFIX . "filter f ON (pf.filter_id = f.filter_id) 
                LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) 
                WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
                AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
                AND p.status = '1' 
                AND fd.name = '" . $this->db->escape($filter_name) . "'";
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    public function getModelsByBrand($data = array()) {
        $sql = "SELECT DISTINCT fd.name, COUNT(DISTINCT pf.product_id) as products_count
                FROM " . DB_PREFIX . "filter f 
                LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) 
                LEFT JOIN " . DB_PREFIX . "product_filter pf ON (f.filter_id = pf.filter_id)
                WHERE fd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        
        if (!empty($data['filter_brand'])) {
            $sql .= " AND fd.name LIKE '" . $this->db->escape($data['filter_brand']) . "%'";
        }
        
        $sql .= " GROUP BY fd.name ORDER BY fd.name ASC";
        
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
    
    public function getModelsByManufacturer($data = array()) {
        $sql = "SELECT DISTINCT fd.name, COUNT(DISTINCT pf.product_id) as products_count
                FROM " . DB_PREFIX . "filter f 
                LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) 
                LEFT JOIN " . DB_PREFIX . "product_filter pf ON (f.filter_id = pf.filter_id)
                LEFT JOIN " . DB_PREFIX . "manufacturer m ON (SUBSTRING_INDEX(fd.name, ' ', 1) = m.name)
                WHERE fd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        
        if (!empty($data['filter_manufacturer_id'])) {
            $sql .= " AND m.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
        }
        
        $sql .= " GROUP BY fd.name ORDER BY fd.name ASC";
        
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
     * Obtém filtros com suas imagens
     */
    public function getFiltersWithImages($data = array()) {
        $sql = "SELECT f.filter_id, fd.name, f.filter_group_id, f.sort_order,
                       fdi.filter_image_id, fdi.image, fdi.sort_order as image_sort_order
                FROM " . DB_PREFIX . "filter f 
                LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) 
                LEFT JOIN " . DB_PREFIX . "filter_description_images fdi ON (f.filter_id = fdi.filter_id AND fd.language_id = fdi.language_id)
                WHERE fd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        
        if (!empty($data['filter_group_id'])) {
            $sql .= " AND f.filter_group_id = '" . (int)$data['filter_group_id'] . "'";
        }
        
        if (!empty($data['filter_name'])) {
            $sql .= " AND fd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }
        
        $sql .= " ORDER BY f.sort_order ASC, fdi.sort_order ASC";
        
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
        
        // Organizar resultados por filtro
        $filters = array();
        foreach ($query->rows as $row) {
            if (!isset($filters[$row['filter_id']])) {
                $filters[$row['filter_id']] = array(
                    'filter_id' => $row['filter_id'],
                    'name' => $row['name'],
                    'filter_group_id' => $row['filter_group_id'],
                    'sort_order' => $row['sort_order'],
                    'images' => array()
                );
            }
            
            if ($row['filter_image_id']) {
                $filters[$row['filter_id']]['images'][] = array(
                    'filter_image_id' => $row['filter_image_id'],
                    'image' => $row['image'],
                    'image_url' => 'image/' . $row['image'], // Caminho completo para exibição
                    'sort_order' => $row['image_sort_order']
                );
            }
        }
        
        return array_values($filters);
    }
    
    /**
     * Obtém um filtro específico por nome exato
     */
    public function getFilterByName($filter_name, $language_id = null) {
        if (!$language_id) {
            $language_id = (int)$this->config->get('config_language_id');
        }
        
        $sql = "SELECT f.filter_id, f.filter_group_id, f.sort_order, fd.name
                FROM " . DB_PREFIX . "filter f 
                LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) 
                WHERE fd.language_id = '" . (int)$language_id . "' 
                AND fd.name = '" . $this->db->escape($filter_name) . "'";
        
        $query = $this->db->query($sql);
        
        if ($query->num_rows) {
            return $query->row;
        }
        
        return false;
    }

    /**
     * Obtém imagens de um filtro específico
     */
    public function getFilterImages($filter_id, $language_id = null) {
        if (!$language_id) {
            $language_id = (int)$this->config->get('config_language_id');
        }
        
        // Buscar imagens diretamente usando filter_id (que é o mesmo valor em ambas as tabelas)
        $sql = "SELECT fdi.filter_image_id, fdi.image, fdi.sort_order 
                FROM " . DB_PREFIX . "filter_description_images fdi
                WHERE fdi.filter_id = '" . (int)$filter_id . "' 
                AND fdi.language_id = '" . (int)$language_id . "' 
                ORDER BY fdi.sort_order ASC";
        
        $query = $this->db->query($sql);
        
        // Adicionar caminho completo para exibição
        $images = array();
        foreach ($query->rows as $image) {
            $images[] = array(
                'filter_image_id' => $image['filter_image_id'],
                'image' => $image['image'],
                'image_url' => 'image/' . $image['image'], // Caminho completo para exibição
                'sort_order' => $image['sort_order']
            );
        }
        
        return $images;
    }
}
