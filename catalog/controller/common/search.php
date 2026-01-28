<?php
class ControllerCommonSearch extends Controller {
	public function index() {
		$this->load->language('common/search');

		$data['text_search'] = $this->language->get('text_search');

		if (isset($this->request->get['search'])) {
			$data['search'] = $this->request->get['search'];
		} else {
			$data['search'] = '';
		}

		// Adicionar telefone para link do WhatsApp
		$telephone = $this->config->get('config_telephone');
		// Remover caracteres não numéricos do telefone
		$data['whatsapp_number'] = preg_replace('/[^0-9]/', '', $telephone);

		return $this->load->view('common/search', $data);
	}
}