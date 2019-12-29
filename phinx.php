<?php

require_once __DIR__.'/vendor/autoload.php';

\App\System\App\App::init();

/** @var \App\System\DataProvider\Mysql\DataProviderInterface $db */
$db = \App\System\App\App::get(\App\System\DataProvider\Mysql\DataProviderInterface::class);

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/mysql/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/mysql/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' =>  $db->getDb(),
        'production' => [
            'connection' => $db->getPdo(),
            'name' => $db->getDb(),
        ]
    ],
    'version_order' => 'creation'
];