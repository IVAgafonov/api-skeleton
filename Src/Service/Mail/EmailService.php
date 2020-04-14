<?php

namespace App\Service\Mail;

use App\Entity\AbstractEnum;
use App\Entity\AbstractSet;
use App\Entity\Email\EmailType;
use App\Entity\User\User;
use App\Entity\User\UserInterface;
use App\System\DataProvider\Mysql\DataProviderInterface;

class EmailService {

    /**
     * @var DataProviderInterface
     */
    protected $dp;

    public function __construct(DataProviderInterface $dp)
    {
        $this->dp = $dp;
    }

    /**
     * @param int $sender_id
     * @param int $recipient_id
     * @param string $subject
     * @param string $message
     * @return int|null
     */
    public function createEmail(int $sender_id, int $recipient_id, string $subject, string $message): ?int
    {
        $this->dp->query(
            "INSERT INTO `app_emails` (`sender_user_id`, `recipient_user_id`, `subject`, `message`, `type`) ".
            "VALUES (:sender_id, :recipient_id, :subject, :message, :sent), ".
            "(:sender_id, :recipient_id, :subject, :message, :received)",
            [
                ':sender_id' => $sender_id,
                ':recipient_id' => $recipient_id,
                ':subject' => $subject,
                ':message' => $message,
                ':sent' => EmailType::SENT,
                ':received' => EmailType::RECEIVED
            ]
        );
        $email_id = $this->dp->getLastInsertId();
        if ($email_id) {
            return $email_id;
        }
        return null;
    }

    public function getInbox(int $user_id, int $page = 1, int $count = 10, $only_important = false)
    {
        if ($only_important) {
            return $this->dp->getArrays(
                "SELECT e.*, u.email as sender, r.email as recipient  FROM `app_emails` e ".
                "JOIN app_users u ON e.sender_user_id = u.id ".
                "JOIN app_users r ON e.recipient_user_id = r.id ".
                "WHERE e.recipient_user_id = :user_id AND e.delete_date IS NULL AND e.type = :received ".
                "AND is_important = :is_important ".
                "LIMIT :offset :limit",
                [
                    ':user_id' => $user_id,
                    ':offset' => (--$page) * $count,
                    ':limit' => $count,
                    ':received' => EmailType::RECEIVED,
                    ':is_important' => 1
                ]
            );
        }
        return $this->dp->getArrays(
            "SELECT e.*, u.email as sender, r.email as recipient FROM `app_emails` e ".
            "JOIN app_users u ON e.sender_user_id = u.id ".
            "JOIN app_users r ON e.recipient_user_id = r.id ".
            "WHERE e.recipient_user_id = :user_id AND e.delete_date IS NULL AND e.type = :received ".
            "LIMIT :offset, :limit",
            [
                ':user_id' => $user_id,
                ':offset' => (--$page) * $count,
                ':limit' => $count,
                ':received' => EmailType::RECEIVED
            ]
        );
    }

    public function getOutbox(int $user_id, int $page = 1, int $count = 10)
    {
        return $this->dp->getArrays(
            "SELECT e.*, u.email as sender, r.email as recipient FROM `app_emails` e ".
            "JOIN app_users u ON e.sender_user_id = u.id ".
            "JOIN app_users r ON e.recipient_user_id = r.id ".
            "WHERE e.sender_user_id = :user_id AND e.delete_date IS NULL AND e.type = :received ".
            "LIMIT :offset, :limit",
            [
                ':user_id' => $user_id,
                ':offset' => (--$page) * $count,
                ':limit' => $count,
                ':received' => EmailType::SENT
            ]
        );
    }

    public function getDeleted(int $user_id, int $page = 1, int $count = 10)
    {
        return $this->dp->getArrays(
            "SELECT e.*, u.email as sender, r.email as recipient FROM `app_emails` e ".
            "JOIN app_users u ON e.sender_user_id = u.id ".
            "JOIN app_users r ON e.recipient_user_id = r.id ".
            "WHERE (e.recipient_user_id = :user_id OR e.sender_user_id = :user_id) AND e.delete_date IS NOT NULL ".
            "LIMIT :offset, :limit",
            [
                ':user_id' => $user_id,
                ':offset' => (--$page) * $count,
                ':limit' => $count,
            ]
        );
    }

    public function getEmailById(int $email_id)
    {
        return $this->dp->getArray(
            "SELECT e.*, u.email as sender, r.email as recipient FROM `app_emails` e ".
            "JOIN app_users u ON e.sender_user_id = u.id ".
            "JOIN app_users r ON e.recipient_user_id = r.id ".
            "WHERE e.delete_date IS NULL AND e.id = :id",
            [
                ':id' => $email_id
            ]
        );
    }

    public function saveEmail(array $email)
    {
        if (empty($email['id'])) {
            throw new \Exception("Empty id");
        }
        $id = $email['id'];
        unset($email['id']);

        if (empty($email)) {
            throw new \Exception("Empty fields");
        }

        $params = [];

        foreach ($email as $field => $value) {
            $params = $field." = ".$this->dp->quote($value);
        }

        $this->dp->query(
            "UPDATE `app_emails` ".
            "SET ".implode($params)." ".
            "WHERE id = :$id",
            [
                ':id' => $id
            ]
        );

        return $this->dp->getAffectedRows();
    }


    public function checkEmailsByIds(array $ids, int $user_id)
    {
        if (empty($ids)) {
            throw new \Exception("Empty ids");
        }
        return $this->dp->getArrays(
            "SELECT e.id FROM `app_emails` e ".
            "WHERE (e.recipient_user_id = :user_id OR e.sender_user_id = :user_id) AND e.id IN (".implode(",", $ids).") ",
            [
                ':user_id' => $user_id
            ]
        );
    }

    public function getInboxCount(int $user_id)
    {
        return (int) $this->dp->getValue(
            "SELECT count(*) FROM `app_emails` e ".
            "WHERE e.recipient_user_id = :user_id AND e.delete_date IS NULL AND type = :received",
            [
                ':user_id' => $user_id,
                ':received' => EmailType::RECEIVED
            ]
        ) ?? 0;
    }

    public function getInboxUnreadCount(int $user_id)
    {
        return (int) $this->dp->getValue(
            "SELECT count(*) FROM `app_emails` e ".
            "WHERE e.recipient_user_id = :user_id AND e.delete_date IS NULL AND is_opened = 0 AND type = :received",
            [
                ':user_id' => $user_id,
                ':received' => EmailType::RECEIVED
            ]
        ) ?? 0;
    }

    public function getOutboxCount(int $user_id)
    {
        return (int) $this->dp->getValue(
            "SELECT count(*) FROM `app_emails` e ".
            "WHERE e.sender_user_id = :user_id AND e.delete_date IS NULL AND type = :sent",
            [
                ':user_id' => $user_id,
                ':sent' => EmailType::SENT
            ]
        ) ?? 0;
    }

    public function getDeletedCount(int $user_id)
    {
        return (int) $this->dp->getValue(
            "SELECT count(*) FROM `app_emails` e ".
            "WHERE (e.recipient_user_id = :user_id OR e.sender_user_id = :user_id) AND e.delete_date IS NOT NULL",
            [
                ':user_id' => $user_id,
            ]
        ) ?? 0;
    }

    public function deleteByIds(array $ids) {
        $this->dp->query(
            "UPDATE `app_emails` ".
            "SET delete_date = :delete_date ".
            "WHERE id IN (".implode(",", $ids).")",
            [
                ':delete_date' => date("Y-m-d H:i:s")
            ]
        );
    }
}