<?php namespace Api\Cotroller\v1;

use \Codeception\Util\HttpCode;

class AuthCest
{
    public function _before(\ApiTester $I)
    {
    }

    // tests
    public function tryToTest(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');

        $user_email = "auth_".date("YmdHis")."@host.com";
        $password = md5(date("Y-m-d H:i:s").rand(1000, 100000));

        $I->sendPOST('/api/v1/user/register', [
            'email' => $user_email,
            'password' => $password,
            'name' => "Test User"
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "SuccessAuthResponse",
            "response" => [
                "token_type" => "TEMPORARY"
            ]
        ]);

        $token = $I->grabDataFromResponseByJsonPath('response.token')[0] ?? "invalid token";

        $I->haveHttpHeader('Authorization', 'Bearer '.$token);
        $I->sendGET('/api/v1/auth/logout');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "EmptyResponse"
        ]);

        $I->haveHttpHeader('Authorization', 'Bearer '.$token);
        $I->sendGET('/api/v1/auth/logout');

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "ErrorAuthResponse",
            "response" => [
                "message" => "Unauthorized [Invalid token]",
            ]
        ]);

        $I->sendPOST('/api/v1/auth/login', [
            'email' => $user_email,
            'password' => $password
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "SuccessAuthResponse",
            "response" => [
                "token_type" => "TEMPORARY"
            ]
        ]);

        $token1 = $I->grabDataFromResponseByJsonPath('response.token')[0] ?? "invalid token";

        $I->sendPOST('/api/v1/auth/login', [
            'email' => $user_email,
            'password' => $password,
            'token_type' => 'PERMANENT'
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "SuccessAuthResponse",
            "response" => [
                "token_type" => "PERMANENT"
            ]
        ]);

        $token2 = $I->grabDataFromResponseByJsonPath('response.token')[0] ?? "invalid token";

        $I->haveHttpHeader('Authorization', 'Bearer '.$token2);
        $I->sendGET('/api/v1/auth/logout?all_devices=true');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "EmptyResponse"
        ]);

        $I->haveHttpHeader('Authorization', 'Bearer '.$token1);
        $I->sendGET('/api/v1/auth/logout');

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "ErrorAuthResponse",
            "response" => [
                "message" => "Unauthorized [Invalid token]",
            ]
        ]);
    }
}
