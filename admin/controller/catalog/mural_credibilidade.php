<?php
class ControllerCatalogMuralCredibilidade extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('catalog/mural_credibilidade');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('catalog/mural_credibilidade');
        
        $this->getList();
    }

    public function add() {
        $this->load->language('catalog/mural_credibilidade');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('catalog/mural_credibilidade');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_catalog_mural_credibilidade->addMural($this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('catalog/mural_credibilidade', 'user_token=' . $this->session->data['user_token'], true));
        }

        $this->getForm();
    }

    public function edit() {
        $this->load->language('catalog/mural_credibilidade');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('catalog/mural_credibilidade');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_catalog_mural_credibilidade->editMural($this->request->get['mural_id'], $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('catalog/mural_credibilidade', 'user_token=' . $this->session->data['user_token'], true));
        }

        $this->getForm();
    }

    public function delete() {
        $this->load->language('catalog/mural_credibilidade');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('catalog/mural_credibilidade');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $mural_id) {
                $this->model_catalog_mural_credibilidade->deleteMural($mural_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('catalog/mural_credibilidade', 'user_token=' . $this->session->data['user_token'], true));
        }

        $this->getList();
    }

    protected function getList() {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'm.sort_order';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/mural_credibilidade', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        $data['add'] = $this->url->link('catalog/mural_credibilidade/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('catalog/mural_credibilidade/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['murals'] = array();

        $filter_data = array(
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $mural_total = $this->model_catalog_mural_credibilidade->getTotalMurals();

        $results = $this->model_catalog_mural_credibilidade->getMurals($filter_data);

        foreach ($results as $result) {
            $data['murals'][] = array(
                'mural_id' => $result['mural_id'],
                'title'    => $result['title'],
                'sort_order' => $result['sort_order'],
                'status'   => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                'edit'     => $this->url->link('catalog/mural_credibilidade/edit', 'user_token=' . $this->session->data['user_token'] . '&mural_id=' . $result['mural_id'] . $url, true)
            );
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_title'] = $this->url->link('catalog/mural_credibilidade', 'user_token=' . $this->session->data['user_token'] . '&sort=m.title' . $url, true);
        $data['sort_sort_order'] = $this->url->link('catalog/mural_credibilidade', 'user_token=' . $this->session->data['user_token'] . '&sort=m.sort_order' . $url, true);
        $data['sort_status'] = $this->url->link('catalog/mural_credibilidade', 'user_token=' . $this->session->data['user_token'] . '&sort=m.status' . $url, true);

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $mural_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('catalog/mural_credibilidade', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($mural_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($mural_total - $this->config->get('config_limit_admin'))) ? $mural_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $mural_total, ceil($mural_total / $this->config->get('config_limit_admin')));

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('catalog/mural_credibilidade_list', $data));
    }

    protected function getForm() {
        $data['text_form'] = !isset($this->request->get['mural_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['title'])) {
            $data['error_title'] = $this->error['title'];
        } else {
            $data['error_title'] = '';
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/mural_credibilidade', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        if (!isset($this->request->get['mural_id'])) {
            $data['action'] = $this->url->link('catalog/mural_credibilidade/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('catalog/mural_credibilidade/edit', 'user_token=' . $this->session->data['user_token'] . '&mural_id=' . $this->request->get['mural_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('catalog/mural_credibilidade', 'user_token=' . $this->session->data['user_token'] . $url, true);

        if (isset($this->request->get['mural_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $mural_info = $this->model_catalog_mural_credibilidade->getMural($this->request->get['mural_id']);
        }

        if (isset($this->request->post['title'])) {
            $data['title'] = $this->request->post['title'];
        } elseif (!empty($mural_info)) {
            $data['title'] = $mural_info['title'];
        } else {
            $data['title'] = '';
        }

        if (isset($this->request->post['sort_order'])) {
            $data['sort_order'] = $this->request->post['sort_order'];
        } elseif (!empty($mural_info)) {
            $data['sort_order'] = $mural_info['sort_order'];
        } else {
            $data['sort_order'] = 0;
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($mural_info)) {
            $data['status'] = $mural_info['status'];
        } else {
            $data['status'] = true;
        }

        // Mural Images
        if (isset($this->request->post['mural_image'])) {
            $mural_images = $this->request->post['mural_image'];
        } elseif (isset($this->request->get['mural_id'])) {
            $mural_images = $this->model_catalog_mural_credibilidade->getMuralImages($this->request->get['mural_id']);
        } else {
            $mural_images = array();
        }

        $data['mural_images'] = array();

        $this->load->model('tool/image');

        foreach ($mural_images as $mural_image) {
            if (is_file(DIR_IMAGE . $mural_image['image'])) {
                $image = $mural_image['image'];
                $thumb = $mural_image['image'];
            } else {
                $image = '';
                $thumb = 'no_image.png';
            }

            $data['mural_images'][] = array(
                'mural_image_id' => $mural_image['mural_image_id'],
                'image'          => $image,
                'thumb'          => $this->model_tool_image->resize($thumb, 100, 100),
                'sort_order'     => $mural_image['sort_order']
            );
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('catalog/mural_credibilidade_form', $data));
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'catalog/mural_credibilidade')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['title']) < 3) || (utf8_strlen($this->request->post['title']) > 255)) {
            $this->error['title'] = $this->language->get('error_title');
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'catalog/mural_credibilidade')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
