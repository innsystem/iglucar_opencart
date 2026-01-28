-- Instalação da funcionalidade de imagens para filtros
-- Tabela para armazenar imagens das descrições de filtros

-- Criar tabela oc_filter_description_images
CREATE TABLE IF NOT EXISTS `oc_filter_description_images` (
  `filter_image_id` int(11) NOT NULL AUTO_INCREMENT,
  `filter_description_id` int(11) NOT NULL,
  `filter_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`filter_image_id`),
  KEY `filter_description_id` (`filter_description_id`),
  KEY `filter_id` (`filter_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Adicionar comentário explicativo
ALTER TABLE `oc_filter_description_images` COMMENT = 'Tabela para armazenar múltiplas imagens para cada descrição de filtro';
