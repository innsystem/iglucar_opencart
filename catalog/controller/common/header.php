<?php
class ControllerCommonHeader extends Controller {
	public function index() {
		// Analytics
		$this->load->model('setting/extension');

		$data['analytics'] = array();

		$analytics = $this->model_setting_extension->getExtensions('analytics');

		foreach ($analytics as $analytic) {
			if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
			}
		}

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink($server . 'image/' . $this->config->get('config_icon'), 'icon');
		}

		$data['title'] = $this->document->getTitle();

		$data['base'] = $server;
		$data['body_class'] = isset($this->request->get['route']) ? $this->request->get['route'] : 'common-home';
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts('header');
		// add url image product to head
		$data['fbMetas'] = $this->document->getFBMeta();
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

		$this->load->language('common/header');

		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			$data['welcome_user'] = $this->customer->getFirstName();
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
		} else {
			$data['welcome_user'] = 'Minha Conta';
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), $this->customer->getFirstName(), $this->url->link('account/logout', '', true));
		
		$data['home'] = $this->url->link('common/home');
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['logged'] = $this->customer->isLogged();
		$data['account'] = $this->url->link('account/account', '', true);
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['contact'] = $this->url->link('information/contact');
		$data['manual_url'] = $this->url->link('information/manual');
		$data['mural_url'] = $this->url->link('information/mural_credibilidade');
		
		// Buscar URL da página Sobre Nós usando SEO URL
		try {
			$this->load->model('catalog/about');
			$about_info = $this->model_catalog_about->getAbout(1); // ID 1 da página Sobre Nós
			if ($about_info) {
				$data['about_title'] = $about_info['title'];
				// Verificar se SEO URLs estão habilitadas nas configurações
				if ($this->config->get('config_seo_url')) {
					// Tentar buscar SEO URL primeiro
					$seo_urls = $this->model_catalog_about->getAboutSeoUrls(1);
					$current_language_id = $this->config->get('config_language_id');
					$current_store_id = $this->config->get('config_store_id');
					
					if (isset($seo_urls[$current_store_id][$current_language_id]) && !empty($seo_urls[$current_store_id][$current_language_id])) {
						// Usar SEO URL se existir - construir URL amigável
						$seo_keyword = $seo_urls[$current_store_id][$current_language_id];
						$data['about_url'] = $this->config->get('config_url') . $seo_keyword;
					} else {
						// Fallback para URL padrão
						$data['about_url'] = $this->url->link('information/about', 'about_id=1', true);
					}
				} else {
					// SEO URLs desabilitadas, usar URL padrão
					$data['about_url'] = $this->url->link('information/about', 'about_id=1', true);
				}
			} else {
				// Fallback se a página não existir
				$data['about_url'] = $this->url->link('information/about', 'about_id=1', true);
			}
		} catch (Exception $e) {
			// Em caso de erro, usar URL padrão
			$data['about_title'] = 'Confiança que protege';
			$data['about_url'] = $this->url->link('information/about', 'about_id=1', true);
		}
		
		$data['telephone'] = $this->config->get('config_telephone');
		
		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->load->controller('common/search');
		$data['cart'] = $this->load->controller('common/cart');
		$data['menu'] = $this->load->controller('common/menu');
		
		// Carregar popup global
		$data['popup_global'] = $this->load->controller('extension/module/popup');
		
		// Adiciona o total de itens do carrinho no header
		$this->load->language('common/cart');
		$total = 0;
		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
			$total = $this->cart->getTotal();
		}
		$data['text_items'] = sprintf(
			$this->language->get('text_items'),
			$this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0),
			$this->currency->format($total, $this->session->data['currency'])
		);

		return $this->load->view('common/header', $data);
	}
}
