<?php
class ModelSaleOrderAttachment extends Model {
    
    public function addAttachment($order_id, $filename, $path) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "order_attachment SET 
            order_id = '" . (int)$order_id . "', 
            filename = '" . $this->db->escape($filename) . "', 
            path = '" . $this->db->escape($path) . "', 
            date_added = NOW()");
        
        return $this->db->getLastId();
    }
    
    public function getAttachments($order_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_attachment 
            WHERE order_id = '" . (int)$order_id . "' 
            ORDER BY date_added DESC");
        
        return $query->rows;
    }
    
    public function getAttachment($order_attachment_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_attachment 
            WHERE order_attachment_id = '" . (int)$order_attachment_id . "'");
        
        return $query->row;
    }
    
    public function deleteAttachment($order_attachment_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "order_attachment 
            WHERE order_attachment_id = '" . (int)$order_attachment_id . "'");
    }
    
    public function sendNotification($order_id) {
        $this->load->model('sale/order');
        $this->load->model('setting/setting');
        
        $order_info = $this->model_sale_order->getOrder($order_id);
        
        if ($order_info && !empty($order_info['email'])) {
            $store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);
            
            $subject = sprintf('Novo anexo adicionado ao pedido #%s', $order_id);
            
            $attachments_link = HTTP_CATALOG . 'index.php?route=account/order_attachment&order_id=' . (int)$order_id;
            $message = sprintf(
                "Olá %s,\n\n" .
                "Um novo anexo foi adicionado ao seu pedido #%s.\n\n" .
                "Você pode visualizar os anexos clicando no link abaixo ou acessando sua conta em nossa loja.\n\n" .
                "%s\n\n" .
                "Atenciosamente,\n%s",
                $order_info['firstname'] . ' ' . $order_info['lastname'],
                $order_id,
                $attachments_link,
                $store_info['config_name'] ?? 'Loja'
            );
            
            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
            
            $mail->setTo($order_info['email']);
            $mail->setFrom($this->config->get('config_email'));
            $mail->setSender(html_entity_decode($store_info['config_name'] ?? 'Loja', ENT_QUOTES, 'UTF-8'));
            $mail->setSubject($subject);
            $mail->setText($message);
            $mail->send();
        }
    }
    
    public function getTotalAttachments($order_id) {
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "order_attachment 
            WHERE order_id = '" . (int)$order_id . "'");
        
        return $query->row['total'];
    }
}
