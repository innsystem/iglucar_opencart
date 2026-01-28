<?php
class ControllerAccountOrderAttachment extends Controller {
    
    public function index() {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/order_attachment', '', true);
            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/order_attachment');
        $this->load->model('account/order_attachment');
        $this->load->model('account/order');

        $data = array();

        if (isset($this->request->get['order_id'])) {
            $order_id = (int)$this->request->get['order_id'];
            
    		$this->document->setTitle('Anexos do Pedido #' . $order_id);

            // Verify order belongs to customer
            $order_info = $this->model_account_order->getOrder($order_id);
            
            if ($order_info) {
                $data['attachments'] = $this->model_account_order_attachment->getAttachments($order_id, $this->customer->getId());
                $data['order_id'] = $order_id;
                $data['order_info'] = $order_info;
            } else {
                $data['error'] = $this->language->get('error_order_not_found');
            }
        } else {
            $data['error'] = $this->language->get('error_order_id');
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_order'),
            'href' => $this->url->link('account/order', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('account/order_attachment', 'order_id=' . (int)$this->request->get['order_id'], true)
        );

        $data['continue'] = $this->url->link('account/order', '', true);

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/order_attachment_list', $data));
    }

    public function download() {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/order_attachment', '', true);
            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->model('account/order_attachment');
        $this->load->model('account/order');

        if (isset($this->request->get['order_attachment_id'])) {
            $order_attachment_id = (int)$this->request->get['order_attachment_id'];
            $attachment = $this->model_account_order_attachment->getAttachment($order_attachment_id);

            if ($attachment) {
                // Verify order belongs to customer
                $order_info = $this->model_account_order->getOrder($attachment['order_id']);
                
                if ($order_info) {
                    $file_path = DIR_IMAGE . $attachment['path'];
                    
                    if (file_exists($file_path)) {
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="' . $attachment['filename'] . '"');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($file_path));
                        readfile($file_path);
                        exit;
                    }
                }
            }
        }
        
        // Redirect back if file not found or access denied
        $this->response->redirect($this->url->link('account/order', '', true));
    }

    public function getAttachments() {
        if (!$this->customer->isLogged()) {
            $json = array('error' => 'Not logged in');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->load->model('account/order_attachment');
        $this->load->model('account/order');

        $json = array();

        if (isset($this->request->get['order_id'])) {
            $order_id = (int)$this->request->get['order_id'];
            
            // Verify order belongs to customer
            $order_info = $this->model_account_order->getOrder($order_id);
            
            if ($order_info) {
                $attachments = $this->model_account_order_attachment->getAttachments($order_id, $this->customer->getId());
                
                foreach ($attachments as &$attachment) {
                    $attachment['download_url'] = $this->url->link('account/order_attachment/download', 'order_attachment_id=' . $attachment['order_attachment_id'], true);
                }
                
                $json['attachments'] = $attachments;
            } else {
                $json['error'] = 'Order not found';
            }
        } else {
            $json['error'] = 'Order ID required';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
