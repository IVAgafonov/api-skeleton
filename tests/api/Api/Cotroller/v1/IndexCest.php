<?php namespace Api\Cotroller\v1;

use \Codeception\Util\HttpCode;

class IndexCest
{
    public function _before(\ApiTester $I)
    {

    }

    // tests
    public function tryToTest(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET('/api/v1/index');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "ClientErrorResponse",
            "response" => [
                "field" => null,
                "message" => "Invalid route"
            ]
        ]);

        $I->sendGET('/api/v1/index/test/index');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "EmptyResponse",
            "response" => []
        ]);
    }
}
