<?php
class ControllerStartupSeoUrl extends Controller {

	private $new_urls = array(
		'common/home' => '',
		'account/account' => 'conta/minha-conta',
		'account/wishlist' => 'conta/lista-desejos',
		'account/register' => 'conta/cadastro',
		'account/login' => 'conta/acessar',
		'account/forgotten' => 'conta/solicitar-senha',
		'account/edit' => 'conta/informacoes',
		'account/password' => 'conta/modificar-senha',
		'account/address' => 'conta/enderecos',
		'account/address/edit' => 'conta/enderecos/editar',
		'account/address/delete' => 'conta/enderecos/excluir',
		'account/address/add' => 'conta/enderecos/cadastro',
		'account/reward' => 'conta/pontos',
		'account/logout' => 'conta/sair',
		'account/order' => 'conta/historico',
		'account/order/info' => 'conta/historico/informacoes',
		'account/order/reorder' => 'conta/historico/refazer',
		'account/newsletter' => 'conta/informativo',
		'account/download' => 'conta/downloads',
		'account/transaction' => 'conta/transacoes',
		'account/recurring' => 'conta/assinaturas',
		'account/return' => 'conta/devolucoes',
		'account/return/add' => 'conta/devolucoes/cadastro',
		'account/return/success' => 'conta/devolucoes/confirmacao',
		'account/voucher' => 'conta/vale-presentes/comprar',
		'account/voucher/success' => 'conta/vale-presentes/confirmacao',
		'account/tracking' => 'conta/afiliados/gerador-links',
		'account/affiliate/edit' => 'conta/afiliados/editar',
		'affiliate/register' => 'afiliados/cadastro',
		'affiliate/login' => 'afiliados/acessar',
		'checkout/cart' => 'carrinho',
		'checkout/checkout' => 'carrinho/finalizar',
		'checkout/success' => 'carrinho/finalizar/confirmacao',
		'information/contact' => 'contato',
		'information/contact/success' => 'contato/enviado',
		'information/sitemap' => 'mapa-site',
		'product/special' => 'promocoes',
		'product/manufacturer' => 'marcas',
		'product/compare' => 'comparar-produtos',
		'product/search' => 'busca'
	);

	public function index() {
		// Add rewrite to url class
		if ($this->config->get('config_seo_url')) {
			$this->url->addRewrite($this);
		}

		// Decode URL
		if (isset($this->request->get['_route_'])) {
			$parts = explode('/', $this->request->get['_route_']);

			// remove any empty arrays from trailing
			if (utf8_strlen(end($parts)) == 0) {
				array_pop($parts);
			}

			foreach ($parts as $part) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($part) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

				if ($query->num_rows) {
					$url = explode('=', $query->row['query']);

					if ($url[0] == 'product_id') {
						$this->request->get['product_id'] = $url[1];
					}

					if ($url[0] == 'category_id') {
						if (!isset($this->request->get['path'])) {
							$this->request->get['path'] = $url[1];
						} else {
							$this->request->get['path'] .= '_' . $url[1];
						}
					}

					if ($url[0] == 'manufacturer_id') {
						$this->request->get['manufacturer_id'] = $url[1];
					}

					if ($url[0] == 'information_id') {
						$this->request->get['information_id'] = $url[1];
					}

					if ($url[0] == 'about_id') {
						$this->request->get['about_id'] = $url[1];
					}

					if ($query->row['query'] && $url[0] != 'information_id' && $url[0] != 'about_id' && $url[0] != 'manufacturer_id' && $url[0] != 'category_id' && $url[0] != 'product_id') {
						$this->request->get['route'] = $query->row['query'];
					}
				} else {
					if (in_array($this->request->get['_route_'], $this->new_urls)) {
						$this->request->get['route'] = array_search($this->request->get['_route_'], $this->new_urls);
					} else {
						$this->request->get['route'] = 'error/not_found';
					}

					break;
				}
			}

			if (!isset($this->request->get['route'])) {
				if (isset($this->request->get['product_id'])) {
					$this->request->get['route'] = 'product/product';
				} elseif (isset($this->request->get['path'])) {
					$this->request->get['route'] = 'product/category';
				} elseif (isset($this->request->get['manufacturer_id'])) {
					$this->request->get['route'] = 'product/manufacturer/info';
				} elseif (isset($this->request->get['information_id'])) {
					$this->request->get['route'] = 'information/information';
				} elseif (isset($this->request->get['about_id'])) {
					$this->request->get['route'] = 'information/about';
				}
			}
		}
	}

	public function rewrite($link) {
		$url_info = parse_url(str_replace('&amp;', '&', $link));

		$url = '';

		$data = array();

		parse_str($url_info['query'], $data);

		foreach ($data as $key => $value) {
			if (isset($data['route'])) {
				if (($data['route'] == 'product/product' && $key == 'product_id') || (($data['route'] == 'product/manufacturer/info' || $data['route'] == 'product/product') && $key == 'manufacturer_id') || ($data['route'] == 'information/information' && $key == 'information_id') || ($data['route'] == 'information/about' && $key == 'about_id')) {
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($key . '=' . (int)$value) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($query->num_rows && $query->row['keyword']) {
						$url .= '/' . $query->row['keyword'];

						unset($data[$key]);
					}
				} elseif ($key == 'path') {
					$categories = explode('_', $value);

					foreach ($categories as $category) {
						$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = 'category_id=" . (int)$category . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

						if ($query->num_rows && $query->row['keyword']) {
							$url .= '/' . $query->row['keyword'];
						} else {
							$url = '';

							break;
						}
					}

					unset($data[$key]);
				} else {
					$this->new_urls = array_flip($this->new_urls);
					if (in_array($data['route'], $this->new_urls)) {
						$url = '/' . array_search($data['route'], $this->new_urls);
					}
					$this->new_urls = array_flip($this->new_urls);
				}
			}
		}

		if ($url) {
			unset($data['route']);

			$query = '';

			if ($data) {
				foreach ($data as $key => $value) {
					$query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string)$value));
				}

				if ($query) {
					$query = '?' . str_replace('&', '&amp;', trim($query, '&'));
				}
			}

			return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . $query;
		} else {
			return $link;
		}
	}
}
