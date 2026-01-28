<?php
class ControllerExtensionModuleBlockInfos extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/block_infos');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_block_infos', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			// Redirect back to the module's own page after saving
			$this->response->redirect($this->url->link('extension/module/block_infos', 'user_token=' . $this->session->data['user_token'], true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');		
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_add_block'] = $this->language->get('text_add_block');
		$data['text_block'] = $this->language->get('text_block');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_text'] = $this->language->get('entry_text');
		$data['entry_image'] = $this->language->get('entry_image');
		$data['entry_border_color'] = $this->language->get('entry_border_color');
		$data['entry_status'] = $this->language->get('entry_status');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_remove'] = $this->language->get('button_remove');


		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		// Error messages for fields (basic validation as requested)
		$data['error_name'] = isset($this->error['name']) ? $this->error['name'] : '';
		$data['error_blocks'] = isset($this->error['blocks']) ? $this->error['blocks'] : array();

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
			'href' => $this->url->link('extension/module/block_infos', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/block_infos', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		// Load module settings
		if (isset($this->request->post['module_block_infos_status'])) {
			$data['module_block_infos_status'] = $this->request->post['module_block_infos_status'];
		} else {
			$data['module_block_infos_status'] = $this->config->get('module_block_infos_status');
		}

		if (isset($this->request->post['module_block_infos_name'])) {
			$data['module_block_infos_name'] = $this->request->post['module_block_infos_name'];
		} else {
			$data['module_block_infos_name'] = $this->config->get('module_block_infos_name');
		}

		// Load existing blocks or initialize an empty array
		if (isset($this->request->post['module_block_infos_block'])) {
			$data['module_block_infos_block'] = $this->request->post['module_block_infos_block'];
		} elseif ($this->config->get('module_block_infos_block')) {
			$data['module_block_infos_block'] = $this->config->get('module_block_infos_block');
		} else {
			$data['module_block_infos_block'] = array();
		}

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		foreach ($data['module_block_infos_block'] as &$block) {
			$block['thumb'] = $block['image'] ? $this->model_tool_image->resize($block['image'], 100, 100) : $data['placeholder'];
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/block_infos', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/block_infos')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['module_block_infos_name']) < 3) || (utf8_strlen($this->request->post['module_block_infos_name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

		if (isset($this->request->post['module_block_infos_block'])) {
			foreach ($this->request->post['module_block_infos_block'] as $key => $block) {
				// Basic validation: title and text are required
				if ((utf8_strlen($block['title']) < 1) || (utf8_strlen($block['title']) > 255)) {
					$this->error['blocks'][$key]['title'] = $this->language->get('error_title');
				}

				if (utf8_strlen($block['text']) < 1) {
					$this->error['blocks'][$key]['text'] = $this->language->get('error_text');
				}

				// No validation for image and border_color as requested, but check if border_color is set
				if (empty($block['border_color'])) {
					// Optionally add a default color or error, but for now just ensure it exists
					// $this->error['blocks'][$key]['border_color'] = $this->language->get('error_border_color');
				}

			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_form');
		}

		return !$this->error;
	}

	public function install() {
		$this->load->model('setting/event');

		$this->model_setting_event->addEvent('block_infos_install', 'catalog/controller/startup/router/before', 'extension/module/block_infos/install');
	}

	public function uninstall() {
		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('block_infos_install');
	}
}?> 