<?php
class ControllerExtensionModuleBlockInfos extends Controller {
	public function index() {
		$this->load->language('extension/module/block_infos');

		$this->load->model('tool/image');
		$this->load->model('setting/setting');

		$data['blocks'] = array();

		// Get module settings
		$module_info = $this->model_setting_setting->getSetting('module_block_infos');

		if ($module_info && $module_info['module_block_infos_status']) {
			$blocks = $module_info['module_block_infos_block'];

			if (!empty($blocks)) {
				foreach ($blocks as $block) {
					// Ensure required fields exist, although basic validation is in admin
					if (!empty($block['title']) && !empty($block['text'])) {
						$data['blocks'][] = array(
							'title'			 => html_entity_decode($block['title'], ENT_QUOTES, 'UTF-8'),
							'text'				 => html_entity_decode($block['text'], ENT_QUOTES, 'UTF-8'),
							'image'			 => $block['image'] ? $this->model_tool_image->resize($block['image'], 100, 100) : '', // Adjust size as needed
							'border_color' => !empty($block['border_color']) ? $block['border_color'] : '#000000' // Default color if not set
						);
					}
				}
			}
		}

		return $this->load->view('extension/module/block_infos', $data);
	}

	// Methods for install/uninstall if needed for frontend, but not strictly necessary for this module type
	// public function install() {
	//
	// }

	// public function uninstall() {
	//
	// }
}?> 