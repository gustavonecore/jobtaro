<?php namespace Gcore\JobTaro\Contracts;

use Closure;

/**
 * Define a queue driver
 */
interface QueueDriverInterface
{
	/**
	 * Enqueu a message
	 *
	 * @param string $jobClass
	 * @param array  $payload
	 * @return string Message id
	 */
	public function enqueue(QueueMessageInterface $qMessage) : string;

	/**
	 * Dequeue a message
	 *
	 * @param Closure $handler
	 * @return void
	 */
	public function dequeue(Closure $handler);

	/**
	 * Enqueue in failed list
	 *
	 * @param QueueMessageInterface $qMessage
	 * @param integer $attemps
	 * @return string
	 */
	public function enqueueError(QueueMessageInterface $qMessage, int $attemps, string $error = null) : string;
}