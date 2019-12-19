<?php

namespace App\Api\Controller;

use App\Entity\User\User;
use DI\Container;

/**
 * Class AbstractController
 * @package App\Api\Controller
 */
abstract class AbstractApiController {

    /**
     * @OA\OpenApi({
     *     @OA\Info(
     *         title="App",
     *         version="0000.00.00",
     *         description="Api docs"
     *     ),
     *     @OA\Tag(
     *         name="Auth",
     *         description="Auth methods"
     *     ),
     *     @OA\Tag(
     *         name="User",
     *         description="User methods"
     *     ),
     * }, security={{"TokenAuth":{}}})
     *
     * @OA\Server(
     *     description="Current server",
     *     url="/"
     * )
     *
     * @OA\SecurityScheme(
     *     type="http",
     *     scheme="bearer",
     *     securityScheme="TokenAuth"
     * )
     */


    /**
     * @var User|null
     */
    protected $user = null;

	/**
	 * @var string
	 */
	protected $method = 'GET';

    /**
     * @var array
     */
    protected $headers = [];

	/**
	 * @var array
	 */
	protected $params = [];

    /**
     * @var Container
     */
    protected $container = null;

    /**
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

	/**
	 * @param string $method
	 */
	public function setMethod($method)
	{
		$this->method = $method;
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Set uri params
	 * @param array $params
	 */
	public function setParams(array $params) {
		$this->params = $params;
	}

	/**
	 * Get uri params
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * Set headers
	 * @param array $headers
	 */
	public function setHeaders(array $headers) {
		$this->headers = $headers;
	}

	/**
	 * Get headers
	 * @return array
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * Set User
	 * @param User $user
	 */
	public function setUser(User $user) {
		$this->user = $user;
	}

	/**
	 * Get User
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}
}
