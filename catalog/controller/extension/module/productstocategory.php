<?php
class ControllerExtensionModuleProductstocategory extends Controller
{
	public function index($setting)
	{
		$this->load->language('extension/module/productstocategory');

		$this->load->model('catalog/product');
		$this->load->model('catalog/category');

		$this->load->model('tool/image');

		$data['products'] = array();
		
		// Passar o tipo de visualização para o template
		$data['type_view'] = !empty($setting['type_view']) ? $setting['type_view'] : 'grid';
		
		// Gerar um ID único para o módulo
		$data['module_id'] = rand();

		// Obter produtos por categoria em vez dos mais recentes
		if (!empty($setting['category_id'])) {
			$filter_data = array(
				'filter_category_id' => $setting['category_id'],
				'sort'               => 'p.sort_order',
				'order'              => 'ASC',
				'start'              => 0,
				'limit'              => $setting['limit']
			);

			$results = $this->model_catalog_product->getProducts($filter_data);

			if ($results) {
				foreach ($results as $result) {
					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if (!is_null($result['special']) && (float)$result['special'] >= 0) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						$tax_price = (float)$result['special'];
					} else {
						$special = false;
						$tax_price = (float)$result['price'];
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format($tax_price, $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $result['rating'];
					} else {
						$rating = false;
					}

					// Obtém todas as unidades cadastradas no sistema
					$length_unit = $this->model_catalog_product->getLenghtProduct($result['length_class_id']);

					if ((float) $result['special']) {
						$parcelamento = $this->model_catalog_product->getParcelamento($result['special'], $result['tax_class_id']);
					} else {
						$parcelamento = $this->model_catalog_product->getParcelamento($result['price'], $result['tax_class_id']);
					}
					$data['products'][] = array(
						'parcelamento' => $parcelamento,
						'product_id'  => $result['product_id'],
						'thumb'       => $image,
						'name'        => $result['name'],
						'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id']),
						'quantity'        => $result['quantity'],
						'length'        => number_format($result['length'], 2),
						'height'        => number_format($result['height'], 2),
						'width'        => number_format($result['width'], 2),
						'length_unit'        => $length_unit,
					);
				}

				$category_info = $this->model_catalog_category->getCategory($setting['category_id']);

				if ($category_info) {
					$data['heading_title'] = $category_info['name'];
				} else {
					$data['heading_title'] = $this->language->get('heading_title');
				}

				return $this->load->view('extension/module/productstocategory', $data);
			}
		}
	}
} 