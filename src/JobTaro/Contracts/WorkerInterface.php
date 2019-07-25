<?php namespace Gcore\JobTaro\Contracts;

use Gcore\JobTaro\Contracts\JobInterface;
use Gcore\JobTaro\Contracts\QueueMessageInterface;

/**
 * Define a worker responsible to consume from the queue
 */
interface WorkerInterface
{
	/**
	 * Create a new job using dependency injection
	 *
	 * @param \Gcore\JobTaro\Contracts\QueueMessageInterface  $qMessage  Jobtaro Queue message
	 * @return \Gcore\JobTaro\Contracts\JobInterface
	 */
	public function buildJobFromMessage(QueueMessageInterface $qMessage) : JobInterface;

	/**
	 * Entry function to start consuming from the queue
	 *
	 * @return void
	 */
    public function run();
}