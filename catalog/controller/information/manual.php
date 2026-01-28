<?php
class ControllerInformationManual extends Controller {
    public function index() {
        $this->load->language('information/manual');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/manual')
        );
        
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_download'] = $this->language->get('text_download');
        $data['text_manual_pdf'] = $this->language->get('text_manual_pdf');
        $data['text_no_manual'] = $this->language->get('text_no_manual');
        
        // Get the manual PDF from settings
        $manual_pdf = $this->config->get('config_manual_pdf');
        
        if ($manual_pdf && file_exists(DIR_IMAGE . 'catalog/docs/' . $manual_pdf)) {
            $data['manual_pdf_url'] = $this->config->get('config_url') . 'image/catalog/docs/' . $manual_pdf;
            $data['download_url'] = $this->url->link('information/manual/download');
            $data['has_manual'] = true;
        } else {
            $data['manual_pdf_url'] = $this->config->get('config_url') . 'image/catalog/docs/manual.pdf';
            $data['download_url'] = $this->url->link('information/manual/download');
            $data['has_manual'] = true;
        }
        
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        
        $this->response->setOutput($this->load->view('information/manual', $data));
    }
    
    public function download() {
        // Get the manual PDF from settings
        $manual_pdf = $this->config->get('config_manual_pdf');
        
        if ($manual_pdf) {
            $file = DIR_IMAGE . 'catalog/docs/' . $manual_pdf;
            
            if (file_exists($file)) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $manual_pdf . '"');
                header('Content-Length: ' . filesize($file));
                readfile($file);
                exit;
            }
        }
        
        // If no manual is configured or file doesn't exist, redirect to 404
        $this->response->redirect($this->url->link('error/not_found'));
    }
}
