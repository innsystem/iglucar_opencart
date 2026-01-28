<?php
class ControllerExtensionModuleProductOptionImage extends Controller {
    private $error = array();


    public function install()
    {
        $query = $this->db->query("SHOW COLUMNS FROM `".DB_PREFIX."product_option_value` LIKE 'product_option_image' ");
        if(!$query->num_rows){
            $this->db->query("ALTER TABLE `".DB_PREFIX."product_option_value` ADD `product_option_image` VARCHAR(255) ");
        }
    }

    public function index() {
        $this->load->language('extension/module/product_option_image');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_sagepay_direct_cards', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/sagepay_direct_cards', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/sagepay_direct_cards', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_sagepay_direct_cards_status'])) {
            $data['module_sagepay_direct_cards_status'] = $this->request->post['module_sagepay_direct_cards_status'];
        } else {
            $data['module_sagepay_direct_cards_status'] = $this->config->get('module_sagepay_direct_cards_status');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/product_option_image', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/product_option_image')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}