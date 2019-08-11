<?php namespace Api\Cotroller\v1;

use \Codeception\Util\HttpCode;

class UserCest
{
    public function _before(\ApiTester $I)
    {
    }

    // tests
    public function tryToTest(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');

        $user_email = "test_".date("YmdHis")."@host.com";
        $password = md5(date("Y-m-d H:i:s").rand(1000, 100000));

        $I->sendPOST('/api/v1/user/register', [
            'email' => 'invalid email',
            'password' => $password,
            'name' => "Test User"
        ]);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "ClientErrorResponse",
            "response" => [
                "field" => "email",
                "message" => "Invalid email format"
            ]
        ]);

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

        $I->sendPOST('/api/v1/user/register', [
            'email' => $user_email,
            'password' => $password,
            'name' => "Test User"
        ]);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "ClientErrorResponse",
            "response" => [
                "field" => "email",
                "message" => "User with this email already exists"
            ]
        ]);

        $I->haveHttpHeader('Authorization', 'Bearer '.$token."_invalid");
        $I->sendGET('/api/v1/user');

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "ErrorAuthResponse",
            "response" => [
                "message" => "Unauthorized [Invalid token]",
            ]
        ]);

        $I->haveHttpHeader('Authorization', 'Bearer '.$token);

        $I->sendPOST('/api/v1/user/update', [
            'name' => "Test User Updated"
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "UserResponse",
            "response" => [
                "email" => $user_email,
                "name" => "Test User Updated",
                "groups" => [
                    "USER"
                ]
            ]
        ]);

        $I->sendGET('/api/v1/user');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "UserResponse",
            "response" => [
                "email" => $user_email,
                "name" => "Test User Updated",
                "groups" => [
                    "USER"
                ]
            ]
        ]);
    }
}
