<?php
class ModelCatalogAbout extends Model {
	public function getAbout($about_id) {
		$query = $this->db->query("SELECT DISTINCT a.about_id, ad.title, ad.description, ad.meta_title, ad.meta_description, ad.meta_keyword FROM " . DB_PREFIX . "about a LEFT JOIN " . DB_PREFIX . "about_description ad ON (a.about_id = ad.about_id) WHERE a.about_id = '" . (int)$about_id . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND a.status = '1'");

		return $query->row;
	}

	public function getTestimonials($about_id) {
		$query = $this->db->query("SELECT customer_name, city, message, image, video_url, sort_order FROM " . DB_PREFIX . "about_testimonial WHERE about_id = '" . (int)$about_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getAboutSeoUrls($about_id) {
		$about_seo_url_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'about_id=" . (int)$about_id . "'");

		foreach ($query->rows as $result) {
			$about_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $about_seo_url_data;
	}
}
