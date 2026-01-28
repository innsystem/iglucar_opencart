<?php
class ControllerExtensionModuleParcelamento extends Controller {
    private $error = array();

    public function index() {
        $data = $this->load->language('extension/module/parcelamento');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_parcelamento', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            if (isset($this->request->post['save_stay']) && ($this->request->post['save_stay'] == '1')) {
                $this->response->redirect($this->url->link('extension/module/parcelamento', 'user_token=' . $this->session->data['user_token'], true));
            } else {
                $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
            }
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['user_token'] = $this->session->data['user_token'];

        $erros = array(
            'warning',
            'total',
            'desconto',
            'parcelas',
            'juros',
            'calculo_juros',
            'sem_juros',
            'minimo'
        );

        foreach ($erros as $erro) {
            if (isset($this->error[$erro])) {
                $data['error_'.$erro] = $this->error[$erro];
            } else {
                $data['error_'.$erro] = '';
            }
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
            'href' => $this->url->link('extension/module/parcelamento', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/parcelamento', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $data['versao'] = '2.4.1';

        $campos = array(
            'language_id' => '',
            'currency_id' => '',
            'stores' => array(0),
            'customer_groups' => array(0),
            'status' => '',
            'total' => '',
            'desconto' => '',
            'parcelas' => '',
            'calculo_juros' => '',
            'juros' => '',
            'sem_juros' => '',
            'minimo' => '',
            'imagem' => '',
            'layout_modulos' => '',
            'layout_produto' => '',
            'layout_parcelas' => '',
            'texto_sem_juros' => 'sem juros',
            'texto_com_juros' => 'com juros',
            'tabela' => array(0),
            'options_container' => '#product',
            'view_container' => '#parcelamento'
        );

        foreach ($campos as $campo => $valor) {
            if (!empty($valor)) {
                if (isset($this->request->post['module_parcelamento_'.$campo])) {
                    $data['module_parcelamento_'.$campo] = $this->request->post['module_parcelamento_'.$campo];
                } else if ($this->config->get('module_parcelamento_'.$campo)) {
                    $data['module_parcelamento_'.$campo] = $this->config->get('module_parcelamento_'.$campo);
                } else {
                    $data['module_parcelamento_'.$campo] = $valor;
                }
            } else {
                if (isset($this->request->post['module_parcelamento_'.$campo])) {
                    $data['module_parcelamento_'.$campo] = $this->request->post['module_parcelamento_'.$campo];
                } else {
                    $data['module_parcelamento_'.$campo] = $this->config->get('module_parcelamento_'.$campo);
                }
            }
        }

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('localisation/currency');
        $data['currencies'] = $this->model_localisation_currency->getCurrencies();

        $data['store_default'] = $this->config->get('config_name');
        $this->load->model('setting/store');
        $data['stores'] = $this->model_setting_store->getStores();

        $this->load->model('customer/customer_group');
        $data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

        $this->load->model('tool/image');
        if (isset($this->request->post['module_parcelamento_imagem']) && is_file(DIR_IMAGE . $this->request->post['module_parcelamento_imagem'])) {
            $data['thumb'] = $this->model_tool_image->resize($this->request->post['module_parcelamento_imagem'], 100, 100);
        } elseif (is_file(DIR_IMAGE . $this->config->get('module_parcelamento_imagem'))) {
            $data['thumb'] = $this->model_tool_image->resize($this->config->get('module_parcelamento_imagem'), 100, 100);
        } else {
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }
        $data['no_image'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/parcelamento', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/parcelamento')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $campos = array(
            'total',
            'desconto',
            'parcelas',
            'sem_juros',
            'minimo'
        );

        foreach ($campos as $campo) {
            if (!(is_numeric($this->request->post['module_parcelamento_'.$campo]))) {
                $this->error[$campo] = $this->language->get('error_'.$campo);
            }
        }

        if ($this->request->post['module_parcelamento_calculo_juros'] != 'tabela') {
            if (!(is_numeric($this->request->post['module_parcelamento_juros']))) {
                $this->error['juros'] = $this->language->get('error_juros');
            }
        }

        if (($this->request->post['module_parcelamento_calculo_juros'] == 'tabela') && (count($this->request->post['module_parcelamento_tabela']) != $this->request->post['module_parcelamento_parcelas'])) {
            $this->error['calculo_juros'] = $this->language->get('error_calculo_juros');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }
}