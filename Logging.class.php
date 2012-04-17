<?php
/**
 * Logging class
 *
 * @author Mihai Zaharie <mihai@zaharie.ro>
 */

class Logging
{
	private $archivalSize = 50; // Megabytes
	protected $logFilePath = null;
	protected $logFile = null;

	public function __construct($logFile, $archivalSize = false)
	{
		$this->logFilePath = $logFile;
		if (is_int($archivalSize) && $archivalSize > 0)
		{
			$this->archivalSize = $archivalSize;
		}
		$this->archive();
		$this->logFile = fopen($logFile, 'a');
	}

	public function __destruct()
	{
		fclose($this->logFile);
		$this->logFile = null;
	}

	public function add($message)
	{
		$this->archive();
		fwrite($this->logFile, $message . "\n");
		return true;
	}

	public function archive()
	{
		if (file_exists($this->logFilePath) && filesize($this->logFilePath) >= $this->archivalSize * 1048576)
		{
			$pathInfo = pathinfo($this->logFilePath);
			$archivedName = $pathInfo['dirname'] . '/' . date('Y-m-d His', time()) . '.' . $pathInfo['extension'];
			copy($this->logFilePath, $archivedName);
			$currentLog = fopen($this->logFilePath, 'w');
			fclose($currentLog);
			//unlink($this->logFilePath);
		}
		return true;
	}
}