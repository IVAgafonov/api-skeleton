<?php

use Phinx\Migration\AbstractMigration;

class CreateAuthTables extends AbstractMigration
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
        $this->query("CREATE TABLE app_users_auth_tokens (
`token` binary(32) NOT NULL,
`user_id` int(13) NOT NULL,
`create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
`expire_date` datetime DEFAULT null,
`type` enum('TEMPORARY', 'PERMANENT') NOT NULL DEFAULT 'TEMPORARY',
PRIMARY KEY (`token`),
KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin");
    }
}
