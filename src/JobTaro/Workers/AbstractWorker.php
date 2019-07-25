<?php namespace Gcore\JobTaro\Workers;

use Gcore\JobTaro\Contracts\JobInterface;
use Gcore\JobTaro\Contracts\QueueDriverInterface;
use Gcore\JobTaro\Contracts\QueueMessageInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractWorker
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
	 * Constructs the worker
	 *
	 * @param \Gcore\JobTaro\Contracts\QueueDriverInterface   $driver     Queue driver
	 * @param \Psr\Container\ContainerInterface               $container  PSR Containeer instance to inject dependencies into jobs
	 * @param \Psr\Container\LoggerInterface                  $container  PSR Logger instance
	 */
	public function __construct(QueueDriverInterface $driver, ContainerInterface $container, LoggerInterface $logger)
	{
		$this->driver = $driver;
		$this->container = $container;
		$this->logger = $logger;
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
		$job->setComputedId($this->getComputedIdFromMessage($qMessage));

		return $job;
	}

	/**
	 * Generate an unique id for the given message
	 *
	 * @param QueueMessageInterface $qMessage
	 * @return string
	 */
	public function getComputedIdFromMessage(QueueMessageInterface $qMessage) : string
	{
		return sha1(implode('', array_values($qMessage->getPayload())) . $qMessage->getHandlerName());
	}
}