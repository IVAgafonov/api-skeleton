<?php

namespace App\System\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Logger implements LoggerInterface
{
    public static $service = 'unknown';
	protected $name;
	protected $pid = 0;
	protected $max_file_size;
	protected $path;
	protected $shell = false;

	public function __construct($name = 'common', $pid = 0, $max_file_size = 10000000)
	{
		$this->name = $name;
		$this->pid = $pid;
		$this->max_file_size = $max_file_size;
		if (!is_dir(__DIR__."/../../../logs")) {
            mkdir(__DIR__."/../../../logs");
            chmod(__DIR__."/../../../logs", 0777);
        }
		$this->path = __DIR__."/../../../logs/".$name;
	}

	public function setShell($shell)
	{
		$this->shell = $shell;
		return $this;
	}

	public function getShell()
	{
		return $this->shell;
	}

	public function emergency($message, array $context = array())
	{
		$this->log(LogLevel::EMERGENCY, $message, $context);
	}

	public function alert($message, array $context = array())
	{
		$this->log(LogLevel::ALERT, $message, $context);
	}

	public function critical($message, array $context = array())
	{
		$this->log(LogLevel::CRITICAL, $message, $context);
	}

	public function error($message, array $context = array())
	{
		$this->log(LogLevel::ERROR, $message, $context);
	}

	public function warning($message, array $context = array())
	{
		$this->log(LogLevel::WARNING, $message, $context);
	}

	public function notice($message, array $context = array())
	{
		$this->log(LogLevel::NOTICE, $message, $context);
	}

	public function info($message, array $context = array())
	{
		$this->log(LogLevel::INFO, $message, $context);
	}

	public function debug($message, array $context = array())
	{
		$this->log(LogLevel::DEBUG, $message, $context);
	}

	public function log($level, $message, array $context = array())
	{
		$data = $message;
		if (is_array($message)) {
			$data = json_encode($message);
		}

		if (!is_string($message)) {
			$data = serialize($message);
		}

		$msg = date("Y-m-d H:i:s ")."[".$level."]";

		$msg .= "[".$this->pid."]";
		$msg .= "[".self::$service."]";

		$msg .= "\t".$data."\n";

		if ($this->shell) {
			echo $msg;
		}

		/*
		if (in_array($level, [
			LogLevel::DEBUG,
			LogLevel::CRITICAL,
			LogLevel::EMERGENCY,
		])) {
			PultReporter::debug($this->name.": ".$message);
		}
		*/

		if (!file_exists($this->path)) {
			file_put_contents($this->path, $msg);
			chmod($this->path, 0777);
		}

		file_put_contents($this->path, $msg, FILE_APPEND);

		$file_size = filesize($this->path);
		if ($file_size > $this->max_file_size) {
			rename($this->path, $this->path.date("_Y-m-d"));
		}
	}
}