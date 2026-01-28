<?php
class ControllerExtensionModuleProductVariation extends Controller {
    private $error = array();


    public function install()
    {                
            $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."product_variated` (
                `product_id` int(20) NOT NULL,
                `variated_id` int(20) NOT NULL
              ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");        
    }

    public function index() {
        $this->load->language('extension/module/product_variation');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');                    

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
     

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/product_variation', $data));
    }


}