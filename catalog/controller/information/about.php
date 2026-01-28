<?php
class ControllerInformationAbout extends Controller {
	public function index() {
		$this->load->language('information/about');

		$this->load->model('catalog/about');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['about_id'])) {
			$about_id = (int)$this->request->get['about_id'];
		} else {
			$about_id = 0;
		}

		$about_info = $this->model_catalog_about->getAbout($about_id);

		if ($about_info) {
			$this->document->setTitle($about_info['meta_title']);
			$this->document->setDescription($about_info['meta_description']);
			$this->document->setKeywords($about_info['meta_keyword']);

			$data['breadcrumbs'][] = array(
				'text' => $about_info['title'],
				'href' => $this->url->link('information/about', 'about_id=' .  $about_id)
			);

			$data['heading_title'] = $about_info['title'];

			$data['description'] = html_entity_decode($about_info['description'], ENT_QUOTES, 'UTF-8');

			// Buscar depoimentos
			$testimonials = $this->model_catalog_about->getTestimonials($about_id);
			$data['testimonials'] = array();

			foreach ($testimonials as $testimonial) {
				$data['testimonials'][] = array(
					'customer_name' => $testimonial['customer_name'],
					'city'          => $testimonial['city'],
					'message' 		=> $testimonial['message'],
					'image'         => $testimonial['image'] ? $this->config->get('config_url') . 'image/' . $testimonial['image'] : '',
					'video_url'     => $testimonial['video_url'],
					'sort_order'    => $testimonial['sort_order']
				);
			}

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('information/about', $data));
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('information/about', 'about_id=' . $about_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
