<?php

namespace App\Service\Mail;


class MailService {

    static $mails = null;

    public static function initMailList()
    {
        if (!static::$mails) {
            if (!file_exists("/tmp/mails")) {
                $mails['max_id'] = 0;
                file_put_contents("/tmp/mails", json_encode($mails));
            }
            static::$mails = json_decode(file_get_contents("/tmp/mails"), true);
        }
        return static::$mails;
    }

    public static function getMailList($user_mail, int $count, int $page = 1, bool $deleted = false)
    {
        static::initMailList();
        if (!isset(static::$mails[$user_mail])) {
            return [];
        }

        if ($count == -1) {
            $list = static::$mails[$user_mail];
        } else {
            $list = array_slice(static::$mails[$user_mail], ($page - 1) * $count, $count);
        }

        $result = [];
        foreach ($list as &$l) {
            if (mb_strlen($l['subject']) > 30) {
                if ($count != -1) {
                    $l['subject'] = trim(mb_substr($l['subject'], 0, 30))."...";
                }
            }
            if (mb_strlen($l['text']) > 50) {
                if ($count != -1) {
                    $l['text'] = trim(mb_substr($l['text'], 0, 50))."...";
                }
            }
            if ($deleted) {
                if ($l['deleted']) {
                    $result[] = $l;
                }
            } else {
                if (!$l['deleted']) {
                    $result[] = $l;
                }
            }
        }
        return $result;
    }

    public static function setEmailRead(int $id)
    {
        static::initMailList();

        foreach (static::$mails as &$mail_list) {
            if (!is_array($mail_list)) {
                continue;
            }
            foreach ($mail_list as &$m) {
                if ($m['id'] == $id) {
                    $m['read'] = true;
                    file_put_contents("/tmp/mails", json_encode(static::$mails));
                    return true;
                }
            }
        }
        return false;
    }

    public static function deleteMails($user_email, array $ids)
    {
        self::initMailList();
        if (!isset(static::$mails[$user_email])) {
            return [];
        }
        foreach (static::$mails[$user_email] as $k => $mail) {
            if (in_array($mail['id'], $ids)) {
                static::$mails[$user_email][$k]['deleted'] = true;
            }
        }
        file_put_contents("/tmp/mails", json_encode(static::$mails));
    }

    public static function addEmail($email, $from, $subject, $text)
    {
        self::initMailList();
        static::$mails['max_id']++;
        if (!isset(static::$mails[$email])) {
            static::$mails[$email] = [];
        }
        static::$mails[$email][] = [
            'id' => static::$mails['max_id'],
            'subject' => $subject,
            'sender' => $from,
            'text' => $text,
            'read' => false,
            'deleted' => false,
            'date' => date("Y-m-d H:i:s")
        ];

        file_put_contents("/tmp/mails", json_encode(static::$mails));
    }
}