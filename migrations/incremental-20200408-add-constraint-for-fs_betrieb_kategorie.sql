UPDATE fs_betrieb
SET betrieb_kategorie_id = NULL
WHERE betrieb_kategorie_id NOT IN (
	SELECT id
	FROM fs_betrieb_kategorie
);
ALTER TABLE `fs_betrieb` MODIFY COLUMN `betrieb_kategorie_id` INT(10) UNSIGNED;
ALTER TABLE `fs_betrieb` ADD KEY `betrieb_kategorie_id_FK1` (`betrieb_kategorie_id`);
ALTER TABLE `fs_betrieb`
  ADD CONSTRAINT `fs_betrieb_kategorie_idfk_1` FOREIGN KEY (`betrieb_kategorie_id`) REFERENCES `fs_betrieb_kategorie` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;



