<?php
class ControllerExtensionModuleCategoryIcons extends Controller
{
    public function index()
    {
        $this->load->language('extension/module/category_icons');

        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
        } else {
            $parts = array();
        }

        if (isset($parts[0])) {
            $data['category_id'] = $parts[0];
        } else {
            $data['category_id'] = 0;
        }

        if (isset($parts[1])) {
            $data['child_id'] = $parts[1];
        } else {
            $data['child_id'] = 0;
        }

        $this->load->model('catalog/category');

        $this->load->model('catalog/product');

        $this->load->model('tool/image');

        $data['categories'] = array();

        $categories = $this->model_catalog_category->getCategories(0);

        foreach ($categories as $category) {
            if (isset($category['image']) && $category['image'] != '') {
                $filter_data = array(
                    'filter_category_id'  => $category['category_id'],
                    'filter_sub_category' => true
                );

                $data['categories'][] = array(
                    'category_id' => $category['category_id'],
                    'image' => $this->model_tool_image->resize($category['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height')),
                    'name'        => $category['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
                    'href'        => $this->url->link('product/category', 'path=' . $category['category_id'])
                );
            }
        }

        return $this->load->view('extension/module/category_icons', $data);
    }
}
