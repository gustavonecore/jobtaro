<?php namespace Gcore\JobTaro\Workers;

use Gcore\JobTaro\Contracts\FailedJobProcessorInterface;
use Gcore\JobTaro\Contracts\JobInterface;
use Gcore\JobTaro\Contracts\QueueDriverInterface;
use Gcore\JobTaro\Contracts\QueueMessageInterface;
use Gcore\JobTaro\Contracts\WorkerInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractWorker implements WorkerInterface
{
	/**
	 * @var \Gcore\JobTaro\Contracts\QueueDriverInterface
	 */
	protected $driver;

	/**
	 * @var \Psr\Container\ContainerInterface
	 */
	protected $container;

	/**
	 * @var \Psr\Container\LoggerInterface
	 */
	protected $logger;

	/**
	 * @var \Gcore\JobTaro\Contracts\FailedJobProcessorInterface|null
	 */
	protected $failedJobsHandler;

	/**
	 * Constructs the worker
	 *
	 * @param \Gcore\JobTaro\Contracts\QueueDriverInterface               $driver                Queue driver
	 * @param \Psr\Container\ContainerInterface                           $container             PSR Containeer instance to inject dependencies into jobs
	 * @param \Psr\Container\LoggerInterface                              $container             PSR Logger instance
	 * @param \Gcore\JobTaro\Contracts\FailedJobProcessorInterface|null   $failedJobsHandler     Queue driver
	 */
	public function __construct(
		QueueDriverInterface $driver,
		ContainerInterface $container,
		LoggerInterface $logger,
		FailedJobProcessorInterface $failedJobsHandler = null
	)
	{
		$this->driver = $driver;
		$this->container = $container;
		$this->logger = $logger;
		$this->failedJobsHandler = $failedJobsHandler;
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildJobFromMessage(QueueMessageInterface $qMessage) : JobInterface
	{
		$job = $this->container->get($qMessage->getHandlerName());

		if (!($job instanceof JobInterface))
		{
			throw new InvalidJobDefinitionException;
		}

		$job->setJobId($qMessage->getId());
		$job->setAttempts($qMessage->getAttempts());

		return $job;
	}

	/**
	 * {@inheritDoc}
	 */
	public abstract function run();
}