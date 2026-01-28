<?php
class ControllerInformationMuralCredibilidade extends Controller {
    public function index() {
        $this->load->language('information/mural_credibilidade');
        $this->load->model('catalog/mural_credibilidade');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/mural_credibilidade')
        );
        
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_gallery'] = $this->language->get('text_gallery');
        
        // Buscar imagens do mural
        $mural_images = $this->model_catalog_mural_credibilidade->getActiveMuralImages();
        
        $data['mural_images'] = array();
        
        foreach ($mural_images as $mural_image) {
            if (is_file(DIR_IMAGE . $mural_image['image'])) {
                $data['mural_images'][] = array(
                    'image' => $this->config->get('config_url') . 'image/' . $mural_image['image'],
                    'thumb' => $this->config->get('config_url') . 'image/' . $mural_image['image'],
                    'title' => $mural_image['title']
                );
            }
        }
        
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('information/mural_credibilidade', $data));
    }
}
