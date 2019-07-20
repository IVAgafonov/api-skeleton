<?php

namespace App\System\Router;

use App\Api\Controller\AbstractApiController;
use App\Api\Response\AbstractResponse;
use App\Api\Response\ServerErrorResponse;
use App\Api\Response\ResponseInterface;
use App\System\Config\Config;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Router
 * @package App\System\Router
 */
class Router
{
    const ROLE_GUEST = 0;
    const ROLE_USER = 1;

    /**
     * @var string
     */
    protected static $controller = 'index';

    /**
     * @var string
     */
    protected static $action = 'index';

    /**
     * @var string
     */
    protected static $version = 'v1';

    /**
     * @var int
     */
    protected static $role = Router::ROLE_GUEST;

    /**
     * @var array
     */
    protected static $params = [];

    /**
     * @var string
     */
    protected static $service = 'unknown';

    /**
     * @var string
     */
    protected static $method = 'GET';

    /**
     * @var ResponseInterface
     */
    protected static $response;

    /**
     * @var ResponseInterface
     */
    protected static $response_code = 200;

    public static function init()
    {
        self::response(new ServerErrorResponse("Server made a boo boo"));
        set_error_handler(function (int $code, string $message, string $file, int $line, $context = null) {
            $msg = $message." in file: ".$file." on line: ".$line.". Code: ".$code;
            self::$response_code = 500;
            error_log($msg);
            die();
        });
        set_exception_handler(function (\Throwable $e) {
            if ($e->getCode() > 300 && $e->getCode() < 600) {
                self::$response_code = $e->getCode();
            }
            $msg = "Thrown exception [".get_class($e)."] with message: ".$e->getMessage()." in file: ".$e->getFile()." on line :".$e->getLine();
            $trace = $e->getTrace();

            if ($trace && is_array($trace)) {
                $msg .= "\tTrace:";
                foreach ($trace as $i => $t) {
                    $msg .= "[".$i." => ".$t['file'].": ".$t['line']."]";
                }
            }
            error_log($msg);
            die();
        });
        register_shutdown_function(function () {
            http_response_code(self::$response_code);
            echo json_encode([
                'timestamp' => (new \DateTime())->getTimestamp(),
                'response_type' => self::$response::getResponseType(),
                'data' => self::$response
            ]);
        });

        \App\System\Config\Config::init();
        session_start();
        header("Content-Type: application/json");

        if (!empty($_SERVER['REQUEST_METHOD'] && in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST', 'DELETE', 'PUT', 'PATCH']))) {
            self::$method = $_SERVER['REQUEST_METHOD'];
        } else {
            throw new \Exception("Invalid request method", 400);
        }

        if (function_exists('getallheaders')) {
            $headers = \getallheaders();
        } else {
            //todo tmpc
            $headers = ['Authorization' => 'test'];
        }

        $token = '';
        if (!empty($headers['Authorization']) || !empty($headers['authorization'])) {
            $token = !empty($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
            $token = trim($token, "bearer");
            $token = trim($token, "Bearer");
            $token = trim($token);
        }

        $auth = Config::get('auth.tokens');
        if (!empty($auth[$token])) {
            self::$service = $auth[$token];
            self::$role = Router::ROLE_USER;
        } else {
            self::$role = Router::ROLE_GUEST;
        }

        self::dispatch();
    }

    /**
     * @param $response
     * @throws \Exception
     */
    protected static function response(ResponseInterface $response)
    {
        self::$response = $response;
    }

    /**
     * @throws \Exception
     */
    protected static function dispatch()
    {
        $path = [];
        if (!empty($_SERVER['REQUEST_URI'])) {
            $path = $_SERVER['REQUEST_URI'];
        }
        if (empty($path) && !empty($_SERVER['PATH_INFO'])) {
            $path = $_SERVER['PATH_INFO'];
        }
        if (empty($path) && !empty($_SERVER['SCRIPT_URL'])) {
            $path = $_SERVER['SCRIPT_URL'];
        }

        $path = explode("?", $path);
        $path = $path[0];

        $path = trim($path, "/");

        $response = self::initController($path, self::$method);
        self::response($response);
    }

    protected static function parseParams()
    {
        switch (self::$method) {
            case 'GET':
                self::$params = array_merge(self::$params, $_GET);
                break;
            case 'POST':
                if (empty($_POST)) {
                    self::$params = array_merge(self::$params, (array)json_decode(trim(file_get_contents('php://input')), true, JSON_UNESCAPED_UNICODE));
                } else {
                    self::$params = array_merge(self::$params, $_POST);
                }
                break;
            default:
                self::$params = array_merge(self::$params, (array)json_decode(trim(file_get_contents('php://input')), true, JSON_UNESCAPED_UNICODE));
                self::$params = array_merge(self::$params, $_GET);
                self::$params = array_merge(self::$params, $_POST);
        }

        return self::$params;
    }

    /**
     * @param string $path
     * @param string $method
     * @return null
     * @throws \Exception
     */
    protected static function initController(string $path, string $method)
    {
        $routeSchema = Yaml::parseFile(__DIR__."/../../../docs/openapi.yaml");

        if (empty($routeSchema['paths']) || !is_array($routeSchema['paths'])) {
            throw new \Exception("Invalid route list", 500);
        }

        self::parseParams();

        foreach ($routeSchema['paths'] as $route => $routeInfo) {
            $route = trim($route, "/");
            $routeMask = preg_replace("/{\w+}/", "[^/]+", $route);

            $pathItems = explode("/", $path);
            if (preg_match("#^".$routeMask."$#i", $route)) {
                foreach (explode("/", $route) as $index => $item) {
                    if (preg_match("/^{\w+}$/i", $item)) {
                        self::$params[trim($item, "{}")] = urldecode($pathItems[$index] ?? null);
                    }
                }

                foreach ($routeInfo as $routeMethod => $info) {
                    if (mb_strtolower($method) === mb_strtolower($routeMethod)) {
                        $controller = explode("::", $info["operationId"]);
                        if (empty($controller[0]) || empty($controller[1])) {
                            throw new \Exception("Invalid operation", 500);
                        }
                        if (class_exists($controller[0])) {
                            $class = new $controller[0]();
                            $m = $controller[1];
                            if (method_exists($class, $controller[1])) {
                                return $class->$m();
                            }
                        }
                    }
                }
            }
        }
        return null;
    }
}
