-- Tabela principal do Mural da Credibilidade
CREATE TABLE IF NOT EXISTS `oc_mural_credibilidade` (
  `mural_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `sort_order` int(3) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`mural_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Tabela de imagens do Mural da Credibilidade
CREATE TABLE IF NOT EXISTS `oc_mural_credibilidade_image` (
  `mural_image_id` int(11) NOT NULL AUTO_INCREMENT,
  `mural_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mural_image_id`),
  KEY `mural_id` (`mural_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
