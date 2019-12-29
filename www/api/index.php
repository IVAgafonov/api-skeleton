<?php

require_once __DIR__.'/../../vendor/autoload.php';

set_time_limit(60);
ini_set('memory_limit', '2G');

\App\System\App\App::init();
\App\System\Router\Router::init();