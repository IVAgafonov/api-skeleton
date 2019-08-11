<?php

require_once __DIR__.'/vendor/autoload.php';

\App\System\Config\Config::init();

$db = new \App\System\DataProvider\Mysql\DataProvider(\App\System\Config\Config::get('mysql.main'));

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/mysql/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/mysql/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => 'app_main',
        'production' => [
            'connection' => $db->getPdo(),
            'name' => 'app_main',
        ]
    ],
    'version_order' => 'creation'
];