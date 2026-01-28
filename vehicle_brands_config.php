<?php
/**
 * Configuração das Marcas de Veículos
 * 
 * Este arquivo contém as configurações das marcas de veículos
 * que serão exibidas no módulo Vehicle Cover Section
 */

// Configuração das marcas de veículos
$vehicleBrandsConfig = array(
    // Marcas principais
    'main_brands' => array(
        'audi', 'bmw', 'mercedes', 'volkswagen', 'ford', 'chevrolet', 'fiat', 'honda', 'toyota',
        'nissan', 'hyundai', 'kia', 'peugeot', 'renault', 'citroen', 'opel', 'volvo'
    ),
    
    // Marcas de luxo
    'luxury_brands' => array(
        'jaguar', 'land rover', 'range rover', 'mini', 'alfa romeo', 'maserati', 'ferrari',
        'lamborghini', 'porsche', 'bentley', 'rolls royce', 'aston martin', 'mclaren'
    ),
    
    // Marcas asiáticas
    'asian_brands' => array(
        'lexus', 'infiniti', 'acura', 'mazda', 'mitsubishi', 'subaru', 'suzuki', 'daihatsu'
    ),
    
    // Marcas americanas
    'american_brands' => array(
        'cadillac', 'buick', 'chrysler', 'dodge', 'jeep', 'smart'
    ),
    
    // Marcas europeias
    'european_brands' => array(
        'seat', 'skoda', 'dacia', 'lada', 'fiat', 'lancia'
    ),
    
    // Marcas emergentes
    'emerging_brands' => array(
        'tata', 'mahindra', 'geely', 'haval', 'great wall'
    )
);

// Mapeamento de modelos populares por marca
$popularModelsConfig = array(
    'audi' => array('Audi A3', 'Audi A4', 'Audi Q5', 'Audi Q7', 'Audi TT'),
    'bmw' => array('BMW Série 3', 'BMW Série 5', 'BMW X3', 'BMW X5', 'BMW Z4'),
    'mercedes' => array('Mercedes Classe A', 'Mercedes Classe C', 'Mercedes Classe E', 'Mercedes GLC', 'Mercedes AMG'),
    'volkswagen' => array('Volkswagen Golf', 'Volkswagen Passat', 'Volkswagen Tiguan', 'Volkswagen Jetta', 'Volkswagen Polo'),
    'ford' => array('Ford Focus', 'Ford Fiesta', 'Ford Mondeo', 'Ford Kuga', 'Ford Ranger'),
    'chevrolet' => array('Chevrolet Onix', 'Chevrolet Cruze', 'Chevrolet Tracker', 'Chevrolet Spin', 'Chevrolet S10'),
    'fiat' => array('Fiat Argo', 'Fiat Mobi', 'Fiat Pulse', 'Fiat Fastback', 'Fiat Toro'),
    'honda' => array('Honda Civic', 'Honda HR-V', 'Honda CR-V', 'Honda Fit', 'Honda City'),
    'toyota' => array('Toyota Corolla', 'Toyota Yaris', 'Toyota RAV4', 'Toyota Hilux', 'Toyota Camry'),
    'nissan' => array('Nissan Versa', 'Nissan Sentra', 'Nissan Kicks', 'Nissan Frontier', 'Nissan Leaf'),
    'hyundai' => array('Hyundai HB20', 'Hyundai i30', 'Hyundai Tucson', 'Hyundai Santa Fe', 'Hyundai Elantra'),
    'kia' => array('Kia Picanto', 'Kia Rio', 'Kia Sportage', 'Kia Sorento', 'Kia Cerato'),
    'peugeot' => array('Peugeot 208', 'Peugeot 308', 'Peugeot 3008', 'Peugeot 5008', 'Peugeot 2008'),
    'renault' => array('Renault Kwid', 'Renault Sandero', 'Renault Captur', 'Renault Duster', 'Renault Logan'),
    'porsche' => array('Porsche 911', 'Porsche Cayenne', 'Porsche Macan', 'Porsche Panamera', 'Porsche Boxster'),
    'ferrari' => array('Ferrari F8', 'Ferrari 812', 'Ferrari SF90', 'Ferrari Roma', 'Ferrari Portofino'),
    'lamborghini' => array('Lamborghini Huracán', 'Lamborghini Aventador', 'Lamborghini Urus', 'Lamborghini Revuelto'),
    'maserati' => array('Maserati Ghibli', 'Maserati Levante', 'Maserati Quattroporte', 'Maserati Grecale'),
    'bentley' => array('Bentley Continental', 'Bentley Flying Spur', 'Bentley Bentayga', 'Bentley Mulsanne'),
    'rolls royce' => array('Rolls Royce Phantom', 'Rolls Royce Ghost', 'Rolls Royce Cullinan', 'Rolls Royce Dawn')
);

// Mapeamento de imagens das marcas
$brandImageMap = array(
    'audi' => 'catalog/view/theme/default/image/brands/audi.png',
    'bmw' => 'catalog/view/theme/default/image/brands/bmw.png',
    'mercedes' => 'catalog/view/theme/default/image/brands/mercedes.png',
    'volkswagen' => 'catalog/view/theme/default/image/brands/volkswagen.png',
    'ford' => 'catalog/view/theme/default/image/brands/ford.png',
    'chevrolet' => 'catalog/view/theme/default/image/brands/chevrolet.png',
    'fiat' => 'catalog/view/theme/default/image/brands/fiat.png',
    'honda' => 'catalog/view/theme/default/image/brands/honda.png',
    'toyota' => 'catalog/view/theme/default/image/brands/toyota.png',
    'nissan' => 'catalog/view/theme/default/image/brands/nissan.png',
    'hyundai' => 'catalog/view/theme/default/image/brands/hyundai.png',
    'kia' => 'catalog/view/theme/default/image/brands/kia.png',
    'peugeot' => 'catalog/view/theme/default/image/brands/peugeot.png',
    'renault' => 'catalog/view/theme/default/image/brands/renault.png',
    'citroen' => 'catalog/view/theme/default/image/brands/citroen.png',
    'opel' => 'catalog/view/theme/default/image/brands/opel.png',
    'volvo' => 'catalog/view/theme/default/image/brands/volvo.png',
    'jaguar' => 'catalog/view/theme/default/image/brands/jaguar.png',
    'land rover' => 'catalog/view/theme/default/image/brands/land_rover.png',
    'range rover' => 'catalog/view/theme/default/image/brands/range_rover.png',
    'mini' => 'catalog/view/theme/default/image/brands/mini.png',
    'alfa romeo' => 'catalog/view/theme/default/image/brands/alfa_romeo.png',
    'maserati' => 'catalog/view/theme/default/image/brands/maserati.png',
    'ferrari' => 'catalog/view/theme/default/image/brands/ferrari.png',
    'lamborghini' => 'catalog/view/theme/default/image/brands/lamborghini.png',
    'porsche' => 'catalog/view/theme/default/image/brands/porsche.png',
    'bentley' => 'catalog/view/theme/default/image/brands/bentley.png',
    'rolls royce' => 'catalog/view/theme/default/image/brands/rolls.png',
    'aston martin' => 'catalog/view/theme/default/image/brands/aston_martin.png',
    'mclaren' => 'catalog/view/theme/default/image/brands/mclaren.png',
    'lexus' => 'catalog/view/theme/default/image/brands/lexus.png',
    'infiniti' => 'catalog/view/theme/default/image/brands/infiniti.png',
    'acura' => 'catalog/view/theme/default/image/brands/acura.png',
    'cadillac' => 'catalog/view/theme/default/image/brands/cadillac.png',
    'buick' => 'catalog/view/theme/default/image/brands/buick.png',
    'chrysler' => 'catalog/view/theme/default/image/brands/chrysler.png',
    'dodge' => 'catalog/view/theme/default/image/brands/dodge.png',
    'jeep' => 'catalog/view/theme/default/image/brands/jeep.png',
    'mazda' => 'catalog/view/theme/default/image/brands/mazda.png',
    'mitsubishi' => 'catalog/view/theme/default/image/brands/mitsubishi.png',
    'subaru' => 'catalog/view/theme/default/image/brands/subaru.png',
    'suzuki' => 'catalog/view/theme/default/image/brands/suzuki.png'
);

// Configurações de exibição
$displayConfig = array(
    'max_brands' => 20,           // Máximo de marcas a exibir
    'brand_image_width' => 60,    // Largura da imagem da marca
    'brand_image_height' => 40,   // Altura da imagem da marca
    'show_brand_names' => true,   // Mostrar nomes das marcas
    'brand_name_font_size' => '10px',
    'brand_name_max_width' => 60
);

// Função para obter todas as marcas
function getAllVehicleBrands() {
    global $vehicleBrandsConfig;
    $allBrands = array();
    
    foreach ($vehicleBrandsConfig as $category => $brands) {
        $allBrands = array_merge($allBrands, $brands);
    }
    
    return array_unique($allBrands);
}

// Função para verificar se uma marca existe
function isVehicleBrand($brandName) {
    $allBrands = getAllVehicleBrands();
    $brandNameLower = strtolower(trim($brandName));
    
    return in_array($brandNameLower, $allBrands);
}

// Função para obter a imagem de uma marca
function getBrandImage($brandName) {
    global $brandImageMap;
    $brandNameLower = strtolower(trim($brandName));
    
    if (isset($brandImageMap[$brandNameLower])) {
        return $brandImageMap[$brandNameLower];
    }
    
    // Buscar por correspondência parcial
    foreach ($brandImageMap as $brand => $imagePath) {
        if (strpos($brandNameLower, $brand) !== false || strpos($brand, $brandNameLower) !== false) {
            return $imagePath;
        }
    }
    
    return 'catalog/view/theme/default/image/brands/default_brand.png';
}

// Função para obter modelos populares de uma marca
function getPopularModelsForBrand($brandName) {
    global $popularModelsConfig;
    $brandNameLower = strtolower(trim($brandName));
    
    if (isset($popularModelsConfig[$brandNameLower])) {
        return $popularModelsConfig[$brandNameLower];
    }
    
    return array();
}

// Função para criar URL de busca otimizada
function createBrandSearchUrl($brandName, $baseUrl = 'index.php?route=product/search') {
    $searchTerms = getPopularModelsForBrand($brandName);
    
    if (!empty($searchTerms)) {
        $searchTerm = $searchTerms[0];
        return $baseUrl . '&search=' . urlencode($searchTerm) . '&filter=' . urlencode($searchTerm);
    } else {
        return $baseUrl . '&search=' . urlencode($brandName) . '&filter=' . urlencode($brandName);
    }
}

// Exemplo de uso:
// $brands = getAllVehicleBrands();
// $isBrand = isVehicleBrand('BMW');
// $imagePath = getBrandImage('Audi');
// $models = getPopularModelsForBrand('BMW');
// $searchUrl = createBrandSearchUrl('BMW');
?>
