-- allow 'unspecified / unclear' options for #33
ALTER TABLE `fs_betrieb` MODIFY COLUMN `presse` TINYINT(4) NULL;
ALTER TABLE `fs_betrieb` MODIFY COLUMN `sticker` TINYINT(4) NULL;
