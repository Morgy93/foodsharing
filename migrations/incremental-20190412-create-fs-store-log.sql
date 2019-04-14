create table IF NOT EXISTS fs_store_log
(
    id             int (10)      unsigned NOT NULL                    COMMENT 'unique id of entry',
    store_id       int (10)      unsigned NOT NULL                    COMMENT 'ID of Store',
    date_activity  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP   COMMENT 'when did the action take place',
    action         tinyint (4)   unsigned NOT NULL                    COMMENT 'action type  that was performed' ,
    fs_id_a        int (10)      unsigned NOT NULL                    COMMENT 'foodsaver_id who is doing the action',
    fs_id_p        int (10)      unsigned default null                COMMENT 'to which foodsaver_id is it done to',
    date_reference datetime      default null                         COMMENT 'date referenced (slot or wallpost entry)',
    content        varchar(255)  default null	                      COMMENT 'Text from the store-wall-entry',
    reason         varchar(255)  default null		                  COMMENT 'Why a negativ action was done'
);

ALTER TABLE `fs_store_log`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `fs_store_log`
    MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
