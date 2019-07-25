<?php namespace Gcore\JobTaro\Contracts;

use Gcore\JobTaro\Contracts\QueueMessageInterface;
use Interop\Queue\Message;

class JobTaroMessage implements QueueMessageInterface
{
	/**
	 * Handler class name
	 *
	 * @var string
	 */
	protected $handlerCls;

	/**
	 * Payload
	 *
	 * @var array
	 */
	protected $payload;

	/**
	 * Message id
	 *
	 * @var mixed
	 */
	protected $id;

	/**
	 * Attempts
	 *
	 * @var mixed
	 */
	protected $attempts;

	/**
	 * Creates a new message
	 *
	 * @param string $handlerCls   Handler class name
	 * @param array  $payload      Payload to be processed
	 * @param string $id           Message id
	 */
	public function __construct(string $handlerCls, array $payload, string $id = null, int $attempts = 0)
	{
		$this->handlerCls = $handlerCls;
		$this->payload = $payload;
		$this->id = $id === null ? rand(99, 888888888888) . md5($handlerCls . implode('', array_keys($payload))) : $id;
		$this->attempts = $attempts;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHandlerName(): string
	{
		return $this->handlerCls;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPayload(): array
	{
		return $this->payload;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAttempts() : int
	{
		return $this->attempts;
	}

	/**
	 * Get message from interop queue message
	 *
	 * @param \Interop\Queue\Message  $message
	 * @return \Gcore\JobTaro\Contracts\QueueMessageInterface
	 */
	public static function fromInteropMessage(Message $message) : QueueMessageInterface
	{
		[$handlerCls, $payload] = unserialize($message->getBody());

		return new self(
			$handlerCls,
			$payload,
			$message->getMessageId(),
			(int)$message->getHeader(QueueMessageInterface::HEADER_TRY)
		);
	}

	/**
	 * Get message from plain array
	 *
	 * @param array $data
	 * @return QueueMessageInterface
	 */
	public static function fromArray(array $data) : QueueMessageInterface
	{
		return new self(
			$data['handler'],
			json_decode($data['payload'], true),
			$data['uuid'],
			(int)$data['tries']
		);
	}
}