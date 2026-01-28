-- Instalação do Módulo Vehicle Cover Section
-- Este arquivo deve ser executado no banco de dados OpenCart

-- Inserir o módulo na tabela de extensões
INSERT INTO `oc_extension` (`type`, `code`) VALUES ('module', 'vehicle_cover_section');

-- Inserir o módulo na tabela de módulos (ajuste o store_id conforme necessário)
INSERT INTO `oc_setting` (`store_id`, `code`, `key`, `value`, `serialized`) VALUES 
(0, 'module_vehicle_cover_section', 'module_vehicle_cover_section_status', '1', 0),
(0, 'module_vehicle_cover_section', 'module_vehicle_cover_section_name', 'Seção de Capa para Veículos', 0);

-- Nota: Após executar este SQL, você pode acessar o painel administrativo
-- e ir em Extensões > Módulos para configurar o módulo
