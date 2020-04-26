<?php namespace Api\Cotroller\v1;

use App\System\App\App;
use App\System\DataProvider\Mysql\DataProvider;
use \Codeception\Util\HttpCode;

class MailCest
{
    public function _before(\ApiTester $I)
    {
    }

    // tests
    public function createEmailTest(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');

        $sender = "test_sender_".date("YmdHis")."@host.com";
        $recipient = "test_recipient_".date("YmdHis")."@host.com";
        $password = md5(date("Y-m-d H:i:s").rand(1000, 100000));

        $I->sendPOST('/api/v1/user/register', [
            'email' => $sender,
            'password' => $password,
            'name' => "Test Sender"
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "SuccessAuthResponse",
            "response" => [
                "token_type" => "TEMPORARY"
            ]
        ]);

        $token_sender = $I->grabDataFromResponseByJsonPath('response.token')[0] ?? "invalid token";

        $I->sendPOST('/api/v1/user/register', [
            'email' => $recipient,
            'password' => $password,
            'name' => "Test Recipient"
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "SuccessAuthResponse",
            "response" => [
                "token_type" => "TEMPORARY"
            ]
        ]);

        $token_recipient = $I->grabDataFromResponseByJsonPath('response.token')[0] ?? "invalid token";

        $I->haveHttpHeader('Authorization', 'Bearer '.$token_sender);

        $I->sendGET('/api/v1/mail/inbox');
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "MailList",
            "response" => [
                "items" => [
                    [
                        "subject" => "Thanks for registration",
                        "message" => "Hello! Your registration successfully completed! Please, delete this message after reading.",
                        "sender" => "admin@agafonov.me",
                        "recipient" => $sender,
                        "is_opened" => false,
                        "is_important" => false
                    ]
                ]
            ]
        ]);

        $email_id = $I->grabDataFromResponseByJsonPath('response.items.0.id')[0] ?? 0;

        $I->sendGET('/api/v1/mail/'.$email_id);

        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "MailItem",
            "response" => [
                "id" => $email_id,
                "subject" => "Thanks for registration",
                "message" => "Hello! Your registration successfully completed! Please, delete this message after reading.",
                "sender" => "admin@agafonov.me",
                "recipient" => $sender,
                "is_opened" => true,
                "is_important" => false
            ]
        ]);

        $I->sendPOST('/api/v1/mail/important', [
            'email_id' => $email_id,
            'important' => 1
        ]);

        $I->sendGET('/api/v1/mail/inbox');
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "MailList",
            "response" => [
                "items" => [
                    [
                        "subject" => "Thanks for registration",
                        "message" => "Hello! Your registration successfully completed! Please, delete this message after reading.",
                        "sender" => "admin@agafonov.me",
                        "recipient" => $sender,
                        "is_opened" => true,
                        "is_important" => true
                    ]
                ]
            ]
        ]);


        $I->sendGET('/api/v1/mail/inbox?only_important=1');
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "MailList",
            "response" => [
                "items" => [
                    [
                        "subject" => "Thanks for registration",
                        "message" => "Hello! Your registration successfully completed! Please, delete this message after reading.",
                        "sender" => "admin@agafonov.me",
                        "recipient" => $sender,
                        "is_opened" => true,
                        "is_important" => true
                    ]
                ]
            ]
        ]);

        $I->sendPOST('/api/v1/mail/important', [
            'email_id' => $email_id,
            'important' => 0
        ]);

        $I->sendGET('/api/v1/mail/inbox');
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "MailList",
            "response" => [
                "items" => [
                    [
                        "subject" => "Thanks for registration",
                        "message" => "Hello! Your registration successfully completed! Please, delete this message after reading.",
                        "sender" => "admin@agafonov.me",
                        "recipient" => $sender,
                        "is_opened" => true,
                        "is_important" => false
                    ]
                ]
            ]
        ]);

        $I->sendPOST('/api/v1/mail/send', [
            'email' => $recipient,
            'subject' => 'test subject',
            'message' => 'test message'
        ]);

        $I->haveHttpHeader('Authorization', 'Bearer '.$token_recipient);

        $I->sendGET('/api/v1/mail/inbox');
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "MailList",
            "response" => [
                "items" => [
                    [
                        "subject" => "Thanks for registration",
                        "message" => "Hello! Your registration successfully completed! Please, delete this message after reading.",
                        "sender" => "admin@agafonov.me",
                        "recipient" => $recipient,
                        "is_opened" => false,
                        "is_important" => false
                    ],
                    [
                        "subject" => "test subject",
                        "message" => "test message",
                        "sender" => $sender,
                        "recipient" => $recipient,
                        "is_opened" => false,
                        "is_important" => false
                    ]
                ]
            ]
        ]);

        $I->sendGET('/api/v1/mail/inbox?filter=subject');
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "MailList",
            "response" => [
                "items" => [
                    [
                        "subject" => "test subject",
                        "message" => "test message",
                        "sender" => $sender,
                        "recipient" => $recipient,
                        "is_opened" => false,
                        "is_important" => false
                    ]
                ]
            ]
        ]);

        $I->sendGET('/api/v1/mail/inbox?filter=message');
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "MailList",
            "response" => [
                "items" => [
                    [
                        "subject" => "test subject",
                        "message" => "test message",
                        "sender" => $sender,
                        "recipient" => $recipient,
                        "is_opened" => false,
                        "is_important" => false
                    ]
                ]
            ]
        ]);

        $I->sendGET('/api/v1/mail/inbox?filter=test_sender');
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "MailList",
            "response" => [
                "items" => [
                    [
                        "subject" => "test subject",
                        "message" => "test message",
                        "sender" => $sender,
                        "recipient" => $recipient,
                        "is_opened" => false,
                        "is_important" => false
                    ]
                ]
            ]
        ]);

        $I->sendGET('/api/v1/mail/inbox?filter=test_recipient');
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "MailList",
            "response" => [
                "items" => [
                    [
                        "subject" => "test subject",
                        "message" => "test message",
                        "sender" => $sender,
                        "recipient" => $recipient,
                        "is_opened" => false,
                        "is_important" => false
                    ]
                ]
            ]
        ]);

        $I->sendGET('/api/v1/mail/inbox?filter=message');
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "response_type" => "MailList",
            "response" => [
                "items" => [
                    [
                        "subject" => "test subject",
                        "message" => "test message",
                        "sender" => $sender,
                        "recipient" => $recipient,
                        "is_opened" => false,
                        "is_important" => false
                    ]
                ]
            ]
        ]);
    }
}
