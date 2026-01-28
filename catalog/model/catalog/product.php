<?php
class ModelCatalogProduct extends Model
{



	private function getParcelar($total, $parcela)
	{
		$parcelas = $this->config->get('module_parcelamento_parcelas');
		$calculo_juros = $this->config->get('module_parcelamento_calculo_juros');
		$juros_mes = ($this->config->get('module_parcelamento_juros')) ? $this->config->get('module_parcelamento_juros') : 0;
		$fator = $juros_mes * 0.01;
		$valorParcela = 0;

		if ($parcela > $parcelas) {
			$parcela = $parcelas;
		}

		if ($calculo_juros == 'simples') {
			$valorParcela = ($total * pow((1 + $fator), $parcela) * $fator) / (pow((1 + $fator), $parcela) - 1);
		} else if ($calculo_juros == 'composto') {
			$valorParcela = ($total * pow((1 + $fator), $parcela)) / $parcela;
		} else if ($calculo_juros == 'tabela') {
			$juros = 0;
			$tabela = $this->config->get('module_parcelamento_tabela');
			if (is_array($tabela)) {
				if (count($tabela) > 0) {
					for ($i = 0; $i < count($tabela); $i++) {
						if ($tabela[$i]['parcelas'] == $parcela) {
							$juros = $tabela[$i]['juros'];
							break;
						}
					}
					$fator = ($juros * 0.01) + 1;
					$montante = $total * $fator;
					$valorParcela = ($montante / $parcela);
				}
			}
		}

		$valorParcela = round($valorParcela, 2);
		$valorTotal = $parcela * $valorParcela;

		$resultado = array(
			'valorParcela' => $valorParcela,
			'valorTotal' => $valorTotal
		);

		return $resultado;
	}

	private function getDemonstrativo($total)
	{
		$parcelamento = array();

		$currency_code = $this->session->data['currency'];
		$parcelas = $this->config->get('module_parcelamento_parcelas');
		$sem_juros = $this->config->get('module_parcelamento_sem_juros');
		$minimo = ($this->config->get('module_parcelamento_minimo') > 0) ? $this->config->get('module_parcelamento_minimo') : '0';

		for ($i = 1; $i <= $parcelas; $i++) {
			if ($i == 1) {
				$parcelamento[] = array(
					'parcela' => $i,
					'valor' => $this->currency->format($total, $currency_code),
					'juros' => false,
					'total' => $this->currency->format($total, $currency_code)
				);
			} else {
				if ($i <= $sem_juros) {
					$valorParcela = ($total / $i);
					if ($valorParcela >= $minimo) {
						$parcelamento[] = array(
							'parcela' => $i,
							'valor' => $this->currency->format($valorParcela, $currency_code),
							'juros' => false,
							'total' => $this->currency->format($total, $currency_code)
						);
					}
				} else {
					$resultado = $this->getParcelar($total, $i);
					if ($resultado['valorParcela'] >= $minimo) {
						$parcelamento[] = array(
							'parcela' => $i,
							'valor' => $this->currency->format($resultado['valorParcela'], $currency_code),
							'juros' => true,
							'total' => $this->currency->format($resultado['valorTotal'], $currency_code)
						);
					}
				}
			}
		}

		return $parcelamento;
	}

	public function getParcelamento($preco, $tax_class_id, $detalhes = false)
	{
		$parcelamento = '';

		if ($this->config->get('module_parcelamento_status')) {
			$valor_minimo = $this->config->get('module_parcelamento_total');
			if ($preco > 0 && $preco >= $valor_minimo) {
				if (in_array($this->config->get('config_store_id'), $this->config->get('module_parcelamento_stores'))) {
					if ($this->session->data['language'] == $this->config->get('module_parcelamento_language_id')) {
						$currency_code = $this->session->data['currency'];
						if ($currency_code == $this->config->get('module_parcelamento_currency_id')) {
							if ($this->customer->isLogged()) {
								$customerGroupId = $this->customer->getGroupId();
							} else {
								$customerGroupId = $this->config->get('config_customer_group_id');
							}
							if (in_array($customerGroupId, $this->config->get('module_parcelamento_customer_groups'))) {
								$desconto = ($this->config->get('module_parcelamento_desconto')) ? $this->config->get('module_parcelamento_desconto') : 0;
								$total_parcelas = $this->config->get('module_parcelamento_parcelas');
								$juros_mes = ($this->config->get('module_parcelamento_juros')) ? $this->config->get('module_parcelamento_juros') : 0;
								$sem_juros = $this->config->get('module_parcelamento_sem_juros');
								$minimo = $this->config->get('module_parcelamento_minimo');
								$imagem = $this->config->get('module_parcelamento_imagem');
								$layout_modulos = $this->config->get('module_parcelamento_layout_modulos');
								$layout_produto = $this->config->get('module_parcelamento_layout_produto');
								$layout_parcelas = $this->config->get('module_parcelamento_layout_parcelas');

								$parcela_minima = ($minimo > 0) ? $minimo : 0;
								$sem_juros = ($sem_juros > 0) ? $sem_juros : 1;

								$parcelas_sem_juros = 1;
								for ($p = 1; $p <= $sem_juros; $p++) {
									if (($preco / $p) >= $parcela_minima) {
										$parcelas_sem_juros = $p;
									}
								}

								$sjuros = $preco / $parcelas_sem_juros;
								$sjuros = round($sjuros, 2);
								$residuo = $preco - ($sjuros * $parcelas_sem_juros);
								$sjuros = ($residuo > 0) ? $sjuros + 0.01 : $sjuros;
								$valor_parcela_sem_juros = $sjuros;

								$sjuros = $this->currency->format($this->tax->calculate($sjuros, $tax_class_id, $this->config->get('config_tax')), $currency_code);

								$total_parcelas = ($total_parcelas > 0) ? $total_parcelas : 1;

								$parcelas = 1;
								for ($p = 1; $p <= $total_parcelas; $p++) {
									$resultado = $this->getParcelar($preco, $p);
									if ($resultado['valorParcela'] >= $parcela_minima) {
										$parcelas = $p;
									}
								}

								$resultado = $this->getParcelar($preco, $parcelas);
								$cjuros = $resultado['valorParcela'];
								$valor_parcela_com_juros = $cjuros;
								$cjuros = $this->currency->format($this->tax->calculate($cjuros, $tax_class_id, $this->config->get('config_tax')), $currency_code);

								if ($desconto > 0) {
									$vdesconto = $preco - (($preco * $desconto) / 100);
									$veconomia = $preco - $vdesconto;
									$vdesconto = $this->currency->format($this->tax->calculate($vdesconto, $tax_class_id, $this->config->get('config_tax')), $currency_code);
									$veconomia = $this->currency->format($this->tax->calculate($veconomia, $tax_class_id, $this->config->get('config_tax')), $currency_code);
								} else {
									$vdesconto = $preco;
									$veconomia = 0;
									$vdesconto = $this->currency->format($this->tax->calculate($vdesconto, $tax_class_id, $this->config->get('config_tax')), $currency_code);
									$veconomia = $this->currency->format($veconomia, $currency_code);
								}

								$preco_formatado = $this->currency->format($this->tax->calculate($preco, $tax_class_id, $this->config->get('config_tax')), $currency_code);

								if ($detalhes) {
									if (isset($imagem)) {
										$imagem = '<img src="' . HTTPS_SERVER . 'image/' . $imagem . '" />';
									} else {
										$imagem = '';
									}

									$resultado = $this->getDemonstrativo($preco);

									$demonstrativo = '';

									if (is_array($resultado)) {
										$params_input = array('{parcelas}', '{parcela}', '{total}', '{juros}');

										for ($i = 0; $i <= count($resultado) - 1; $i++) {
											$qtd_parcela = str_pad($resultado[$i]['parcela'], 2, "0", STR_PAD_LEFT);
											if ($resultado[$i]['parcela'] == '1' || $resultado[$i]['juros'] == false) {
												$params_output = array($qtd_parcela, $resultado[$i]['valor'], $resultado[$i]['total'], $this->config->get('module_parcelamento_texto_sem_juros'));
												$demonstrativo .= str_replace($params_input, $params_output, $layout_parcelas);
											} else {
												$params_output = array($qtd_parcela, $resultado[$i]['valor'], $resultado[$i]['total'], $this->config->get('module_parcelamento_texto_com_juros'));
												$demonstrativo .= str_replace($params_input, $params_output, $layout_parcelas);
											}
										}
									}

									$params_input = array('{preco}', '{desconto}', '{parcelas}', '{sem_juros}', '{juros_mes}', '{sjuros}', '{cjuros}', '{vdesconto}', '{veconomia}', '{imagem}', '{demonstrativo}');
									$params_output = array($preco_formatado, $desconto, $parcelas, $parcelas_sem_juros, $juros_mes, $sjuros, $cjuros, $vdesconto, $veconomia, $imagem, $demonstrativo);

									$output = str_replace($params_input, $params_output, $layout_produto);
								} else {
									$params_input = array('{preco}', '{desconto}', '{parcelas}', '{sem_juros}', '{juros_mes}', '{sjuros}', '{cjuros}', '{vdesconto}', '{veconomia}');
									$params_output = array($preco_formatado, $desconto, $parcelas, $parcelas_sem_juros, $juros_mes, $sjuros, $cjuros, $vdesconto, $veconomia);

									$output = str_replace($params_input, $params_output, $layout_modulos);
								}

								if ($parcelas_sem_juros > 0 && $valor_parcela_sem_juros < $valor_minimo) {
									$output = preg_replace("/(({sjminimo(.*?)?})(.*?)(\/sjminimo}))/sim", "", $output);
								} else {
									$output = str_replace(array('{sjminimo}', '{/sjminimo}'), array('', ''), $output);
								}

								if ($juros_mes > 0 && $parcelas <= $sem_juros) {
									$output = preg_replace("/(({cjminimo(.*?)?})(.*?)(\/cjminimo}))/sim", "", $output);
								} else {
									if ($valor_parcela_com_juros < $valor_minimo) {
										$output = preg_replace("/(({cjminimo(.*?)?})(.*?)(\/cjminimo}))/sim", "", $output);
									} else {
										$output = str_replace(array('{cjminimo}', '{/cjminimo}'), array('', ''), $output);
									}
								}

								/* Remove as quebras de linha do layout para evitar problemas */
								$output = trim(preg_replace('/\s/', ' ', $output));

								/* Prepara o layout para ser exibido */
								$parcelamento = html_entity_decode($output, ENT_QUOTES, 'UTF-8');
							}
						}
					}
				}
			}
		}

		return $parcelamento;
	}


	public function updateViewed($product_id)
	{
		$this->db->query("UPDATE " . DB_PREFIX . "product SET viewed = (viewed + 1) WHERE product_id = '" . (int)$product_id . "'");
	}

	public function getProduct($product_id)
	{
		$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return array(
				'product_id'       => $query->row['product_id'],
				'name'             => $query->row['name'],
				'description'      => $query->row['description'],
				'meta_title'       => $query->row['meta_title'],
				'meta_description' => $query->row['meta_description'],
				'meta_keyword'     => $query->row['meta_keyword'],
				'tag'              => $query->row['tag'],
				'model'            => $query->row['model'],
				'sku'              => $query->row['sku'],
				'upc'              => $query->row['upc'],
				'ean'              => $query->row['ean'],
				'jan'              => $query->row['jan'],
				'isbn'             => $query->row['isbn'],
				'mpn'              => $query->row['mpn'],
				'location'         => $query->row['location'],
				'quantity'         => $query->row['quantity'],
				'stock_status'     => $query->row['stock_status'],
				'image'            => $query->row['image'],
				'manufacturer_id'  => $query->row['manufacturer_id'],
				'manufacturer'     => $query->row['manufacturer'],
				'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
				'special'          => $query->row['special'],
				'reward'           => $query->row['reward'],
				'points'           => $query->row['points'],
				'tax_class_id'     => $query->row['tax_class_id'],
				'date_available'   => $query->row['date_available'],
				'weight'           => $query->row['weight'],
				'weight_class_id'  => $query->row['weight_class_id'],
				'length'           => $query->row['length'],
				'width'            => $query->row['width'],
				'height'           => $query->row['height'],
				'length_class_id'  => $query->row['length_class_id'],
				'subtract'         => $query->row['subtract'],
				'rating'           => round(($query->row['rating'] === null) ? 0 : $query->row['rating']),
				'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0,
				'minimum'          => $query->row['minimum'],
				'sort_order'       => $query->row['sort_order'],
				'status'           => $query->row['status'],
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified'],
				'viewed'           => $query->row['viewed']
			);
		} else {
			return false;
		}
	}

	public function getProducts($data = array())
	{
		$sql = "SELECT p.product_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}

			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}

			if (!empty($data['filter_filter'])) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}

				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
			}
		}

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

				foreach ($words as $word) {
					$implode[] = "pd.tag LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			$sql .= ")";
		}

		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}

		$sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.quantity',
			'p.price',
			'rating',
			'p.sort_order',
			'p.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} elseif ($data['sort'] == 'p.price') {
				$sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY p.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$product_data = array();

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}

		return $product_data;
	}

	public function getProductSpecials($data = array())
	{
		$sql = "SELECT DISTINCT ps.product_id, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = ps.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) GROUP BY ps.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'ps.price',
			'rating',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY p.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$product_data = array();

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}

		return $product_data;
	}

	public function getLatestProducts($limit)
	{
		$product_data = $this->cache->get('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit);

		if (!$product_data) {
			$product_data = array();
			$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.date_added DESC LIMIT " . (int)$limit);

			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}

			$this->cache->set('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit, $product_data);
		}

		return $product_data;
	}

	public function getPopularProducts($limit)
	{
		$product_data = $this->cache->get('product.popular.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit);

		if (!$product_data) {
			$product_data = array();
			$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.viewed DESC, p.date_added DESC LIMIT " . (int)$limit);

			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}

			$this->cache->set('product.popular.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit, $product_data);
		}

		return $product_data;
	}

	public function getBestSellerProducts($limit)
	{
		$product_data = $this->cache->get('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit);

		if (!$product_data) {
			$product_data = array();

			$query = $this->db->query("SELECT op.product_id, SUM(op.quantity) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$limit);

			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}

			$this->cache->set('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit, $product_data);
		}

		return $product_data;
	}

	public function getProductAttributes($product_id)
	{
		$product_attribute_group_data = array();

		$product_attribute_group_query = $this->db->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");

		foreach ($product_attribute_group_query->rows as $product_attribute_group) {
			$product_attribute_data = array();

			$product_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY a.sort_order, ad.name");

			foreach ($product_attribute_query->rows as $product_attribute) {
				$product_attribute_data[] = array(
					'attribute_id' => $product_attribute['attribute_id'],
					'name'         => $product_attribute['name'],
					'text'         => $product_attribute['text']
				);
			}

			$product_attribute_group_data[] = array(
				'attribute_group_id' => $product_attribute_group['attribute_group_id'],
				'name'               => $product_attribute_group['name'],
				'attribute'          => $product_attribute_data
			);
		}

		return $product_attribute_group_data;
	}

	public function getProductOptions($product_id)
	{
		$product_option_data = array();

		$product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");

		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = array();

			$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");

			foreach ($product_option_value_query->rows as $product_option_value) {
				$product_option_value_data[] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
					'name'                    => $product_option_value['name'],
					'image'                   => $product_option_value['image'],
					'product_option_image'    => $product_option_value['product_option_image'],
					'quantity'                => $product_option_value['quantity'],
					'subtract'                => $product_option_value['subtract'],
					'price'                   => $product_option_value['price'],
					'price_prefix'            => $product_option_value['price_prefix'],
					'weight'                  => $product_option_value['weight'],
					'weight_prefix'           => $product_option_value['weight_prefix']
				);
			}

			$product_option_data[] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => $product_option['value'],
				'required'             => $product_option['required']
			);
		}

		return $product_option_data;
	}

	public function getProductDiscounts($product_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND quantity > 1 AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity ASC, priority ASC, price ASC");

		return $query->rows;
	}

	public function getProductImages($product_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getProductRelated($product_id)
	{
		$product_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related pr LEFT JOIN " . DB_PREFIX . "product p ON (pr.related_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pr.product_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		foreach ($query->rows as $result) {
			$product_data[$result['related_id']] = $this->getProduct($result['related_id']);
		}

		return $product_data;
	}

	//product variated
	public function getProductVariated($product_id)
	{
		$product_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_variated` pv LEFT JOIN `" . DB_PREFIX . "product` p ON (pv.variated_id = p.product_id) LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE pv.product_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		foreach ($query->rows as $result) {
			$product_data[$result['variated_id']] = $this->getProduct($result['variated_id']);
		}

		return $product_data;
	}

	public function getProductLayoutId($product_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return 0;
		}
	}

	public function getCategories($product_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		return $query->rows;
	}

	public function getProductsByVehicleModel($vehicle_model) {
		$sql = "SELECT DISTINCT p.product_id, 
				(SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, 
				(SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, 
				(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special,
				p.model, p.sku, p.upc, p.ean, p.jan, p.isbn, p.mpn, p.location, p.quantity, p.stock_status_id, p.image, p.manufacturer_id, p.shipping, p.price, p.points, p.tax_class_id, p.date_available, p.weight, p.weight_class_id, p.length, p.width, p.height, p.length_class_id, p.subtract, p.minimum, p.sort_order, p.status, p.date_added, p.date_modified, p.viewed,
				pd.name, pd.description, pd.tag, pd.meta_title, pd.meta_description, pd.meta_keyword
				FROM " . DB_PREFIX . "product p 
				LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
				LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p.product_id = pf.product_id) 
				LEFT JOIN " . DB_PREFIX . "filter f ON (pf.filter_id = f.filter_id) 
				LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) 
				WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
				AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
				AND p.status = '1' 
				AND p.date_available <= NOW()
				AND fd.name = '" . $this->db->escape($vehicle_model) . "'";
		
		$query = $this->db->query($sql);
		
		// Garantir que todos os campos necessários existam
		$results = array();
		foreach ($query->rows as $row) {
			// Definir valores padrão para campos que podem estar NULL
			$row['tax_class_id'] = $row['tax_class_id'] ? $row['tax_class_id'] : 0;
			$row['special'] = $row['special'] ? $row['special'] : '';
			$row['rating'] = $row['rating'] ? $row['rating'] : 0;
			$row['description'] = $row['description'] ? $row['description'] : '';
			$row['minimum'] = $row['minimum'] ? $row['minimum'] : 1;
			$row['quantity'] = $row['quantity'] ? $row['quantity'] : 0;
			$row['length'] = $row['length'] ? $row['length'] : 0;
			$row['width'] = $row['width'] ? $row['width'] : 0;
			$row['height'] = $row['height'] ? $row['height'] : 0;
			$row['weight'] = $row['weight'] ? $row['weight'] : 0;
			$row['weight_class_id'] = $row['weight_class_id'] ? $row['weight_class_id'] : 0;
			$row['length_class_id'] = $row['length_class_id'] ? $row['length_class_id'] : 0;
			$row['points'] = $row['points'] ? $row['points'] : 0;
			$row['subtract'] = $row['subtract'] ? $row['subtract'] : 0;
			$row['shipping'] = $row['shipping'] ? $row['shipping'] : 0;
			$row['viewed'] = $row['viewed'] ? $row['viewed'] : 0;
			$row['sort_order'] = $row['sort_order'] ? $row['sort_order'] : 0;
			
			$results[] = $row;
		}
		
		return $results;
	}

	public function getProductsByBrand($brand_name) {
		$sql = "SELECT DISTINCT p.product_id, 
				(SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, 
				(SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, 
				(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special,
				p.model, p.sku, p.upc, p.ean, p.jan, p.isbn, p.mpn, p.location, p.quantity, p.stock_status_id, p.image, p.manufacturer_id, p.shipping, p.price, p.points, p.tax_class_id, p.date_available, p.weight, p.weight_class_id, p.length, p.width, p.height, p.length_class_id, p.subtract, p.minimum, p.sort_order, p.status, p.date_added, p.date_modified, p.viewed,
				pd.name, pd.description, pd.tag, pd.meta_title, pd.meta_description, pd.meta_keyword
				FROM " . DB_PREFIX . "product p 
				LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
				LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p.product_id = pf.product_id) 
				LEFT JOIN " . DB_PREFIX . "filter f ON (pf.filter_id = f.filter_id) 
				LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) 
				WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
				AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
				AND p.status = '1' 
				AND p.date_available <= NOW()
				AND fd.name LIKE '" . $this->db->escape($brand_name) . "%'";
		
		$query = $this->db->query($sql);
		
		// Garantir que todos os campos necessários existam
		$results = array();
		foreach ($query->rows as $row) {
			// Definir valores padrão para campos que podem estar NULL
			$row['tax_class_id'] = $row['tax_class_id'] ? $row['tax_class_id'] : 0;
			$row['special'] = $row['special'] ? $row['special'] : '';
			$row['rating'] = $row['rating'] ? $row['rating'] : 0;
			$row['description'] = $row['description'] ? $row['description'] : '';
			$row['minimum'] = $row['minimum'] ? $row['minimum'] : 1;
			$row['quantity'] = $row['quantity'] ? $row['quantity'] : 0;
			$row['length'] = $row['length'] ? $row['length'] : 0;
			$row['width'] = $row['width'] ? $row['width'] : 0;
			$row['height'] = $row['height'] ? $row['height'] : 0;
			$row['weight'] = $row['weight'] ? $row['weight'] : 0;
			$row['weight_class_id'] = $row['weight_class_id'] ? $row['weight_class_id'] : 0;
			$row['length_class_id'] = $row['length_class_id'] ? $row['length_class_id'] : 0;
			$row['points'] = $row['points'] ? $row['points'] : 0;
			$row['subtract'] = $row['subtract'] ? $row['subtract'] : 0;
			$row['shipping'] = $row['shipping'] ? $row['shipping'] : 0;
			$row['viewed'] = $row['viewed'] ? $row['viewed'] : 0;
			$row['sort_order'] = $row['sort_order'] ? $row['sort_order'] : 0;
			
			$results[] = $row;
		}
		
		return $results;
	}

	public function getTotalProducts($data = array())
	{
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}

			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}

			if (!empty($data['filter_filter'])) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}

				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
			}
		}

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

				foreach ($words as $word) {
					$implode[] = "pd.tag LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			$sql .= ")";
		}

		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getProfile($product_id, $recurring_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recurring r JOIN " . DB_PREFIX . "product_recurring pr ON (pr.recurring_id = r.recurring_id AND pr.product_id = '" . (int)$product_id . "') WHERE pr.recurring_id = '" . (int)$recurring_id . "' AND status = '1' AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'");

		return $query->row;
	}

	public function getProfiles($product_id)
	{
		$query = $this->db->query("SELECT rd.* FROM " . DB_PREFIX . "product_recurring pr JOIN " . DB_PREFIX . "recurring_description rd ON (rd.language_id = " . (int)$this->config->get('config_language_id') . " AND rd.recurring_id = pr.recurring_id) JOIN " . DB_PREFIX . "recurring r ON r.recurring_id = rd.recurring_id WHERE pr.product_id = " . (int)$product_id . " AND status = '1' AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getTotalProductSpecials()
	{
		$query = $this->db->query("SELECT COUNT(DISTINCT ps.product_id) AS total FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))");

		if (isset($query->row['total'])) {
			return $query->row['total'];
		} else {
			return 0;
		}
	}

	public function checkProductCategory($product_id, $category_ids)
	{

		$implode = array();

		foreach ($category_ids as $category_id) {
			$implode[] = (int)$category_id;
		}

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id IN(" . implode(',', $implode) . ")");
		return $query->row;
	}

	public function getLenghtProduct($lenght_class_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "length_class_description WHERE length_class_id = '" . (int)$lenght_class_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

		$result = $query->row;

		return $result['unit']; // Obtém a unidade (exemplo: cm, m)

	}
}
