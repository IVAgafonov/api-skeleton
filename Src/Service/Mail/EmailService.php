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

    public function getInbox(int $user_id, int $page = 1, int $count = 10, $only_important = false, string $filter = null)
    {
        $sql = "SELECT e.*, u.email as sender, r.email as recipient FROM `app_emails` e ".
            "JOIN app_users u ON e.sender_user_id = u.id ".
            "JOIN app_users r ON e.recipient_user_id = r.id ".
            "WHERE e.recipient_user_id = :user_id AND e.delete_date IS NULL AND e.type = :received ";

        $params = [
            ':user_id' => $user_id,
            ':received' => EmailType::RECEIVED,
            ':offset' => (--$page) * $count,
            ':limit' => $count
        ];

        if ($only_important) {
            $sql .= "AND is_important = :is_important ";
            $params[':is_important'] = 1;
        }

        if ($filter) {
            $sql .= "AND (lower(u.email) LIKE lower(:filter) ".
                "OR lower(r.email) LIKE lower(:filter) ".
                "OR lower(e.subject) LIKE lower(:filter) ".
                "OR lower(e.message) LIKE lower(:filter)) ";
            $params[':filter'] = '%'.$filter.'%';
        }

        $sql .= "LIMIT :offset, :limit";

        return $this->dp->getArrays($sql, $params);
    }

    public function getOutbox(int $user_id, int $page = 1, int $count = 10, string $filter = null)
    {
        $sql = "SELECT e.*, u.email as sender, r.email as recipient FROM `app_emails` e ".
            "JOIN app_users u ON e.sender_user_id = u.id ".
            "JOIN app_users r ON e.recipient_user_id = r.id ".
            "WHERE e.sender_user_id = :user_id AND e.delete_date IS NULL AND e.type = :received ";

        $params = [
            ':user_id' => $user_id,
            ':offset' => (--$page) * $count,
            ':limit' => $count,
            ':received' => EmailType::SENT
        ];

        if ($filter) {
            $sql .= "AND (lower(u.email) LIKE lower(:filter) ".
                "OR lower(r.email) LIKE lower(:filter) ".
                "OR lower(e.subject) LIKE lower(:filter) ".
                "OR lower(e.message) LIKE lower(:filter)) ";
            $params[':filter'] = '%'.$filter.'%';
        }

        $sql .= "LIMIT :offset, :limit";

        return $this->dp->getArrays($sql, $params);
    }

    public function getDeleted(int $user_id, int $page = 1, int $count = 10, $filter = null)
    {
        $sql = "SELECT e.*, u.email as sender, r.email as recipient FROM `app_emails` e ".
            "JOIN app_users u ON e.sender_user_id = u.id ".
            "JOIN app_users r ON e.recipient_user_id = r.id ".
            "WHERE (e.recipient_user_id = :user_id OR e.sender_user_id = :user_id) AND e.delete_date IS NOT NULL ";

        $params = [
            ':user_id' => $user_id,
            ':offset' => (--$page) * $count,
            ':limit' => $count,
        ];

        if ($filter) {
            $sql .= "AND (lower(u.email) LIKE lower(:filter) ".
                "OR lower(r.email) LIKE lower(:filter) ".
                "OR lower(e.subject) LIKE lower(:filter) ".
                "OR lower(e.message) LIKE lower(:filter)) ";
            $params[':filter'] = '%'.$filter.'%';
        }

        $sql .= "LIMIT :offset, :limit";

        return $this->dp->getArrays($sql, $params);
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
            $params[] = $field." = ".$this->dp->quote($value);
        }

        $this->dp->query(
            "UPDATE `app_emails` ".
            "SET ".implode(",", $params)." ".
            "WHERE id = :id",
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

    public function getInboxCount(int $user_id, string $filter = null)
    {
        $sql = "SELECT count(*) FROM `app_emails` e ".
            "JOIN app_users u ON e.sender_user_id = u.id ".
            "JOIN app_users r ON e.recipient_user_id = r.id ".
            "WHERE e.recipient_user_id = :user_id AND e.delete_date IS NULL AND type = :received ";

        $params = [
            ':user_id' => $user_id,
            ':received' => EmailType::RECEIVED
        ];

        if ($filter) {
            $sql .= "AND (lower(u.email) LIKE lower(:filter) ".
                "OR lower(r.email) LIKE lower(:filter) ".
                "OR lower(e.subject) LIKE lower(:filter) ".
                "OR lower(e.message) LIKE lower(:filter)) ";
            $params[':filter'] = '%'.$filter.'%';
        }

        return (int) $this->dp->getValue($sql, $params) ?? 0;
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

    public function getOutboxCount(int $user_id, string $filter = null)
    {
        $sql = "SELECT count(*) FROM `app_emails` e ".
            "JOIN app_users u ON e.sender_user_id = u.id ".
            "JOIN app_users r ON e.recipient_user_id = r.id ".
            "WHERE e.sender_user_id = :user_id AND e.delete_date IS NULL AND type = :sent ";

        $params = [
            ':user_id' => $user_id,
            ':sent' => EmailType::SENT
        ];

        if ($filter) {
            $sql .= "AND (lower(u.email) LIKE lower(:filter) ".
                "OR lower(r.email) LIKE lower(:filter) ".
                "OR lower(e.subject) LIKE lower(:filter) ".
                "OR lower(e.message) LIKE lower(:filter)) ";
            $params[':filter'] = '%'.$filter.'%';
        }

        return (int) $this->dp->getValue($sql, $params) ?? 0;
    }

    public function getDeletedCount(int $user_id, string $filter = null)
    {
        $sql = "SELECT count(*) FROM `app_emails` e ".
            "JOIN app_users u ON e.sender_user_id = u.id ".
            "JOIN app_users r ON e.recipient_user_id = r.id ".
            "WHERE (e.recipient_user_id = :user_id OR e.sender_user_id = :user_id) AND e.delete_date IS NOT NULL ";

        $params = [
            ':user_id' => $user_id
        ];

        if ($filter) {
            $sql .= "AND (lower(u.email) LIKE lower(:filter) ".
                "OR lower(r.email) LIKE lower(:filter) ".
                "OR lower(e.subject) LIKE lower(:filter) ".
                "OR lower(e.message) LIKE lower(:filter)) ";
            $params[':filter'] = '%'.$filter.'%';
        }

        return (int) $this->dp->getValue($sql, $params) ?? 0;
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

    public function addContact(int $user_id, string $email, string $name = '')
    {
        $this->dp->query(
            "INSERT IGNORE INTO `app_contacts` ".
            "(user_id, email, name) VALUES (:user_id, :email, :name)",
            [
                ':user_id' => $user_id,
                ':email' => $email,
                ':name' => $name
            ]
        );
    }

    public function getContacts(int $user_id, $filter = null)
    {
        $params = [
            ':user_id' => $user_id
        ];
        if ($filter) {
            $params[':filter'] = "%".$filter."%";
        }
        return $this->dp->getArrays(
            "SELECT * FROM `app_contacts` ".
            "WHERE user_id = :user_id ".
            ($filter ? "AND (lower(email) LIKE lower(:filter) OR lower(name) LIKE lower(:filter)) " : "").
            "ORDER BY `email`, `name`",
            $params
        );
    }

    public function deleteContact(int $contact_id)
    {
        $this->dp->query(
            "DELETE FROM `app_contacts` WHERE id = :id",
            [
                ':id' => $contact_id
            ]
        );
    }
}