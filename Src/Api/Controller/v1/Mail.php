<?php

namespace App\Api\Controller\v1;

use App\Api\Controller\AbstractApiController;
use App\Api\Response\EmptyResponse;
use App\Api\Response\Error\ClientErrorResponse;
use App\Api\Response\Mail\MailItem;
use App\Api\Response\Mail\MailList;
use App\Service\Mail\MailService;
use App\Service\User\UserService;

/**
 * Class Mail
 * @package App\Api\Controller\v1
 */
class Mail extends AbstractApiController {

    /**
     * @OA\Get(path="/api/v1/mail/inbox",
     *     tags={"Mail"},
     *     summary="Inbox email list",
     *     security={{"TokenAuth":{"USER"}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="int")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response success",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="MailList"),
     *              @OA\Property(property="response", ref="#/components/schemas/MailList")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Client error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ClientErrorResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ClientErrorResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Client auth error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ErrorAuthResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ErrorAuthResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ServerErrorResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ServerErrorResponse")
     *         )
     *     )
     * )
     */
    public function inbox()
    {
        $page = $this->params['page'] ?? 1;
        $count = 5;

        $mail_list = array_map(function($m) {
            return MailItem::createFromArray($m);
        }, MailService::getMailList($this->getUser()->getEmail(), $count, $page));
        return MailList::createFromArray(['count' => count($mail_list), 'items' => $mail_list]);
    }

    /**
     * @OA\Get(path="/api/v1/mail/{mail_id}",
     *     tags={"Mail"},
     *     summary="Inbox email list",
     *     security={{"TokenAuth":{"USER"}}},
     *     @OA\Parameter(
     *         name="mail_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="int")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response success",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="MailItem"),
     *              @OA\Property(property="response", ref="#/components/schemas/MailItem")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Client error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ClientErrorResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ClientErrorResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Client auth error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ErrorAuthResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ErrorAuthResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ServerErrorResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ServerErrorResponse")
     *         )
     *     )
     * )
     */
    public function get()
    {
        $id = $this->params['mail_id'] ?? 0;

        if (!$id) {
            return new ClientErrorResponse('mail_id', 'Empty mail id');
        }

        $mail_list =  MailService::getMailList($this->getUser()->getEmail(), -1);

        foreach ($mail_list as $mail) {
            if ($mail['id'] == $id) {
                MailService::setEmailRead($id);
                return MailItem::createFromArray($mail);
            }
        }

        return new ClientErrorResponse('mail_id', 'Mail not found', 404);
    }

    /**
     * @OA\Post(path="/api/v1/mail/send",
     *     tags={"Mail"},
     *     summary="Send email",
     *     security={{"TokenAuth":{"USER"}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "subject", "text"},
     *             properties={
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="subject", type="string"),
     *                 @OA\Property(property="text", type="string"),
     *             },
     *             example={"email" = "test@host.com", "subject" = "test mail","text" = "test mail text"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response success",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="EmptyResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/EmptyResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Client error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ClientErrorResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ClientErrorResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Client auth error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ErrorAuthResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ErrorAuthResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ServerErrorResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ServerErrorResponse")
     *         )
     *     )
     * )
     */
    public function send()
    {
        $email = $this->params['email'] ?? "";
        if (!preg_match("/\w+@\w+\.\w+/i", $email)) {
            return new ClientErrorResponse('email', 'Invalid email format');
        }
        $subject = $this->params['subject'] ?? "";
        $text = $this->params['text'] ?? "";

        if (mb_strlen($subject) < 3 || mb_strlen($subject) > 50) {
            return new ClientErrorResponse('subject', 'Invalid subject');
        }
        if (mb_strlen($subject) < 3 || mb_strlen($subject) > 512) {
            return new ClientErrorResponse('text', 'Invalid text');
        }

        /** @var UserService $user_service */
        $user_service = $this->getContainer()->get(UserService::class);
        $user_to_send = $user_service->getUserByEmail($email);
        if (!$user_to_send) {
            return new ClientErrorResponse('email', 'Recipient does not exist');
        }

        MailService::addEmail($email, $this->getUser()->getEmail(), $subject, $text);

        return new EmptyResponse();
    }

    /**
     * @OA\Delete(path="/api/v1/mail/delete_mails",
     *     tags={"Mail"},
     *     summary="Delete email list",
     *     security={{"TokenAuth":{"USER"}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"ids"},
     *             properties={
     *                 @OA\Property(property="ids", type="array", @OA\Items(type="integer"))
     *             },
     *             example={"ids" = {1, 2, 3}}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response success",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="EmptyResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/EmptyResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Client error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ClientErrorResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ClientErrorResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Client auth error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ErrorAuthResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ErrorAuthResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ServerErrorResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ServerErrorResponse")
     *         )
     *     )
     * )
     */
    public function delete()
    {
        $ids = $this->params['ids'] ?? [];
        MailService::deleteMails($this->getUser()->getEmail(), $ids);
        return new EmptyResponse();
    }
}
