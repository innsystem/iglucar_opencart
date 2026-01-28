-- Product Quick Edit Installation Script
-- Versão: 1.0.0
-- Compatível com: OpenCart 3.x/4.x

-- Criar tabela de logs para edições rápidas (se não existir)
CREATE TABLE IF NOT EXISTS `oc_product_edit_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `field` varchar(64) NOT NULL,
  `old_value` text,
  `new_value` text,
  `user_id` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  KEY `date_added` (`date_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir permissões para o módulo (se não existir)
INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) 
SELECT `user_group_id`, `name`, 
  CONCAT(
    COALESCE(`permission`, ''), 
    IF(LOCATE('catalog/product_quick_edit', COALESCE(`permission`, '')) = 0, 
      CONCAT(
        IF(`permission` = '' OR `permission` IS NULL, '', ';'),
        'access[catalog/product_quick_edit];modify[catalog/product_quick_edit]'
      ), 
      ''
    )
  )
FROM `oc_user_group` 
WHERE `user_group_id` = 1; -- Grupo de administradores

-- Mensagem de sucesso
SELECT 'Product Quick Edit instalado com sucesso!' as message;

-- Verificações pós-instalação
SELECT 
  CASE 
    WHEN EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'oc_product_edit_log') 
    THEN 'OK' 
    ELSE 'ERRO' 
  END as 'Tabela de logs',
  CASE 
    WHEN EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'oc_seo_url') 
    THEN 'OK' 
    ELSE 'ERRO' 
  END as 'Tabela SEO URL',
  CASE 
    WHEN EXISTS (SELECT 1 FROM oc_user_group WHERE permission LIKE '%catalog/product_quick_edit%') 
    THEN 'OK' 
    ELSE 'ATENÇÃO: Configurar permissões manualmente' 
  END as 'Permissões'; 