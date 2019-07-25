<?php namespace Gcore\JobTaro;

use Gcore\JobTaro\Contracts\JobDispatcher;
use Gcore\JobTaro\Contracts\QueueDriverInterface;
use Gcore\JobTaro\Contracts\JobTaroMessage;

class JobTaroDispatcher implements JobDispatcher
{
	/**
	 * @var \Gcore\JobTaro\Contracts\QueueDriverInterface
	 */
	protected $driver;

	/**
	 * Constructs the worker
	 *
	 * @param \Gcore\JobTaro\Contracts\QueueDriverInterface   $driver     Queue driver
	 */
	public function __construct(QueueDriverInterface $driver)
	{
		$this->driver = $driver;
	}

	/**
	 * {@inheritDoc}
	 */
	public function dispatch(string $handler, array $payload) : string
	{
		return $this->driver->enqueue(new JobTaroMessage($handler, $payload));
	}
}