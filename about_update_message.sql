-- Adicionar campo message na tabela de depoimentos
ALTER TABLE `oc_about_testimonial` ADD COLUMN `message` TEXT DEFAULT NULL AFTER `city`;

-- Atualizar depoimentos existentes com mensagens de exemplo
UPDATE `oc_about_testimonial` SET `message` = 'Excelente atendimento e produtos de qualidade! Recomendo para todos.' WHERE `testimonial_id` = 1;
UPDATE `oc_about_testimonial` SET `message` = 'Superou todas as minhas expectativas. Empresa séria e comprometida.' WHERE `testimonial_id` = 2;
UPDATE `oc_about_testimonial` SET `message` = 'Profissionalismo e qualidade em todos os serviços prestados.' WHERE `testimonial_id` = 3;
