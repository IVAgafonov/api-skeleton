<?php

namespace App\Api\Controller\v1;

use App\Api\Controller\AbstractApiController;
use App\Api\Response\EmptyResponse;
use App\Api\Response\Error\ClientErrorResponse;
use App\Api\Response\Mail\MailItem;
use App\Api\Response\Mail\MailList;
use App\Service\Mail\EmailService;
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
     *     @OA\Parameter(
     *         name="count",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="int")
     *     ),
     *     @OA\Parameter(
     *         name="only_important",
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
        $page = (int) ($this->params['page'] ?? 1);
        $count = (int) ($this->params['count'] ?? 10);
        $only_important = (bool) ($this->params['only_important'] ?? false);

        /** @var EmailService $email_service */
        $email_service = $this->container->get(EmailService::class);

        $email_list_array = array_map(
            function ($e) {
                $e['message'] = mb_substr(str_replace("\n", " ", $e['message']), 0, 128);
                return $e;
            },
            $email_service->getInbox($this->getUser()->getId(), $page, $count, $only_important)
        );

        $mail_list = array_map(function($m) {
            return MailItem::createFromArray($m);
        }, $email_list_array);
        return MailList::createFromArray([
            'count_inbox' => $email_service->getInboxCount($this->getUser()->getId()) ,
            'count_outbox' => $email_service->getOutboxCount($this->getUser()->getId()) ,
            'count_deleted' => $email_service->getDeletedCount($this->getUser()->getId()),
            'count_unread' => $email_service->getInboxUnreadCount($this->getUser()->getId()),
            'items' => $mail_list
        ]);
    }

    /**
     * @OA\Get(path="/api/v1/mail/ountbox",
     *     tags={"Mail"},
     *     summary="Outbox email list",
     *     security={{"TokenAuth":{"USER"}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="int")
     *     ),
     *     @OA\Parameter(
     *         name="count",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="int")
     *     ),
     *     @OA\Parameter(
     *         name="only_important",
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
    public function outbox()
    {
        $page = (int) ($this->params['page'] ?? 1);
        $count = (int) ($this->params['count'] ?? 10);

        /** @var EmailService $email_service */
        $email_service = $this->container->get(EmailService::class);

        $email_list_array = array_map(
            function ($e) {
                $e['message'] = mb_substr(str_replace("\n", " ", $e['message']), 0, 128);
                return $e;
            },
            $email_service->getOutbox($this->getUser()->getId(), $page, $count)
        );

        $mail_list = array_map(function($m) {
            return MailItem::createFromArray($m);
        }, $email_list_array);
        return MailList::createFromArray([
            'count_inbox' => $email_service->getInboxCount($this->getUser()->getId()) ,
            'count_outbox' => $email_service->getOutboxCount($this->getUser()->getId()) ,
            'count_deleted' => $email_service->getDeletedCount($this->getUser()->getId()),
            'count_unread' => $email_service->getInboxUnreadCount($this->getUser()->getId()),
            'items' => $mail_list
        ]);
    }

    /**
     * @OA\Get(path="/api/v1/mail/deleted",
     *     tags={"Mail"},
     *     summary="Deleted email list",
     *     security={{"TokenAuth":{"USER"}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="int")
     *     ),
     *     @OA\Parameter(
     *         name="count",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="int")
     *     ),
     *     @OA\Parameter(
     *         name="only_important",
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
    public function deleted()
    {
        $page = (int) ($this->params['page'] ?? 1);
        $count = (int) ($this->params['count'] ?? 10);

        /** @var EmailService $email_service */
        $email_service = $this->container->get(EmailService::class);

        $email_list_array = array_map(
            function ($e) {
                $e['message'] = mb_substr(str_replace("\n", " ", $e['message']), 0, 128);
                return $e;
            },
            $email_service->getDeleted($this->getUser()->getId(), $page, $count)
        );

        $mail_list = array_map(function($m) {
            return MailItem::createFromArray($m);
        }, $email_list_array);
        return MailList::createFromArray([
            'count_inbox' => $email_service->getInboxCount($this->getUser()->getId()) ,
            'count_outbox' => $email_service->getOutboxCount($this->getUser()->getId()) ,
            'count_deleted' => $email_service->getDeletedCount($this->getUser()->getId()),
            'count_unread' => $email_service->getInboxUnreadCount($this->getUser()->getId()),
            'items' => $mail_list
        ]);
    }

    /**
     * @OA\Get(path="/api/v1/mail/{mail_id}",
     *     tags={"Mail"},
     *     summary="Inbox email list",
     *     security={{"TokenAuth":{"USER"}}},
     *     @OA\Parameter(
     *         description="Email id",
     *         in="path",
     *         name="mail_id",
     *         required=true,
     *         @OA\Schema(
     *           type="integer"
     *         )
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
        $email_id = $this->params['mail_id'] ?? 0;

        if (!$email_id) {
            return new ClientErrorResponse('mail_id', 'Empty mail id');
        }

        $email_service = $this->container->get(EmailService::class);

        if (!$email_service->checkEmailsByIds([$email_id], $this->getUser()->getId())) {
            return new ClientErrorResponse('email_id', 'Email not found');
        }

        $email = $email_service->getEmailById($email_id);

        if (!$email) {
            return new ClientErrorResponse('mail_id', 'Mail not found', 404);
        }

        return MailItem::createFromArray($email);
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
     *                 @OA\Property(property="message", type="string"),
     *             },
     *             example={"email" = "test@host.com", "subject" = "test mail", "message" = "test mail text"}
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
        $message = $this->params['message'] ?? "";

        if (mb_strlen($subject) < 3 || mb_strlen($subject) > 50) {
            return new ClientErrorResponse('subject', 'Invalid subject');
        }
        if (mb_strlen($message) < 3 || mb_strlen($message) > 512) {
            return new ClientErrorResponse('message', 'Invalid message');
        }

        /** @var UserService $user_service */
        $user_service = $this->getContainer()->get(UserService::class);
        $user_to_send = $user_service->getUserByEmail($email);
        if (!$user_to_send) {
            return new ClientErrorResponse('email', 'Recipient does not exist');
        }

        /** @var EmailService $email_service */
        $email_service = $this->getContainer()->get(EmailService::class);
        $email_service->createEmail($this->getUser()->getId(), $user_to_send->getId(), $subject, $message);

        return new EmptyResponse();
    }


    /**
     * @OA\Post(path="/api/v1/mail/important",
     *     tags={"Mail"},
     *     summary="Set/unset email as important",
     *     security={{"TokenAuth":{"USER"}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email_id", "important"},
     *             properties={
     *                 @OA\Property(property="email_id", type="int"),
     *                 @OA\Property(property="important", type="int"),
     *             },
     *             example={"email_id" = 1, "important" = 1}
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
    public function important()
    {
        $email_id = (int) ($this->params['email_id'] ?? 0);
        if (!$email_id) {
            return new ClientErrorResponse('email_id', 'Empty email id');
        }
        $is_important = (int) ($this->params['important'] ?? 0);

        /** @var EmailService $email_service */
        $email_service = $this->getContainer()->get(EmailService::class);

        if (!$email_service->checkEmailsByIds([$email_id], $this->getUser()->getId())) {
            return new ClientErrorResponse('email_id', 'Email not found');
        }

        $email = $email_service->getEmailById($email_id);
        $email['is_important'] = $is_important;
        $email_service->saveEmail($email);

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

        $ids = array_map(function ($id) { return (int) $id; }, $ids);

        /** @var EmailService $email_service */
        $email_service = $this->container->get(EmailService::class);

        if (count($ids) !== count($email_service->checkEmailsByIds($ids, $this->getUser()->getId()))) {
            return new ClientErrorResponse('email_id', 'Email not found');
        }

        $email_service->deleteByIds($ids);

        return new EmptyResponse();
    }
}
