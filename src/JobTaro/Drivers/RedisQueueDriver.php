<?php namespace Gcore\JobTaro\Drivers;

use Closure;
use Enqueue\Redis\RedisConnectionFactory;
use Enqueue\Redis\RedisMessage;
use Gcore\JobTaro\Contracts\QueueDriverInterface;
use Gcore\JobTaro\Contracts\QueueMessageInterface;
use Gcore\JobTaro\Contracts\JobTaroMessage;
use Interop\Queue\Consumer;
use Interop\Queue\Producer;
use Interop\Queue\Queue;
use Psr\Container\ContainerInterface;

/**
 * Redis driver to queue processing
 */
class RedisQueueDriver implements QueueDriverInterface
{
	const DEFAULT_JOBS_QUEUE = 'jobtaro_job';
	const DEFAULT_FAILED_QUEUE = 'jobtaro_job_failed';

	/**
	 * @var \Interop\Queue\Context  Context
	 */
	protected $context;

	/**
	 * @var string Queue name
	 */
	protected $queueName;

	/**
	 * @var Queue Main queue
	 */
	protected $queue;

	private $usedQueue;

	/**
	 * @var Queue Failed queue
	 */
	protected $queueFailed;

	/**
	 * @var \Interop\Queue\Producer
	 */
	protected $producer;

	/**
	 * @var \Interop\Queue\Consumer
	 */
	protected $consumer;

	public function __construct(array $options, ContainerInterface $container = null)
	{
		$this->context = (new RedisConnectionFactory($options))->createContext();
		$this->queueName = array_key_exists('queue', $options) ? $options['queue'] : self::DEFAULT_JOBS_QUEUE;
		$this->usedQueue = $this->context->createQueue($this->queueName);
		$this->container = $container;
	}

	/**
	 * Get a singleton instance of consumer
	 *
	 * @return \Interop\Queue\Consumer
	 */
	public function getConsumer() : Consumer
	{
		if (!$this->consumer)
		{
			$this->consumer = $this->context->createConsumer($this->usedQueue);
		}

		return $this->consumer;
	}

	/**
	 * Get a singleton instance of producer
	 *
	 * @return \Interop\Queue\Producer
	 */
	public function getProducer() : Producer
	{
		if (!$this->producer)
		{
			$this->producer = $this->context->createProducer();
		}

		return $this->producer;
	}

	/**
	 * {@inheritDoc}
	 */
	public function enqueue(QueueMessageInterface $qMessage) : string
	{
		$message = $this->toRedisMessage($qMessage);

		$message->setHeader(QueueMessageInterface::HEADER_TRY, 0);

		$this->getProducer()->send($this->usedQueue, $message);

		return $message->getMessageId();
	}

	/**
	 * {@inheritDoc}
	 */
	public function dequeue(Closure $handler)
	{
		$consumer = $this->getConsumer();

		$message = $consumer->receive();

		$handler(JobTaroMessage::fromInteropMessage($message));

		$consumer->acknowledge($message);
	}

	/**
	 * CReates a new redis message from internal Jobtaro message
	 *
	 * @param QueueMessageInterface $qMessage
	 * @return RedisMessage
	 */
	public function toRedisMessage(QueueMessageInterface $qMessage) : RedisMessage
	{
		return $this->context->createMessage(serialize([$qMessage->getHandlerName(), $qMessage->getPayload()]));
	}

	/**
	 * Enqueue message right into the error stack
	 *
	 * @param QueueMessageInterface $qMessage   Jobtaro Message
	 * @param integer               $attemps    Number of tries
	 * @param string                $error      Error message if provided
	 * @return string
	 */
	public function enqueueError(QueueMessageInterface $qMessage, int $attemps, string $error = null) : string
	{
		if (!$this->queueFailed)
		{
			$this->queueFailed = $this->context->createQueue(self::DEFAULT_FAILED_QUEUE);
		}

		$message = $this->toRedisMessage($qMessage);
		$message->setHeader(QueueMessageInterface::HEADER_TRY, $attemps);

		if ($error)
		{
			$message->setHeader(QueueMessageInterface::HEADER_ERROR, $error);
		}

		$this->getProducer()->send($this->queueFailed, $message);

		return $message->getMessageId();
	}
}