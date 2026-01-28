<?php
class ModelCatalogMuralCredibilidade extends Model {
    public function addMural($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "mural_credibilidade SET title = '" . $this->db->escape($data['title']) . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "'");

        $mural_id = $this->db->getLastId();

        if (isset($data['mural_image'])) {
            foreach ($data['mural_image'] as $mural_image) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "mural_credibilidade_image SET mural_id = '" . (int)$mural_id . "', image = '" . $this->db->escape($mural_image['image']) . "', sort_order = '" . (int)$mural_image['sort_order'] . "'");
            }
        }

        return $mural_id;
    }

    public function editMural($mural_id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "mural_credibilidade SET title = '" . $this->db->escape($data['title']) . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "' WHERE mural_id = '" . (int)$mural_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "mural_credibilidade_image WHERE mural_id = '" . (int)$mural_id . "'");

        if (isset($data['mural_image'])) {
            foreach ($data['mural_image'] as $mural_image) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "mural_credibilidade_image SET mural_id = '" . (int)$mural_id . "', image = '" . $this->db->escape($mural_image['image']) . "', sort_order = '" . (int)$mural_image['sort_order'] . "'");
            }
        }
    }

    public function deleteMural($mural_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "mural_credibilidade WHERE mural_id = '" . (int)$mural_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "mural_credibilidade_image WHERE mural_id = '" . (int)$mural_id . "'");
    }

    public function getMural($mural_id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "mural_credibilidade WHERE mural_id = '" . (int)$mural_id . "'");

        return $query->row;
    }

    public function getMuralImages($mural_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mural_credibilidade_image WHERE mural_id = '" . (int)$mural_id . "' ORDER BY sort_order ASC");

        return $query->rows;
    }

    public function getMurals($data = array()) {
        $sql = "SELECT m.* FROM " . DB_PREFIX . "mural_credibilidade m";

        if (!empty($data['filter_title'])) {
            $sql .= " WHERE m.title LIKE '%" . $this->db->escape($data['filter_title']) . "%'";
        }

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $sql .= (!empty($data['filter_title']) ? " AND" : " WHERE") . " m.status = '" . (int)$data['filter_status'] . "'";
        }

        $sort_data = array(
            'm.title',
            'm.sort_order',
            'm.status'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY m.sort_order";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

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

    public function getTotalMurals() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "mural_credibilidade");

        return $query->row['total'];
    }
}
