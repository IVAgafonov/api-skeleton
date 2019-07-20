<?php

namespace App\System\Reporter;

use App\System\Config\Config;

/**
 * Class Reporter
 * @package App\System\Reporter
 */
class Reporter {

    /**
     * @var array|null
     */
    private static $config = null;

    /**
     * @var array
     */
	protected static $default_curl_options = [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_VERBOSE => false,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
		CURLOPT_CONNECTTIMEOUT => 2,
		CURLOPT_TIMEOUT => 4,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_HEADER => true,
	];

    /**
     * @param array $recipients
     * @param string $message
     * @param string $bot
     * @throws \Exception
     */
	public static function send(array $recipients, string $message, string $bot) {
	    if (!self::$config) {
	        self::initConfig();
        }
		if (is_array($message)) {
			$message = json_encode($message);
		}
		if (!is_string($message)) {
            throw new \Exception('Reporter: Send invalid message');
		}
		if (!is_string($bot)) {
            throw new \Exception('Reporter: Invalid bot type');
		}
		if (!in_array($bot, ['error', 'notify', 'debug'])) {
			throw new \Exception('Reporter: Send message via unknown bot: '.$bot);
		}
		if (empty($message)) {
			throw new \Exception('Reporter: Send empty message');
		}

		$message = "App [".self::$config['name']."] "." [$bot]\n".$message;

		$params = [
			'text' => $message
		];

		foreach (array_keys(self::$config['recipients']) as $recipient) {
		    if (!empty($recipients) && !in_array($recipient, $recipients)) {
		        continue;
            }

			$curl = curl_init("https://api.telegram.org/bot".self::$config['bot']."/sendMessage");

			$params['chat_id'] = self::$config['recipients'][$recipient]['chat_id'];

			if ($curl) {
				curl_setopt_array($curl, self::$default_curl_options);
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
				curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

				curl_setopt($curl, CURLOPT_PROXY, 'UserGroup1:i697HFcjD1wzXpzJhfHe@178.162.211.234:10080');
				curl_setopt($curl, CURLOPT_PROXYTYPE, 7);
				curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);

				curl_exec($curl);

				$errno = curl_errno($curl);
				$info = curl_getinfo($curl);

				$http_code = $info['http_code'];

				if ($errno || $http_code != 200) {
					throw new \Exception("Can't send report: \nrecipient: ".$recipient.", message: ".$message);
				}
			} else {
				throw new \Exception("Can't init curl");
			}
		}
	}

    /**
     * @param array $message
     * @param array $recipients
     * @throws \Exception
     */
	public static function error(array $message, array $recipients = [])
    {
		self::send($recipients, $message, 'error');
	}

    /**
     * @param string $message
     * @param array $recipients
     * @throws \Exception
     */
	public static function notify(string $message, array $recipients = [])
    {
		self::send($recipients, $message, 'notify');
	}

    /**
     * @param string $message
     * @param array $recipients
     * @throws \Exception
     */
	public static function debug(string $message, array $recipients = [])
    {
        self::send($recipients, $message, 'debug');
	}

    /**
     * @throws \Exception
     */
	public static function initConfig()
    {
        self::$config = Config::get('telegram');
        if (empty(self::$config['name'] || !is_string(self::$config['name']))) {
            throw new \Exception("Invalid telegram reporter name");
        }
        if (empty(self::$config['bot']) || !is_string(self::$config['bot'])) {
            throw new \Exception("Invalid telegram reporter bot");
        }
        if (empty(self::$config['recipients']) || !is_array(self::$config['recipients'])) {
            throw new \Exception("Invalid telegram reporter bot");
        } else {
            foreach (self::$config['recipients'] as $recipientName => $chatId) {
                if (empty($recipientName) || !is_string($recipientName)) {
                    throw new \Exception("Invalid telegram reporter recipient name");
                }
                if (empty($chatId['chat_id'])) {
                    throw new \Exception("Invalid telegram reporter chat id. Recipient: ".$recipientName);
                }
            }
        }
    }
}