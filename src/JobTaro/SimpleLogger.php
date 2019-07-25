<?php namespace Gcore\JobTaro;

use Psr\Log\LoggerInterface;

/**
 * Class to define a simple logger.
 */
class SimpleLogger implements LoggerInterface
{
	public function emergency($message, array $context = array())
	{
		\error_log($message);
	}
	public function alert($message, array $context = array())
	{
		\error_log($message);
	}
	public function critical($message, array $context = array())
	{
		\error_log($message);
	}
	public function error($message, array $context = array())
	{
		\error_log($message);
	}
	public function warning($message, array $context = array())
	{
		\error_log($message);
	}
	public function notice($message, array $context = array())
	{
		\error_log($message);
	}
	public function info($message, array $context = array())
	{
		\error_log($message);
	}
	public function debug($message, array $context = array())
	{
		\error_log($message);
	}
	public function log($level, $message, array $context = array())
	{
		\error_log('[' . $level . '] - ' . $message);
	}
}