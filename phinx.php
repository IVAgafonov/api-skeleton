<?php

require_once __DIR__.'/vendor/autoload.php';

\App\System\Config\Config::init();

$db = \App\System\App\App::get(\App\System\DataProvider\Mysql\DataProviderInterface::class);

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