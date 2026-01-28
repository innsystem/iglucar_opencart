<?php
class ControllerExtensionModuleVideoCarousel extends Controller {
	public function index($setting) {
		static $module = 0;

		$this->load->model('tool/image');
		
		// Carregar CSS e JS do Swiper apenas se for formato carousel
		if (isset($setting['format']) && $setting['format'] == 'carousel') {
			$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
			$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css?1');
			$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.min.js');
		}

		$data['format'] = isset($setting['format']) ? $setting['format'] : 'carousel';
		$data['video_items'] = array();

		if (isset($setting['video_item']) && is_array($setting['video_item'])) {
			// Ordenar por sort_order
			usort($setting['video_item'], function($a, $b) {
				$sort_a = isset($a['sort_order']) ? (int)$a['sort_order'] : 0;
				$sort_b = isset($b['sort_order']) ? (int)$b['sort_order'] : 0;
				return $sort_a - $sort_b;
			});

			foreach ($setting['video_item'] as $video_item) {
				// Converter URL do YouTube para formato embed
				$youtube_url = isset($video_item['youtube_url']) ? $video_item['youtube_url'] : '';
				$embed_url = $this->convertToEmbedUrl($youtube_url);

				// Só processar se tiver URL válida do YouTube
				if (!$embed_url) {
					continue;
				}

				// Verificar se tem imagem válida
				if (isset($video_item['image']) && !empty($video_item['image']) && is_file(DIR_IMAGE . $video_item['image'])) {
					$image = $this->model_tool_image->resize($video_item['image'], 800, 600);
					$thumb = $this->model_tool_image->resize($video_item['image'], 400, 300);
				} else {
					// Se não tiver imagem, definir como vazio para mostrar placeholder
					$image = '';
					$thumb = '';
				}

				$data['video_items'][] = array(
					'title'      => isset($video_item['title']) ? $video_item['title'] : '',
					'image'      => $image,
					'thumb'      => $thumb,
					'youtube_url' => $youtube_url,
					'embed_url'  => $embed_url
				);
			}
		}

		$data['module'] = $module++;

		return $this->load->view('extension/module/video_carousel', $data);
	}

	/**
	 * Converte URLs do YouTube para formato embed
	 */
	private function convertToEmbedUrl($url) {
		if (empty($url)) {
			return '';
		}

		$url = trim($url);

		// Se já for formato embed, retornar como está
		if (strpos($url, 'youtube.com/embed/') !== false) {
			return $url;
		}

		// Formato Shorts: https://www.youtube.com/shorts/VIDEO_ID
		if (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
			return 'https://www.youtube.com/embed/' . $matches[1];
		}

		// Formato: https://www.youtube.com/watch?v=VIDEO_ID
		if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
			return 'https://www.youtube.com/embed/' . $matches[1];
		}

		// Formato: https://youtu.be/VIDEO_ID
		if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
			return 'https://www.youtube.com/embed/' . $matches[1];
		}

		// Formato: https://www.youtube.com/watch?v=VIDEO_ID&t=123s
		if (preg_match('/youtube\.com\/watch\?.*v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
			$video_id = $matches[1];
			// Extrair tempo se existir
			$time = '';
			if (preg_match('/[&?]t=(\d+)s/', $url, $time_matches)) {
				$time = '?start=' . $time_matches[1];
			}
			return 'https://www.youtube.com/embed/' . $video_id . $time;
		}

		return '';
	}
}
