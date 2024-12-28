-- Remover foreign keys relacionadas ao formulario_id
ALTER TABLE DAT1 DROP FOREIGN KEY fk_formulario_id;
ALTER TABLE DAT2 DROP FOREIGN KEY fk_formulario_id;
ALTER TABLE DAT4 DROP FOREIGN KEY fk_formulario_id;
ALTER TABLE user_vehicles DROP FOREIGN KEY fk_formulario_id;
ALTER TABLE vehicle_damages DROP FOREIGN KEY fk_formulario_id;

-- Dropar a coluna formulario_id das tabelas
ALTER TABLE DAT1 DROP COLUMN formulario_id;
ALTER TABLE DAT2 DROP COLUMN formulario_id;
ALTER TABLE DAT4 DROP COLUMN formulario_id;
ALTER TABLE user_vehicles DROP COLUMN formulario_id;
ALTER TABLE vehicle_damages DROP COLUMN formulario_id;