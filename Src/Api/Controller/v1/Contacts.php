<?php

namespace App\Api\Controller\v1;

use App\Api\Controller\AbstractApiController;
use App\Api\Response\EmptyResponse;
use App\Api\Response\Error\ClientErrorResponse;
use App\Api\Response\Mail\ContactItem;
use App\Api\Response\Mail\ContactList;
use App\Api\Response\Mail\MailItem;
use App\Api\Response\Mail\MailList;
use App\Service\Mail\EmailService;
use App\Service\Mail\MailService;
use App\Service\User\UserService;

/**
 * Class Contacts
 * @package App\Api\Controller\v1
 */
class Contacts extends AbstractApiController {

    /**
     * @OA\Get(path="/api/v1/contact/list",
     *     tags={"Contacts"},
     *     summary="Get contact list",
     *     security={{"TokenAuth":{"USER"}}},
     *     @OA\Parameter(
     *         name="filter",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response success",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ContactList"),
     *              @OA\Property(property="response", ref="#/components/schemas/ContactList")
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
    public function list()
    {
        $filter = $this->params['filter'] ?? null;

        /** @var EmailService $email_service */
        $email_service = $this->container->get(EmailService::class);

        $contacts = $email_service->getContacts($this->getUser()->getId(), $filter);

        return ContactList::createFromArray([
            'count' => count($contacts),
            'items' => array_map(function ($c) {
                return ContactItem::createFromArray($c);
            }, $contacts)
        ]);
    }

    /**
     * @OA\Delete(path="/api/v1/contact/delete/{id}",
     *     tags={"Contacts"},
     *     summary="Get contact list",
     *     security={{"TokenAuth":{"USER"}}},
     *     @OA\Parameter(
     *         name="filter",
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
        $id = (int) ($this->params['id'] ?? 0);

        /** @var EmailService $email_service */
        $email_service = $this->container->get(EmailService::class);

        $contacts = $email_service->getContacts($this->getUser()->getId());

        if (!in_array($id, array_column($contacts, 'id'))) {
            return new ClientErrorResponse('id', 'Contact not found', 404);
        }

        $email_service->deleteContact($id);

        return new EmptyResponse();
    }
}
