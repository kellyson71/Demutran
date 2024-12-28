ALTER TABLE `formularios_dat_central` 
ADD COLUMN `preenchimento_status` varchar(50) DEFAULT 'Incompleto' 
AFTER `status`;
