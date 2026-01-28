<?php
class ModelCatalogAbout extends Model {
	public function addAbout($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "about SET sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "'");

		$about_id = $this->db->getLastId();

		foreach ($data['about_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "about_description SET about_id = '" . (int)$about_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// Depoimentos
		if (isset($data['testimonials'])) {
			foreach ($data['testimonials'] as $testimonial) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "about_testimonial SET about_id = '" . (int)$about_id . "', customer_name = '" . $this->db->escape($testimonial['customer_name']) . "', city = '" . $this->db->escape($testimonial['city']) . "', message = '" . $this->db->escape($testimonial['message']) . "', image = '" . $this->db->escape($testimonial['image']) . "', video_url = '" . $this->db->escape($testimonial['video_url']) . "', sort_order = '" . (int)$testimonial['sort_order'] . "'");
			}
		}

		if (isset($data['about_store'])) {
			foreach ($data['about_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "about_to_store SET about_id = '" . (int)$about_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		// SEO URL
		if (isset($data['about_seo_url'])) {
			$this->load->model('design/seo_url');
			
			foreach ($data['about_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->model_design_seo_url->addSeoUrl(array(
							'store_id' => $store_id,
							'language_id' => $language_id,
							'query' => 'about_id=' . $about_id,
							'keyword' => $keyword
						));
					}
				}
			}
		}
		
		if (isset($data['about_layout'])) {
			foreach ($data['about_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "about_to_layout SET about_id = '" . (int)$about_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('about');

		return $about_id;
	}

	public function editAbout($about_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "about SET sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "' WHERE about_id = '" . (int)$about_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "about_description WHERE about_id = '" . (int)$about_id . "'");

		foreach ($data['about_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "about_description SET about_id = '" . (int)$about_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// Depoimentos
		$this->db->query("DELETE FROM " . DB_PREFIX . "about_testimonial WHERE about_id = '" . (int)$about_id . "'");
		
		if (isset($data['testimonials'])) {
			foreach ($data['testimonials'] as $testimonial) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "about_testimonial SET about_id = '" . (int)$about_id . "', customer_name = '" . $this->db->escape($testimonial['customer_name']) . "', city = '" . $this->db->escape($testimonial['city']) . "', message = '" . $this->db->escape($testimonial['message']) . "', image = '" . $this->db->escape($testimonial['image']) . "', video_url = '" . $this->db->escape($testimonial['video_url']) . "', sort_order = '" . (int)$testimonial['sort_order'] . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "about_to_store WHERE about_id = '" . (int)$about_id . "'");

		if (isset($data['about_store'])) {
			foreach ($data['about_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "about_to_store SET about_id = '" . (int)$about_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		// SEO URL
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'about_id=" . (int)$about_id . "'");

		if (isset($data['about_seo_url'])) {
			$this->load->model('design/seo_url');
			
			foreach ($data['about_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (trim($keyword)) {
						$this->model_design_seo_url->addSeoUrl(array(
							'store_id' => $store_id,
							'language_id' => $language_id,
							'query' => 'about_id=' . $about_id,
							'keyword' => $keyword
						));
					}
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "about_to_layout` WHERE about_id = '" . (int)$about_id . "'");

		if (isset($data['about_layout'])) {
			foreach ($data['about_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "about_to_layout` SET about_id = '" . (int)$about_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('about');
	}

	public function deleteAbout($about_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "about` WHERE about_id = '" . (int)$about_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "about_description` WHERE about_id = '" . (int)$about_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "about_testimonial` WHERE about_id = '" . (int)$about_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "about_to_store` WHERE about_id = '" . (int)$about_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "about_to_layout` WHERE about_id = '" . (int)$about_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'about_id=" . (int)$about_id . "'");

		$this->cache->delete('about');
	}

	public function getAbout($about_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "about WHERE about_id = '" . (int)$about_id . "'");

		return $query->row;
	}

	public function getAbouts($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "about a LEFT JOIN " . DB_PREFIX . "about_description ad ON (a.about_id = ad.about_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			$sort_data = array(
				'ad.title',
				'a.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY ad.title";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
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

			$query = $this->db->query($sql);

			return $query->rows;
		} else {
			$about_data = $this->cache->get('about.' . (int)$this->config->get('config_language_id'));

			if (!$about_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "about a LEFT JOIN " . DB_PREFIX . "about_description ad ON (a.about_id = ad.about_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ad.title");

				$about_data = $query->rows;

				$this->cache->set('about.' . (int)$this->config->get('config_language_id'), $about_data);
			}

			return $about_data;
		}
	}

	public function getAboutDescriptions($about_id) {
		$about_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "about_description WHERE about_id = '" . (int)$about_id . "'");

		foreach ($query->rows as $result) {
			$about_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			);
		}

		return $about_description_data;
	}

	public function getTestimonials($about_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "about_testimonial WHERE about_id = '" . (int)$about_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getAboutStores($about_id) {
		$about_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "about_to_store WHERE about_id = '" . (int)$about_id . "'");

		foreach ($query->rows as $result) {
			$about_store_data[] = $result['store_id'];
		}

		return $about_store_data;
	}

	public function getAboutSeoUrls($about_id) {
		$about_seo_url_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'about_id=" . (int)$about_id . "'");

		foreach ($query->rows as $result) {
			$about_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $about_seo_url_data;
	}

	public function getAboutLayouts($about_id) {
		$about_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "about_to_layout WHERE about_id = '" . (int)$about_id . "'");

		foreach ($query->rows as $result) {
			$about_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $about_layout_data;
	}

	public function getTotalAbouts() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "about");

		return $query->row['total'];
	}

	public function getTotalAboutsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "about_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}
}
