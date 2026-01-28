<?php
class ControllerSaleOrderAttachment extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('sale/order_attachment');
        $this->load->model('sale/order_attachment');

        $data = array();

        // Pass language variables
        $data['heading_title'] = $this->language->get('heading_title');
        $data['button_upload'] = $this->language->get('button_upload');
        $data['button_close'] = $this->language->get('button_close');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_download'] = $this->language->get('button_download');
        $data['text_drag_drop'] = $this->language->get('text_drag_drop');
        $data['text_max_files'] = $this->language->get('text_max_files');
        $data['text_allowed_types'] = $this->language->get('text_allowed_types');
        $data['text_max_size'] = $this->language->get('text_max_size');
        $data['text_no_attachments'] = $this->language->get('text_no_attachments');
        $data['text_confirm_delete'] = $this->language->get('text_confirm_delete');
        $data['error_no_files'] = $this->language->get('error_no_files');
        
        // Pass user token
        $data['user_token'] = $this->session->data['user_token'];
        
        // Pass URL helper
        $data['url_link'] = $this->url->link('', '', true);

        if (isset($this->request->get['order_id'])) {
            $order_id = (int)$this->request->get['order_id'];
            $data['attachments'] = $this->model_sale_order_attachment->getAttachments($order_id);
            $data['order_id'] = $order_id;
        }

        $this->response->setOutput($this->load->view('sale/order_attachment_modal', $data));
    }

    public function upload() {
        $this->load->language('sale/order_attachment');
        $this->load->model('sale/order_attachment');

        $json = array();

        if (!$this->user->hasPermission('modify', 'sale/order_attachment')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!isset($this->request->post['order_id']) || empty($this->request->post['order_id'])) {
            $json['error'] = $this->language->get('error_order_id');
        }

        if (!$json) {
            $order_id = (int)$this->request->post['order_id'];
            
            // Create directory if it doesn't exist
            $upload_path = DIR_IMAGE . 'catalog/order_attachments/' . $order_id . '/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }

            $uploaded_files = array();

            if (isset($this->request->files['files'])) {
                $files = $this->request->files['files'];
                
                // Handle multiple files
                if (is_array($files['name'])) {
                    for ($i = 0; $i < count($files['name']); $i++) {
                        if ($files['error'][$i] == UPLOAD_ERR_OK) {
                            $file_info = array(
                                'name' => $files['name'][$i],
                                'type' => $files['type'][$i],
                                'tmp_name' => $files['tmp_name'][$i],
                                'error' => $files['error'][$i],
                                'size' => $files['size'][$i]
                            );
                            
                            $result = $this->processFile($file_info, $order_id, $upload_path);
                            if ($result['success']) {
                                $uploaded_files[] = $result['file'];
                            } else {
                                $json['error'] = $result['error'];
                                break;
                            }
                        }
                    }
                } else {
                    // Single file
                    $result = $this->processFile($files, $order_id, $upload_path);
                    if ($result['success']) {
                        $uploaded_files[] = $result['file'];
                    } else {
                        $json['error'] = $result['error'];
                    }
                }
            }

            if (!$json && !empty($uploaded_files)) {
                $json['success'] = $this->language->get('text_upload_success');
                $json['files'] = $uploaded_files;
                
                // Send notification email
                $this->model_sale_order_attachment->sendNotification($order_id);
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function processFile($file, $order_id, $upload_path) {
        $allowed_extensions = array('pdf', 'docx', 'doc', 'jpg', 'jpeg', 'png', 'gif');
        $max_size = 10 * 1024 * 1024; // 10MB

        $filename = $file['name'];
        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Validate extension
        if (!in_array($file_extension, $allowed_extensions)) {
            return array('success' => false, 'error' => $this->language->get('error_file_type'));
        }

        // Validate size
        if ($file['size'] > $max_size) {
            return array('success' => false, 'error' => $this->language->get('error_file_size'));
        }

        // Generate unique filename
        $new_filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        $file_path = $upload_path . $new_filename;
        $relative_path = 'catalog/order_attachments/' . $order_id . '/' . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Save to database
            $attachment_id = $this->model_sale_order_attachment->addAttachment($order_id, $filename, $relative_path);
            
            return array(
                'success' => true,
                'file' => array(
                    'id' => $attachment_id,
                    'filename' => $filename,
                    'path' => $relative_path,
                    'url' => HTTP_CATALOG . 'image/' . $relative_path
                )
            );
        } else {
            return array('success' => false, 'error' => $this->language->get('error_upload'));
        }
    }

    public function getAttachments() {
        $this->load->model('sale/order_attachment');

        $json = array();

        if (isset($this->request->get['order_id'])) {
            $order_id = (int)$this->request->get['order_id'];
            $attachments = $this->model_sale_order_attachment->getAttachments($order_id);
            
            foreach ($attachments as &$attachment) {
                $attachment['url'] = HTTP_CATALOG . 'image/' . $attachment['path'];
                $attachment['download_url'] = $this->url->link('sale/order_attachment/download', 'user_token=' . $this->session->data['user_token'] . '&order_attachment_id=' . $attachment['order_attachment_id'], true);
            }
            
            $json['attachments'] = $attachments;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function download() {
        $this->load->model('sale/order_attachment');

        if (isset($this->request->get['order_attachment_id'])) {
            $order_attachment_id = (int)$this->request->get['order_attachment_id'];
            $attachment = $this->model_sale_order_attachment->getAttachment($order_attachment_id);

            if ($attachment) {
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
        
        // Redirect back if file not found
        $this->response->redirect($this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function view() {
        $this->load->model('sale/order_attachment');

        if (isset($this->request->get['order_attachment_id'])) {
            $order_attachment_id = (int)$this->request->get['order_attachment_id'];
            $attachment = $this->model_sale_order_attachment->getAttachment($order_attachment_id);

            if ($attachment) {
                $file_path = DIR_IMAGE . $attachment['path'];
                if (file_exists($file_path)) {
                    // Build the public URL to the file located in the image directory
                    $file_url = HTTP_CATALOG . 'image/' . $attachment['path'];
                    // Redirect so the browser opens the file in a new tab
                    $this->response->redirect($file_url);
                    return;
                }
            }
        }

        // Redirect back if file not found or parameters are missing
        $this->response->redirect($this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function deleteAttachment() {
        $this->load->language('sale/order_attachment');
        $this->load->model('sale/order_attachment');

        $json = array();

        if (!$this->user->hasPermission('modify', 'sale/order_attachment')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!isset($this->request->post['order_attachment_id'])) {
            $json['error'] = $this->language->get('error_attachment_id');
        }

        if (!$json) {
            $order_attachment_id = (int)$this->request->post['order_attachment_id'];
            $attachment = $this->model_sale_order_attachment->getAttachment($order_attachment_id);
            
            if ($attachment) {
                // Delete file from filesystem
                $file_path = DIR_IMAGE . $attachment['path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                
                // Delete from database
                $this->model_sale_order_attachment->deleteAttachment($order_attachment_id);
                
                $json['success'] = $this->language->get('text_delete_success');
            } else {
                $json['error'] = $this->language->get('error_attachment_not_found');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
