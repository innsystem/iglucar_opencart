<?php
class ControllerExtensionModuleVehicleSearch extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/vehicle_search');

		$data['heading_title'] = $setting['name'];
		$data['placeholder'] = $setting['placeholder'];
		$data['min_length'] = $setting['min_length'];
		$data['max_results'] = $setting['max_results'];
		$data['module_id'] = $this->request->get['module_id'] ?? rand();

		return $this->load->view('extension/module/vehicle_search', $data);
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/filter');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start' => 0,
				'limit' => $this->request->get['max_results'] ?? 10
			);

			$results = $this->model_catalog_filter->getFilterAutocomplete($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'name' => $result['name'],
					'value' => $result['name']
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
