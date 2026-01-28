<?php
class ControllerCatalogImportExport extends Controller
{
    public function index()
    {
        $this->document->setTitle('Importar/Exportar Produtos');
        
        $this->load->model('catalog/product');

        if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
        
        $data['export_action'] = $this->url->link('catalog/import_export/exportXLSX', 'user_token=' . $this->session->data['user_token'], true);
        $data['import_action'] = $this->url->link('catalog/import_export/importXLSX', 'user_token=' . $this->session->data['user_token'], true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('catalog/import_export', $data));

        //$this->response->setOutput(json_encode($json));
    }

    public function exportXLSX()
    {
        require_once(DIR_SYSTEM . 'vendor/autoload.php');
        $this->load->model('catalog/product');
        $products = $this->model_catalog_product->getProducts();
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Definir cabeçalhos na nova ordem
        $headers = [
            'name', 'description', 'model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'location', 'quantity', 'image', 'price', 'weight', 'length', 'width', 'height'
        ];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', ucfirst($header));
            $col++;
        }

        $row = 2;
        foreach ($products as $product) {
            // Buscar a descrição e o nome do produto para o idioma padrão (assumindo ID 1)
            // Pode ser necessário ajustar o ID do idioma conforme a configuração da loja
            $name = '';
            $description = '';
            $product_descriptions = $this->model_catalog_product->getProductDescriptions($product['product_id']);
            if (isset($product_descriptions[1])) { // Assumindo language_id 1 para Português ou padrão
                $name = ucwords(html_entity_decode($product_descriptions[1]['name'], ENT_QUOTES, 'UTF-8'));
                $description = html_entity_decode($product_descriptions[1]['description'], ENT_QUOTES, 'UTF-8');
            }

            // Preencher dados do produto na nova ordem
            $col = 'A';
            $sheet->setCellValue($col++ . $row, $name);
            $sheet->setCellValue($col++ . $row, $description);
            $sheet->setCellValue($col++ . $row, $product['model']);
            $sheet->setCellValue($col++ . $row, $product['sku']);
            $sheet->setCellValue($col++ . $row, $product['upc']);
            $sheet->setCellValue($col++ . $row, $product['ean']);
            $sheet->setCellValue($col++ . $row, $product['jan']);
            $sheet->setCellValue($col++ . $row, $product['isbn']);
            $sheet->setCellValue($col++ . $row, $product['mpn']);
            $sheet->setCellValue($col++ . $row, $product['location']);
            $sheet->setCellValue($col++ . $row, $product['quantity']);
            $sheet->setCellValue($col++ . $row, $product['image']);
            $sheet->setCellValue($col++ . $row, $product['price']);
            $sheet->setCellValue($col++ . $row, $product['weight']);
            $sheet->setCellValue($col++ . $row, $product['length']);
            $sheet->setCellValue($col++ . $row, $product['width']);
            $sheet->setCellValue($col++ . $row, $product['height']);

            $row++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="products.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    public function importXLSX()
    {
        require_once(DIR_SYSTEM . 'vendor/autoload.php');
        $this->load->model('catalog/product');
        $this->load->language('catalog/product'); // Carregar linguagem do produto para usar texto de erro se necessário
        
        // Função auxiliar para gerar slug (URL amigável)
        if (!function_exists('generateSeoUrl')) {
            function generateSeoUrl($string) {
                // Converter para minúsculas
                $string = mb_strtolower($string, 'UTF-8');
                // Substituir caracteres acentuados e especiais
                $string = str_replace(
                    array('À', 'à', 'Á', 'á', 'Â', 'â', 'Ã', 'ã', 'Ä', 'ä', 'Å', 'å', 'Ā', 'ā', 'Ą', 'ą', 'Ă', 'ă', 'Æ', 'æ', 'Ç', 'ç', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'È', 'è', 'É', 'é', 'Ê', 'ê', 'Ë', 'ë', 'Ē', 'ē', 'Ę', 'ę', 'Ě', 'ě', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ì', 'ì', 'Í', 'í', 'Î', 'î', 'Ï', 'ï', 'Ī', 'ī', 'Ĩ', 'ĩ', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'K', 'k', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'Ñ', 'ñ', 'Ø', 'ø', 'Ō', 'ō', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ù', 'ù', 'Ú', 'ú', 'Û', 'û', 'Ü', 'ü', 'Ū', 'ū', 'Ũ', 'ũ', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ý', 'ý', 'ÿ', 'Ŷ', 'ŷ', 'Ÿ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ß', 'ſ', '\xA0', '\xAD', '\xe2\x80\x93', '\xe2\x80\x94', '\xe2\x80\x98', '\xe2\x80\x99', '\xe2\x80\x9c', '\xe2\x80\x9d', '\xe2\x80\xa6', '\xe2\x80\xb0'),
                    array('a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'ae', 'c', 'c', 'c', 'c', 'c', 'c', 'c', 'c', 'c', 'c', 'd', 'd', 'd', 'd', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'h', 'h', 'h', 'h', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'ij', 'ij', 'j', 'j', 'k', 'k', 'k', 'k', 'l', 'l', 'l', 'l', 'l', 'l', 'l', 'l', 'l', 'l', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'oe', 'oe', 'r', 'r', 'r', 'r', 'r', 'r', 's', 's', 's', 's', 's', 's', 's', 's', 't', 't', 't', 't', 't', 't', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'w', 'w', 'y', 'y', 'y', 'y', 'y', 'y', 'z', 'z', 'z', 'z', 'z', 'z', 'ss', 'ss', ' ', ' ', '-', '-', '\'', '\'', '\"', '\"', '...', '.'),
                    $string
                );
                // Remover todos os caracteres que não são letras, números ou hifens
                $string = preg_replace('/[^a-z0-9-]/i', '-', $string);
                // Substituir múltiplos hifens por um único
                $string = preg_replace('/-+/', '-', $string);
                // Remover hifens do início e fim
                $string = trim($string, '-');
                
                return $string;
            }
        }
        
        if (isset($this->request->files['import_file']['tmp_name'])) {
            $file = $this->request->files['import_file']['tmp_name'];
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            
            for ($row = 2; $row <= $highestRow; $row++) {
                // Ler valores das colunas na nova ordem
                $raw_name = $sheet->getCell('A' . $row)->getValue() ?: '';
                // Converter para minúsculas primeiro, depois capitalizar a primeira letra de cada palavra
                $name = ucwords(mb_strtolower($raw_name, 'UTF-8'));

                // Verificar se o nome está em branco, se sim, pular esta linha
                if (empty($name)) {
                    continue;
                }

                $description = $sheet->getCell('B' . $row)->getValue() ?: '';
                $model = $sheet->getCell('C' . $row)->getValue() ?: '';
                $sku = $sheet->getCell('D' . $row)->getValue() ?: '';
                $upc = $sheet->getCell('E' . $row)->getValue() ?: '';
                $ean = $sheet->getCell('F' . $row)->getValue() ?: '';
                $jan = $sheet->getCell('G' . $row)->getValue() ?: '';
                $isbn = $sheet->getCell('H' . $row)->getValue() ?: '';
                $mpn = $sheet->getCell('I' . $row)->getValue() ?: '';
                $location = $sheet->getCell('J' . $row)->getValue() ?: '';
                $quantity = $sheet->getCell('K' . $row)->getValue() ?: 0;
                $image = $sheet->getCell('L' . $row)->getValue() ?: '';
                $price = $sheet->getCell('M' . $row)->getValue() ?: 0;
                $weight = $sheet->getCell('N' . $row)->getValue() ?: 0;
                $length = $sheet->getCell('O' . $row)->getValue() ?: 0;
                $width = $sheet->getCell('P' . $row)->getValue() ?: 0;
                $height = $sheet->getCell('Q' . $row)->getValue() ?: 0;

                // Gerar SEO URL a partir do nome do produto
                $keyword = '';
                if (!empty($name)) {
                    $keyword = generateSeoUrl($name);
                    
                    // Verificar se a keyword já existe
                    $seo_url_info = $this->model_catalog_product->getSeoUrlByKeyword($keyword);
                    
                    // Se a keyword já existir, adicionar sufixo
                    $counter = 1;
                    while($seo_url_info) {
                         $keyword = generateSeoUrl($name) . '-' . $counter++;
                         $seo_url_info = $this->model_catalog_product->getSeoUrlByKeyword($keyword);
                    }
                }

                // Verificar se o produto existe (primeiro por UPC, depois por SKU, depois por model)
                $product = null;
                if ($upc) {
                    $product = $this->model_catalog_product->getProductByUPC($upc);
                }
                if (!$product && $sku) {
                    $product = $this->model_catalog_product->getProductBySKU($sku);
                }
                if (!$product && $model) {
                    $product = $this->model_catalog_product->getProductByModel($model);
                }

                // Preparar dados do produto para tabela oc_product
                $product_data = [
                    'model' => $model,
                    'sku' => $sku,
                    'upc' => $upc,
                    'ean' => $ean,
                    'jan' => $jan,
                    'isbn' => $isbn,
                    'mpn' => $mpn,
                    'location' => $location,
                    'quantity' => (int)$quantity,
                    'image' => $image,
                    'price' => (float)$price,
                    'weight' => (float)$weight,
                    'length' => (float)$length,
                    'width' => (float)$width,
                    'height' => (float)$height,
                    'status' => 0, // Produto ativo por padrão
                    'sort_order' => 0,
                    'date_available' => date('Y-m-d'),
                    'date_added' => date('Y-m-d H:i:s'),
                    'date_modified' => date('Y-m-d H:i:s'),
                    // Campos obrigatórios adicionais com valores padrão
                    'minimum' => 1, // Quantidade mínima para compra
                    'subtract' => 1, // Reduzir estoque ao vender (1 = sim, 0 = não)
                    'stock_status_id' => 1, // ID do status do estoque (1 = "Esgotado" no OpenCart padrão)
                    'manufacturer_id' => 0, // ID do fabricante (0 = sem fabricante)
                    'shipping' => 1, // Produto requer frete (1 = sim, 0 = não)
                    'points' => 0, // Pontos de recompensa
                    'weight_class_id' => 1, // ID da classe de peso (1 = kg no OpenCart padrão)
                    'length_class_id' => 1, // ID da classe de comprimento (1 = cm no OpenCart padrão)
                    'tax_class_id' => 0 // ID da classe de imposto (0 = sem imposto)
                ];

                // Preparar dados de descrição para tabela oc_product_description
                // Assumindo language_id 1 para português brasileiro
                $product_description = [
                    1 => [
                        'name' => $name,
                        'description' => $description,
                        'meta_title' => $name, // name também vai para meta_title
                        'meta_description' => $description, // description também vai para meta_description
                        'meta_keyword' => '',
                        'tag' => ''
                    ]
                ];

                $product_data['product_description'] = $product_description;
                
                // Adicionar dados de SEO URL
                $product_data['product_seo_url'] = [
                    0 => [
                        1 => $keyword // Assumindo store_id 0 e language_id 1
                    ]
                ];

                // *** ADICIONADO: Associar produto à loja padrão (store_id = 0) ***
                $product_data['product_store'] = [0]; // Loja padrão

                // *** ADICIONADO: Associar produto a uma categoria padrão (opcional) ***
                // Se você quiser associar a uma categoria específica, adicione:
                // $product_data['product_category'] = [1]; // ID da categoria padrão
                
                // *** ADICIONADO: Layout padrão para a loja ***
                $product_data['product_layout'] = [
                    0 => 0 // store_id 0, layout_id 0 (layout padrão)
                ];

                if ($product) {
                    // Atualizar produto existente
                    $this->model_catalog_product->editProduct($product['product_id'], $product_data);
                    $product_id_to_update = $product['product_id'];
                } else {
                    // Adicionar novo produto
                    $product_id_to_update = $this->model_catalog_product->addProduct($product_data);
                }

                // Forçar capitalização correta na tabela oc_product_description após salvar
                if ($product_id_to_update) {
                    $this->db->query("UPDATE " . DB_PREFIX . "product_description SET name = '" . $this->db->escape($name) . "', meta_title = '" . $this->db->escape($name) . "' WHERE product_id = '" . (int)$product_id_to_update . "' AND language_id = '1'");
                }
            }
        }
        
        $this->session->data['success'] = 'Importação concluída!';
        $this->response->redirect($this->url->link('catalog/import_export', 'user_token=' . $this->session->data['user_token'], true));
    }
}
