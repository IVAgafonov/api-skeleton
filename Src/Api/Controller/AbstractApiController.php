<?php

namespace App\Api\Controller;

use App\Api\Response\AbstractResponse;

/**
 * Class AbstractController
 * @package App\Api\Controller
 */
abstract class AbstractApiController {

	/**
	 * @var string
	 */
	protected $service;

	/**
	 * @var string
	 */
	protected $method;

	/**
	 * @var array
	 */
	protected $params = [];

	/**
	 * @param string $service
	 */
	public function setService($service)
	{
		$this->service = $service;
	}

	/**
	 * @return string
	 */
	public function getService()
	{
		return $this->service;
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
}
