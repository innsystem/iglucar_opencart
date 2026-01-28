<?php
class ControllerCommonMenu extends Controller {
    public function index() {
        $this->load->language('common/menu');
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');

        $data['categories'] = array();
        $data['allCategories'] = array();

        $data['menu_special'] = $this->url->link('product/special');

        // Obtém todas as categorias principais (nível 1)
        $categories = $this->model_catalog_category->getCategories(0);

        foreach ($categories as $category) {
            // Obtém subcategorias (nível 2)
            $children_data = array();
            $children = $this->model_catalog_category->getCategories($category['category_id']);

            foreach ($children as $child) {
                $filter_data = array(
                    'filter_category_id'  => $child['category_id'],
                    'filter_sub_category' => true
                );

                $children_data[] = array(
                    'name'  => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
                    'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
                );
            }

            // Se a categoria está marcada para exibição no topo, adiciona em `$data['categories']`
            if ($category['top']) {
                $data['categories'][] = array(
                    'name'     => $category['name'],
                    'children' => $children_data,
                    'column'   => $category['column'] ? $category['column'] : 1,
                    'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
                );
            }

            // Todas as categorias habilitadas entram em `$data['allCategories']`
            $data['allCategories'][] = array(
                'name'     => $category['name'],
                'children' => $children_data,
                'column'   => $category['column'] ? $category['column'] : 1,
                'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
            );
        }

        return $this->load->view('common/menu', $data);
    }
}
