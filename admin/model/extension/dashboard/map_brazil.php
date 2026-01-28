<?php
class ModelExtensionDashboardMapBrazil extends Model
{
    public function getTotalOrdersByZone($data = array())
    {
        $sql = "SELECT COUNT(*) AS total, SUM(o.total) AS amount, z.code as code FROM `" . DB_PREFIX . "order` o LEFT JOIN `" . DB_PREFIX . "zone` z ON (o.payment_zone_id = z.zone_id) WHERE order_status_id > '0'";

        // Verifica se há status de pedido definidos
		if (!empty($data['config_complete_status']) && is_array($data['config_complete_status'])) {
			$status_ids = array_map('intval', $data['config_complete_status']); // Converte para inteiros
			$sql .= " AND order_status_id IN (" . implode(",", $status_ids) . ")";
		}

        $sql .= "AND o.payment_country_id = '30' GROUP BY o.payment_zone_id";

        $query = $this->db->query($sql);
	
		return $query->rows;
    }
}
