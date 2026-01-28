<?php
class ControllerCatalogProductQuickEdit extends Controller {
    private $error = array();
    
    public function updateField() {
        // Adicionar log aqui para verificar se o controller é acessado
        // $this->log->write('ProductQuickEdit: updateField accessed');

        $this->load->language('catalog/product_quick_edit');
        
        $json = array();
        
        // Verificar token de segurança
        if (!isset($this->session->data['user_token']) || !isset($this->request->post['user_token']) || ($this->session->data['user_token'] != $this->request->post['user_token'])) {
            $json['error'] = $this->language->get('error_token');
        }
        
        // Verificar permissões
        if (!$this->user->hasPermission('modify', 'catalog/product')) {
            $json['error'] = $this->language->get('error_permission');
        }
        
        // Validar dados obrigatórios
        if (!isset($this->request->post['product_id']) || !isset($this->request->post['field']) || !isset($this->request->post['value'])) {
            $json['error'] = $this->language->get('error_missing_data');
        }
        
        if (!$json) {
            $this->load->model('catalog/product');
            
            $product_id = (int)$this->request->post['product_id'];
            $field = $this->request->post['field'];
            $value = $this->request->post['value'];
            
            // Validar se produto existe
            $product_info = $this->model_catalog_product->getProduct($product_id);
            if (!$product_info) {
                $json['error'] = $this->language->get('error_product_not_found');
            } else {
                // Validar e sanitizar campo específico
                $validation_result = $this->validateField($field, $value);
                
                if ($validation_result['error']) {
                    $json['error'] = $validation_result['message'];
                } else {
                    try {
                        // Atualizar campo específico
                        $this->updateProductField($product_id, $field, $validation_result['value']);
                        
                        $json['success'] = $this->language->get('text_success_update');
                        $json['data'] = array(
                            'field' => $field,
                            'value' => $validation_result['value'],
                            'formatted_value' => $this->getFormattedValue($field, $validation_result['value'])
                        );
                        
                        // Log da alteração
                        $this->logChange($product_id, $field, $product_info[$field], $validation_result['value']);
                                                
                        // *** ADICIONADO: Limpar cache geral do produto ***
                        $this->cache->delete('product');
                    } catch (Exception $e) {
                        $json['error'] = $this->language->get('error_database');
                    }
                }
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function updateImage() {
        $this->load->language('catalog/product_quick_edit');
        
        $json = array();
        
        // Verificar token de segurança
        if (!isset($this->session->data['user_token']) || !isset($this->request->post['user_token']) || ($this->session->data['user_token'] != $this->request->post['user_token'])) {
            $json['error'] = $this->language->get('error_token');
        }
        
        // Verificar permissões
        if (!$this->user->hasPermission('modify', 'catalog/product')) {
            $json['error'] = $this->language->get('error_permission');
        }
        
        if (!$json) {
            $this->load->model('catalog/product');
            
            $product_id = (int)$this->request->post['product_id'];
            $image_path = $this->request->post['image_path'];
            
            // Validar se produto existe
            $product_info = $this->model_catalog_product->getProduct($product_id);
            if (!$product_info) {
                $json['error'] = $this->language->get('error_product_not_found');
            } else {
                // Validar imagem
                if ($this->validateImage($image_path)) {
                    try {
                        // Atualizar imagem
                        $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($image_path) . "' WHERE product_id = '" . (int)$product_id . "'");
                        
                        // Limpar cache de imagem
                        $this->load->model('tool/image');
                        $this->model_tool_image->resize($image_path, 40, 40);
                        
                        $json['success'] = $this->language->get('text_success_image');
                        $json['data'] = array(
                            'image_path' => $image_path,
                            'image_url' => $this->model_tool_image->resize($image_path, 40, 40)
                        );
                        
                        // Log da alteração
                        $this->logChange($product_id, 'image', $product_info['image'], $image_path);
                        
                        // *** ADICIONADO: Limpar cache geral do produto ***
                        $this->cache->delete('product');

                    } catch (Exception $e) {
                        $json['error'] = $this->language->get('error_database');
                    }
                } else {
                    $json['error'] = $this->language->get('error_invalid_image');
                }
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function toggleStatus() {
        $this->load->language('catalog/product_quick_edit');
        
        $json = array();
        
        // Verificar token de segurança
        if (!isset($this->session->data['user_token']) || !isset($this->request->post['user_token']) || ($this->session->data['user_token'] != $this->request->post['user_token'])) {
            $json['error'] = $this->language->get('error_token');
        }
        
        // Verificar permissões
        if (!$this->user->hasPermission('modify', 'catalog/product')) {
            $json['error'] = $this->language->get('error_permission');
        }
        
        if (!$json) {
            $this->load->model('catalog/product');
            
            $product_id = (int)$this->request->post['product_id'];
            
            // Validar se produto existe
            $product_info = $this->model_catalog_product->getProduct($product_id);
            if (!$product_info) {
                $json['error'] = $this->language->get('error_product_not_found');
            } else {
                try {
                    $new_status = $product_info['status'] ? 0 : 1;
                    
                    // Atualizar status
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET status = '" . (int)$new_status . "' WHERE product_id = '" . (int)$product_id . "'");
                    
                    $json['success'] = $this->language->get('text_success_status');
                    $json['data'] = array(
                        'status' => $new_status,
                        'status_text' => $new_status ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                        'button_class' => $new_status ? 'btn-success' : 'btn-danger'
                    );
                    
                    // Log da alteração
                    $this->logChange($product_id, 'status', $product_info['status'], $new_status);
                    
                } catch (Exception $e) {
                    $json['error'] = $this->language->get('error_database');
                }
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    private function validateField($field, $value) {
        $result = array('error' => false, 'value' => $value, 'message' => '');
        
        switch ($field) {
            case 'name':
                $value = trim(strip_tags($value));
                if (utf8_strlen($value) < 3) {
                    $result['error'] = true;
                    $result['message'] = $this->language->get('error_name_min');
                } elseif (utf8_strlen($value) > 100) {
                    $result['error'] = true;
                    $result['message'] = $this->language->get('error_name_max');
                }
                $result['value'] = $value;
                break;
                
            case 'model':
                $value = trim(strip_tags($value));
                if (utf8_strlen($value) > 64) {
                    $result['error'] = true;
                    $result['message'] = $this->language->get('error_model_max');
                }
                $result['value'] = $value;
                break;
                
            case 'price':
                $value = (float)$value;
                if ($value < 0) {
                    $result['error'] = true;
                    $result['message'] = $this->language->get('error_price_min');
                }
                $result['value'] = number_format($value, 4, '.', '');
                break;
                
            case 'quantity':
                $value = (int)$value;
                if ($value < 0) {
                    $result['error'] = true;
                    $result['message'] = $this->language->get('error_quantity_min');
                }
                $result['value'] = $value;
                break;
                
            default:
                $result['error'] = true;
                $result['message'] = $this->language->get('error_invalid_field');
                break;
        }
        
        return $result;
    }
    
    private function validateImage($image_path) {
        if (empty($image_path)) {
            return true; // Permitir imagem vazia
        }
        
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $extension = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowed_extensions)) {
            return false;
        }
        
        // Verificar se arquivo existe
        $full_path = DIR_IMAGE . $image_path;
        if (!file_exists($full_path)) {
            return false;
        }
        
        // Verificar tamanho do arquivo (max 2MB)
        if (filesize($full_path) > 2097152) {
            return false;
        }
        
        return true;
    }
    
    private function updateProductField($product_id, $field, $value) {
        $allowed_fields = array('name', 'model', 'price', 'quantity');
        
        if (in_array($field, $allowed_fields)) {
            if ($field == 'name') {
                // Atualizar na tabela product_description (name, meta_title e seo_url)
                $seo_url = $this->generateSeoUrl($value, $product_id);
                
                $this->db->query("UPDATE " . DB_PREFIX . "product_description SET 
                    name = '" . $this->db->escape($value) . "',
                    meta_title = '" . $this->db->escape($value) . "'
                    WHERE product_id = '" . (int)$product_id . "' 
                    AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
                
                // Atualizar SEO URL na tabela seo_url
                $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url 
                    WHERE query = 'product_id=" . (int)$product_id . "' 
                    AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
                
                if (!empty($seo_url)) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET 
                        store_id = '0',
                        language_id = '" . (int)$this->config->get('config_language_id') . "',
                        query = 'product_id=" . (int)$product_id . "',
                        keyword = '" . $this->db->escape($seo_url) . "'");
                }
            } else {
                // Atualizar na tabela product
                $this->db->query("UPDATE " . DB_PREFIX . "product SET " . $field . " = '" . $this->db->escape($value) . "' WHERE product_id = '" . (int)$product_id . "'");
                
                // Se o campo atualizado for 'model', também atualiza o campo 'sku'
                if ($field == 'model') {
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET sku = '" . $this->db->escape($value) . "' WHERE product_id = '" . (int)$product_id . "'");
                }
            }
        }
    }
    
    private function generateSeoUrl($name, $product_id) {
        // Criar SEO URL baseado no nome
        $seo_url = trim($name);
        $seo_url = html_entity_decode($seo_url, ENT_QUOTES, 'UTF-8');
        $seo_url = preg_replace('/[^\w\s-]/', '', $seo_url);
        $seo_url = preg_replace('/[\s_-]+/', '-', $seo_url);
        $seo_url = strtolower(trim($seo_url, '-'));
        
        // Verificar se SEO URL já existe (excluindo o produto atual)
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "seo_url 
            WHERE keyword = '" . $this->db->escape($seo_url) . "' 
            AND query != 'product_id=" . (int)$product_id . "'");
        
        if ($query->row['total'] > 0) {
            $seo_url .= '-' . $product_id;
        }
        
        return $seo_url;
    }
    
    private function getFormattedValue($field, $value) {
        switch ($field) {
            case 'price':
                $this->load->model('localisation/currency');
                return $this->currency->format($value, $this->config->get('config_currency'));
                
            case 'quantity':
                // Retornar apenas o valor numérico, sem as classes label
                return (int)$value;
                
            default:
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
    }
    
    private function logChange($product_id, $field, $old_value, $new_value) {
        if ($old_value != $new_value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_edit_log SET 
                product_id = '" . (int)$product_id . "',
                field = '" . $this->db->escape($field) . "',
                old_value = '" . $this->db->escape($old_value) . "',
                new_value = '" . $this->db->escape($new_value) . "',
                user_id = '" . (int)$this->user->getId() . "',
                date_added = NOW()");
        }
    }
}
?> 