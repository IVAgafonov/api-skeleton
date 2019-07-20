<?php

namespace App\System\Config;

/**
 * Class Config
 * @package App\System\Config
 */
class Config {

    /**
     * @var array
     */
    private static $config = [];

    /**
     * Get param from config
     *
     * @param string $paramName Example: auth.tokens
     * @return mixed|null
     */
    public static function get(string $paramName)
    {
        $paramPath = explode(".", $paramName);
        $param = self::$config;
        foreach ($paramPath as $path) {
            if (isset($param[$path])) {
                $param = $param[$path];
            } else {
                return null;
            }
        }
        return $param;
    }

    /**
     *
     */
    public static function init()
    {
        $configPaths = [
            __DIR__ . "/../../../configs/Global",
            __DIR__ . "/../../../configs/Local",
        ];

        foreach ($configPaths as $path) {
            $configFiles = self::getConfigFiles($path);
            foreach ($configFiles as $file) {
                self::$config = array_merge(
                    self::$config,
                    [
                        trim(
                            mb_strtolower(
                                basename($file, ".php")
                            ), "."
                        ) => require_once $file
                    ]
                );
            }
        }
    }

    /**
     * @param string $dir
     * @return array
     */
    private static function getConfigFiles(string $dir)
    {
        $files = [];
        $paths = scandir($dir);

        foreach ($paths as $path) {
            if (!is_dir($dir.DIRECTORY_SEPARATOR.$path)) {
                $files[] = $dir.DIRECTORY_SEPARATOR.$path;
            } else if ($path !== "." && $path !== ".."){
                $files = array_merge($files, self::getConfigFiles($dir.DIRECTORY_SEPARATOR.$path));
            }
        }
        return $files;
    }
}