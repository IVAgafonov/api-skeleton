<?php

namespace App\System\Router;

use App\Api\Controller\AbstractApiController;
use App\Api\Middleware\MiddlewareInterface;
use App\Api\Response\AbstractResponse;
use App\Api\Response\Error\ClientErrorResponse;
use App\Api\Response\Error\ServerErrorResponse;
use App\Api\Response\ResponseInterface;
use App\System\Config\Config;
use App\System\Reporter\Reporter;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Router
 * @package App\System\Router
 */
class Router
{

    /**
     * @var array
     */
    protected static $headers = [];

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

    public static function init()
    {
        set_error_handler(function (int $code, string $message, string $file, int $line, $context = null) {
            $msg = $message." in file: ".$file." on line: ".$line.". Code: ".$code;
            error_log($msg);
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
            error_log($msg);
            die();
        });
        register_shutdown_function(function () {
            $error = error_get_last();

            if ($error) {
                $msg = "[".$error['type']."] with message: ".$error['message']." in file: " . $error['file'] . " on line: " . $error['line'];
                error_log("[".date("d-M-Y H:i:s")."] ".$msg);
            }

            echo json_encode([
                'timestamp' => (new \DateTime())->getTimestamp(),
                'response_type' => self::$response::getResponseType(),
                'response' => self::$response
            ]);
        });
        self::response(new ServerErrorResponse("Server made a boo boo"));

        \App\System\Config\Config::init();
        session_start();
        header("Content-Type: application/json");

        if (!empty($_SERVER['REQUEST_METHOD'] && in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST', 'DELETE', 'PUT', 'PATCH']))) {
            self::$method = $_SERVER['REQUEST_METHOD'];
        } else {
            throw new \Exception("Invalid request method", 400);
        }

        self::$headers = [];

        if (function_exists('getallheaders')) {
            self::$headers = \getallheaders();
        }

        if (empty($headers)) {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    self::$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
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
            if (preg_match("#^".$routeMask."$#i", $path)) {
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
                            $allowed_groups = $info['security'][0]['TokenAuth'] ?? [];
                            $class = new $controller[0]();
                            $m = $controller[1];
                            if (method_exists($class, $controller[1])) {
                                //middleware
                                $middlewares = Config::get('app.middleware');

                                if (is_array($middlewares) && !empty($middlewares)) {
                                    foreach ($middlewares as $middleware) {
                                        $mw = new $middleware();
                                        if ($mw instanceof MiddlewareInterface) {
                                            $response = $mw($class, self::$headers, self::$params, [
                                                'allowed_groups' => $allowed_groups
                                            ]);
                                            if ($response instanceof ResponseInterface) {
                                                return $response;
                                            }
                                        } else {
                                            throw new \Exception("Invalid middleware: ".$middleware);
                                        }
                                    }
                                }

                                //controller
                                $class->setHeaders(self::$headers);
                                $class->setParams(self::$params);
                                $class->setMethod(self::$method);
                                return $class->$m();
                            }
                        }
                    }
                }
            }
        }
        return new ClientErrorResponse("Invalid route", 404);
    }
}
