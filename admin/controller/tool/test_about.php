<?php
class ControllerToolTestAbout extends Controller {
    public function index() {
        $this->load->language('tool/test_about');
        
        $this->document->setTitle('Teste da Página Sobre Nós');
        
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => 'Início',
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Teste da Página Sobre Nós',
            'href' => $this->url->link('tool/test_about', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        // Testar se as tabelas existem
        $data['tables_status'] = $this->checkTables();
        
        // Testar se o controller funciona
        $data['controller_status'] = $this->checkController();
        
        // Testar se o model funciona
        $data['model_status'] = $this->checkModel();
        
        $this->response->setOutput($this->load->view('tool/test_about', $data));
    }
    
    private function checkTables() {
        $tables = array(
            'about',
            'about_description', 
            'about_testimonial',
            'about_to_store',
            'about_to_layout'
        );
        
        $status = array();
        
        foreach ($tables as $table) {
            $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
            $status[$table] = $query->num_rows > 0 ? 'OK' : 'ERRO';
        }
        
        return $status;
    }
    
    private function checkController() {
        try {
            if (class_exists('ControllerCatalogAbout')) {
                return 'OK - Controller Admin existe';
            } else {
                return 'ERRO - Controller Admin não encontrado';
            }
        } catch (Exception $e) {
            return 'ERRO - ' . $e->getMessage();
        }
    }
    
    private function checkModel() {
        try {
            if (class_exists('ModelCatalogAbout')) {
                return 'OK - Model Admin existe';
            } else {
                return 'ERRO - Model Admin não encontrado';
            }
        } catch (Exception $e) {
            return 'ERRO - ' . $e->getMessage();
        }
    }
}
