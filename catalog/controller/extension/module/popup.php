<?php
class ControllerExtensionModulePopup extends Controller {
	public function index($setting = null) {
		// Se não foi passado setting, buscar popup global
		if ($setting === null) {
			return $this->global();
		}
		
		static $popup_shown = false;
		
		// Evitar múltiplas exibições do mesmo popup na mesma página
		if ($popup_shown && isset($setting['global_display']) && $setting['global_display']) {
			return '';
		}

		$status = isset($setting['status']) ? $setting['status'] : '';
		
		if (!$status) {
			return '';
		}

		$data = array();

		$data['popup_id'] = 'popup-module-' . (isset($setting['module_id']) ? $setting['module_id'] : '0');
		$data['title'] = isset($setting['title']) ? $setting['title'] : '';
		$data['image'] = isset($setting['image']) ? $setting['image'] : '';
		$data['link'] = isset($setting['link']) ? $setting['link'] : '';
		$data['width'] = isset($setting['width']) ? (int)$setting['width'] : 500;
		$data['height'] = isset($setting['height']) ? (int)$setting['height'] : 400;
		$data['delay'] = isset($setting['delay']) ? (int)$setting['delay'] : 3000;
		$data['repeat_time'] = isset($setting['repeat_time']) ? $setting['repeat_time'] : 'disabled';

		if ($data['image']) {
			$this->load->model('tool/image');
			$data['image_url'] = $this->model_tool_image->resize($data['image'], $data['width'], $data['height']);
		} else {
			return '';
		}

		// Se for exibição global, marcar como mostrado
		if (isset($setting['global_display']) && $setting['global_display']) {
			$popup_shown = true;
		}

		return $this->load->view('extension/module/popup', $data);
	}

	// Método para exibição global no header
	public function global() {
		try {
			// Verificar se a tabela de módulos existe
			if (!$this->db) {
				error_log('Popup: Database not available');
				return '';
			}
			
			// Buscar módulos popup ativos diretamente do banco de dados
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `code` = 'popup' ORDER BY `name`");
			
			if (!$query || !isset($query->rows) || !is_array($query->rows)) {
				error_log('Popup: No query results or invalid rows');
				return '';
			}
			
			error_log('Popup: Found ' . count($query->rows) . ' popup modules');
			
			foreach ($query->rows as $module) {
				if (!isset($module['setting']) || !isset($module['module_id'])) {
					continue;
				}
				
				$module_data = json_decode($module['setting'], true);
				
				if (!$module_data || !is_array($module_data)) {
					continue;
				}
				
				$module_data['module_id'] = $module['module_id'];
				
				error_log('Popup: Checking module ' . $module['module_id'] . ' - global_display: ' . (isset($module_data['global_display']) ? $module_data['global_display'] : 'not set') . ', status: ' . (isset($module_data['status']) ? $module_data['status'] : 'not set'));
				
				// Verificar se é popup global ativo
				if (isset($module_data['global_display']) && $module_data['global_display'] && isset($module_data['status']) && $module_data['status']) {
					// Verificar se tem imagem e título
					if (isset($module_data['image']) && $module_data['image'] && isset($module_data['title']) && $module_data['title']) {
						error_log('Popup: Rendering popup for module ' . $module['module_id']);
						return $this->renderPopup($module_data);
					} else {
						error_log('Popup: Module ' . $module['module_id'] . ' missing image or title');
					}
				}
			}
		} catch (Exception $e) {
			error_log('Popup Global Error: ' . $e->getMessage());
		}
		
		return '';
	}
	
	// Método para renderizar o popup
	private function renderPopup($setting) {
		$data = array();

		$data['popup_id'] = 'popup-module-' . (isset($setting['module_id']) ? $setting['module_id'] : '0');
		$data['title'] = isset($setting['title']) ? $setting['title'] : '';
		$data['image'] = isset($setting['image']) ? $setting['image'] : '';
		$data['link'] = isset($setting['link']) ? $setting['link'] : '';
		$data['width'] = isset($setting['width']) ? (int)$setting['width'] : 500;
		$data['height'] = isset($setting['height']) ? (int)$setting['height'] : 400;
		$data['delay'] = isset($setting['delay']) ? (int)$setting['delay'] : 3000;
		$data['repeat_time'] = isset($setting['repeat_time']) ? $setting['repeat_time'] : 'disabled';

		if ($data['image']) {
			$this->load->model('tool/image');
			$data['image_url'] = $this->model_tool_image->resize($data['image'], $data['width'], $data['height']);
		} else {
			error_log('Popup: No image found for popup');
			return '';
		}

		error_log('Popup: Rendering popup with data: ' . json_encode($data));
		
		try {
			$output = $this->load->view('extension/module/popup', $data);
			error_log('Popup: Template rendered successfully, length: ' . strlen($output));
			return $output;
		} catch (Exception $e) {
			error_log('Popup: Template error: ' . $e->getMessage());
			return '';
		}
	}
}
