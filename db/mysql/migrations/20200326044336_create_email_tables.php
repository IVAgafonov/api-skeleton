<?php

use Phinx\Migration\AbstractMigration;

class CreateEmailTables extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->query("CREATE TABLE app_emails (
`id` int(13) unsigned NOT NULL AUTO_INCREMENT,
`sender_user_id` varchar(255) NOT NULL,
`recipient_user_id` varchar(255) NOT NULL,
`subject` varchar(255) NOT NULL,
`message` varchar(8096) NOT NULL,
`type` enum('SENT', 'RECEIVED') NOT NULL DEFAULT 'SENT',
`create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
`delete_date` datetime DEFAULT null,
`is_opened` tinyint(1) NOT NULL DEFAULT 0,
`is_important` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`id`),
KEY (`sender_user_id`),
KEY (`recipient_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin");
    }
}
