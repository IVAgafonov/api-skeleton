<?php

namespace App\Api\Controller\v1;

use App\Api\Controller\AbstractApiController;
use App\Api\Response\EmptyResponse;

/**
 * Class Index
 * @package App\Api\Controller\v1
 */
class Index extends AbstractApiController {
    /**
     * @OA\OpenApi({
     *     @OA\Info(
     *         title="App",
     *         version="0000.00.00",
     *         description="Api docs"
     *     )
     * }, security={{"TokenAuth":{}}}))
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
     * @OA\Get(path="/api/v1/index/{index}/index",
     *     tags={"index"},
     *     summary="Test methods",
     *     security={},
     *     @OA\Parameter(
     *         name="index",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Html response success (with js code in script tag)"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *     )
     * )
     */
    public function index()
    {
        return new EmptyResponse();
    }
}
