<?php
class ControllerExtensionModuleVehicleCoverSection extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/vehicle_cover_section');

		$data['text_vehicle_search'] = $this->language->get('text_vehicle_search');
		$data['text_brand_navigation'] = $this->language->get('text_brand_navigation');
		$data['text_vehicle_cover'] = $this->language->get('text_vehicle_cover');
		$data['text_benefits'] = $this->language->get('text_benefits');
		$data['text_search_placeholder'] = $this->language->get('text_search_placeholder');
		$data['text_benefit_waterproof'] = $this->language->get('text_benefit_waterproof');
		$data['text_benefit_no_contact'] = $this->language->get('text_benefit_no_contact');
		$data['text_benefit_no_debris'] = $this->language->get('text_benefit_no_debris');
		$data['text_benefit_no_labor'] = $this->language->get('text_benefit_no_labor');
		$data['text_benefit_no_iptu'] = $this->language->get('text_benefit_no_iptu');
		$data['text_benefit_air_vents'] = $this->language->get('text_benefit_air_vents');
		$data['text_benefit_hail_proof'] = $this->language->get('text_benefit_hail_proof');

		// Obter marcas (manufacturers) com contagem de modelos e produtos
		$data['manufacturers'] = $this->getBrands();
		$data['module_id'] = $this->request->get['module_id'] ?? rand();

		return $this->load->view('extension/module/vehicle_cover_section', $data);
	}

	/**
	 * Obtém marcas com contagem de modelos e produtos
	 */
	private function getBrands() {
		// Primeiro, obter todos os manufacturers (ordenando apenas por nome)
		$sql = "SELECT manufacturer_id, name, image FROM " . DB_PREFIX . "manufacturer ORDER BY name";
		$query = $this->db->query($sql);

		$brands = array();
		foreach ($query->rows as $manufacturer) {
			// Para cada manufacturer, contar quantos filtros (modelos) existem
			$count_sql = "SELECT COUNT(DISTINCT f.filter_id) as total_models,
						 COUNT(DISTINCT pf.product_id) as total_products
						 FROM " . DB_PREFIX . "filter f
						 LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id)
						 LEFT JOIN " . DB_PREFIX . "product_filter pf ON (f.filter_id = pf.filter_id)
						 WHERE fd.language_id = '" . (int)$this->config->get('config_language_id') . "'
						 AND SUBSTRING_INDEX(fd.name, ' ', 1) = '" . $this->db->escape($manufacturer['name']) . "'";

			$count_query = $this->db->query($count_sql);
			$count_row = $count_query->row;

			// Só incluir se tiver modelos
			if ($count_row['total_models'] > 0) {
				$image_path = '';
				if (!empty($manufacturer['image'])) {
					$image_path = 'image/' . $manufacturer['image'];
				} else {
					// Fallback para imagem padrão
					$image_name = strtolower(str_replace(' ', '_', $manufacturer['name'])) . '.png';
					$image_path = 'image/catalog/brands/' . $image_name;
				}

				$brands[] = array(
					'manufacturer_id' => $manufacturer['manufacturer_id'],
					'name' => $manufacturer['name'],
					'total_models' => $count_row['total_models'],
					'total_products' => $count_row['total_products'],
					'image' => $image_path,
					'url' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id'], true)
				);
			}
		}

		return $brands;
	}

	/**
	 * AJAX: Obter modelos de uma marca específica
	 */
	public function getModels() {
		$json = array();

		if (isset($this->request->get['manufacturer_id'])) {
			$this->load->model('catalog/filter');

			$filter_data = array(
				'filter_manufacturer_id' => (int)$this->request->get['manufacturer_id'],
				'start' => 0,
				'limit' => 200
			);

			$results = $this->model_catalog_filter->getModelsByManufacturer($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'name' => $result['name'],
					'products_count' => $result['products_count']
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
