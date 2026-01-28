-- Criar tabela principal para a página Sobre Nós
CREATE TABLE IF NOT EXISTS `oc_about` (
  `about_id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_order` int(3) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`about_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Criar tabela para descrições multilíngue
CREATE TABLE IF NOT EXISTS `oc_about_description` (
  `about_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(64) NOT NULL,
  `description` text NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keyword` varchar(255) NOT NULL,
  PRIMARY KEY (`about_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Criar tabela para depoimentos de clientes
CREATE TABLE IF NOT EXISTS `oc_about_testimonial` (
  `testimonial_id` int(11) NOT NULL AUTO_INCREMENT,
  `about_id` int(11) NOT NULL,
  `customer_name` varchar(64) NOT NULL,
  `city` varchar(64) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `sort_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`testimonial_id`),
  KEY `about_id` (`about_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Criar tabela para relacionar com lojas
CREATE TABLE IF NOT EXISTS `oc_about_to_store` (
  `about_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`about_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Criar tabela para relacionar com layouts
CREATE TABLE IF NOT EXISTS `oc_about_to_layout` (
  `about_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `layout_id` int(11) NOT NULL,
  PRIMARY KEY (`about_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Inserir dados de exemplo para a página Sobre Nós
INSERT INTO `oc_about` (`about_id`, `sort_order`, `status`) VALUES (1, 0, 1);

-- Inserir descrição em português
INSERT INTO `oc_about_description` (`about_id`, `language_id`, `title`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES 
(1, 1, 'Sobre Nós', '<p>Bem-vindo à nossa empresa! Somos especialistas em fornecer soluções de qualidade para nossos clientes.</p><p>Nossa missão é oferecer produtos e serviços excepcionais, sempre priorizando a satisfação do cliente e a excelência em tudo que fazemos.</p>', 'Sobre Nós - Nossa Empresa', 'Conheça mais sobre nossa empresa, nossa história e nossa missão de fornecer soluções de qualidade.', 'sobre nós, empresa, missão, qualidade');

-- Inserir depoimentos de exemplo
INSERT INTO `oc_about_testimonial` (`about_id`, `customer_name`, `city`, `image`, `video_url`, `sort_order`) VALUES 
(1, 'João Silva', 'São Paulo', '', '', 1),
(1, 'Maria Santos', 'Rio de Janeiro', '', '', 2),
(1, 'Pedro Oliveira', 'Belo Horizonte', '', '', 3);

-- Relacionar com a loja padrão
INSERT INTO `oc_about_to_store` (`about_id`, `store_id`) VALUES (1, 0);

-- Relacionar com layout padrão
INSERT INTO `oc_about_to_layout` (`about_id`, `store_id`, `layout_id`) VALUES (1, 0, 0);
