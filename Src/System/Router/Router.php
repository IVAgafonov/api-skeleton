<?php

namespace App\System\Router;

use App\Api\Controller\AbstractApiController;
use App\Api\Middleware\MiddlewareInterface;
use App\Api\Response\AbstractResponse;
use App\Api\Response\EmptyResponse;
use App\Api\Response\Error\ClientErrorResponse;
use App\Api\Response\Error\ServerErrorResponse;
use App\Api\Response\ResponseRootInterface;
use App\System\App\App;
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
     * @var ResponseRootInterface
     */
    protected static $response;

    public static function init()
    {
        register_shutdown_function(function () {
            http_response_code(self::$response->getResponseCode());
            echo json_encode([
                'timestamp' => (new \DateTime())->getTimestamp(),
                'response_type' => self::$response::getResponseType(),
                'response' => self::$response
            ]);
        });
        self::response(new ServerErrorResponse("Server made a boo boo"));

        session_start();
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: *");
        header("Access-Control-Allow-Headers: *");

        if (!empty($_SERVER['REQUEST_METHOD'] && in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST', 'DELETE', 'PUT', 'PATCH']))) {
            self::$method = $_SERVER['REQUEST_METHOD'];
        } else {
            if (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                self::response(new EmptyResponse());
                die();
            }
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
    protected static function response(ResponseRootInterface $response)
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
        $routeSchema = Yaml::parseFile(__DIR__."/../../../www/docs/openapi.yaml");

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
                            /** @var AbstractApiController $class */
                            $class = new $controller[0]();
                            $m = $controller[1];
                            if (method_exists($class, $controller[1])) {
                                //middleware
                                $middlewares = Config::get('app.middleware');

                                if (is_array($middlewares) && !empty($middlewares)) {
                                    foreach ($middlewares as $middleware) {
                                        $mw = new $middleware();
                                        if ($mw instanceof MiddlewareInterface) {
                                            $response = $mw($class, App::getContainer(), self::$headers, self::$params, [
                                                'allowed_groups' => $allowed_groups
                                            ]);
                                            if ($response instanceof ResponseRootInterface) {
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
                                $class->setContainer(App::getContainer());
                                return $class->$m();
                            }
                        }
                    }
                }
            }
        }
        return new ClientErrorResponse("Router", "Invalid route", 404);
    }
}
