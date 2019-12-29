<?php

namespace App\System\App;

use App\System\Config\Config;
use App\System\Env\Env;
use App\System\Logger\Logger;

/**
 * Class App
 * @package App\System\App
 */
class App
{
    private static $container = null;

    public static function get(string $className)
    {
        if (static::$container === null) {
            throw new \Exception("Container doesn't exist");
        }
        return static::$container->get($className);
    }

    public static function init()
    {
        http_response_code(500);
        set_error_handler(function (int $code, string $message, string $file, int $line, $context = null) {
            $msg = $message." in file: ".$file." on line: ".$line.". Code: ".$code;
            if (Env::isDev()) {
                echo $msg;
            }
            error_log($msg);
            $logger = new Logger();
            $logger->error($msg);
            die();
        });
        set_exception_handler(function (\Throwable $e) {
            if ($e->getCode() > 300 && $e->getCode() < 600) {
                http_response_code($e->getCode());
            }
            $msg = "Thrown exception [".get_class($e)."] with message: ".$e->getMessage()." in file: ".$e->getFile()." on line: ".$e->getLine();
            $trace = $e->getTrace();

            if ($trace && is_array($trace)) {
                $msg .= " Trace:";
                foreach ($trace as $i => $t) {
                    if (!empty($t['file']) && !empty($t['line'])) {
                        $msg .= "[".$i." => ".$t['file'].": ".$t['line']."]";
                    }
                }
            }
            if (Env::isDev()) {
                echo $msg;
            }
            error_log($msg);
            $logger = new Logger();
            $logger->error($msg);
            die();
        });
        register_shutdown_function(function () {
            /*
            $error = error_get_last();
            if ($error) {
                $msg = "[".$error['type']."] with message: ".$error['message']." in file: " . $error['file'] . " on line: " . $error['line'];
                if (Env::isDev()) {
                    echo $msg;
                }
                error_log("[".date("d-M-Y H:i:s")."] ".$msg);
                $logger = new Logger();
                $logger->error($msg);
            }
            */
        });
        Config::init();
        static::getContainer();
    }

    public static function getContainer()
    {
        if (!static::$container) {
            $containerBuilder = new \DI\ContainerBuilder();
            $containerBuilder->useAnnotations(false);
            $containerBuilder->useAutowiring(true);
            $containerBuilder->addDefinitions([
                \App\System\DataProvider\Mysql\DataProviderInterface::class => function() {
                    return new \App\System\DataProvider\Mysql\DataProvider(Config::get('mysql.main'));
                }
            ]);
            static::$container = $containerBuilder->build();
        }

        return static::$container;
    }
}