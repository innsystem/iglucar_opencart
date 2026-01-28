<?php
class ModelAccountOrderAttachment extends Model {
    
    public function getAttachments($order_id, $customer_id) {
        // First verify the order belongs to the customer
        $order_query = $this->db->query("SELECT order_id FROM " . DB_PREFIX . "order 
            WHERE order_id = '" . (int)$order_id . "' 
            AND customer_id = '" . (int)$customer_id . "'");
        
        if (!$order_query->num_rows) {
            return array();
        }
        
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_attachment 
            WHERE order_id = '" . (int)$order_id . "' 
            ORDER BY date_added DESC");
        
        return $query->rows;
    }
    
    public function getAttachment($order_attachment_id) {
        $query = $this->db->query("SELECT oa.*, o.customer_id FROM " . DB_PREFIX . "order_attachment oa
            LEFT JOIN " . DB_PREFIX . "order o ON (oa.order_id = o.order_id)
            WHERE oa.order_attachment_id = '" . (int)$order_attachment_id . "'");
        
        return $query->row;
    }
    
    public function getTotalAttachments($order_id, $customer_id) {
        // First verify the order belongs to the customer
        $order_query = $this->db->query("SELECT order_id FROM " . DB_PREFIX . "order 
            WHERE order_id = '" . (int)$order_id . "' 
            AND customer_id = '" . (int)$customer_id . "'");
        
        if (!$order_query->num_rows) {
            return 0;
        }
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "order_attachment 
            WHERE order_id = '" . (int)$order_id . "'");
        
        return $query->row['total'];
    }
}
