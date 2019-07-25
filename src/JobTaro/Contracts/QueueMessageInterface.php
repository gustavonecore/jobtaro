<?php namespace Gcore\JobTaro\Contracts;

/**
 * Define a queue message
 */
interface QueueMessageInterface
{
	const HEADER_TRY = 'tries';
	const HEADER_ERROR = 'error_message';

	/**
	 * Get handler name
	 *
	 * @return string
	 */
	public function getHandlerName() : string;

	/**
	 * Get payload
	 *
	 * @return array
	 */
	public function getPayload() : array;

	/**
	 * Get messageId
	 *
	 * @return string
	 */
	public function getId() : string;

	/**
	 * Get number of attempts to process the message
	 *
	 * @return string
	 */
	public function getAttempts() : int;
}