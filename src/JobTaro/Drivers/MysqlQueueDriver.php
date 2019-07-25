<?php namespace Gcore\JobTaro\Drivers;

use Closure;
use Exception;
use Gcore\JobTaro\Contracts\QueueDriverInterface;
use Gcore\JobTaro\Contracts\QueueMessageInterface;
use Gcore\JobTaro\Contracts\JobTaroMessage;
use PDO;

class MysqlQueueDriver implements QueueDriverInterface
{
	const DEFAULT_JOBS_QUEUE = 'jobtaro_queue';
	const DEFAULT_FAILED_QUEUE = 'jobtaro_queue_failed';

	protected $pdo;

	/**
	 * @var string Queue name
	 */
	protected $queueName;

	public function __construct(array $options, ContainerInterface $container = null)
	{
		$this->queueName = array_key_exists('queue', $options) ? $options['queue'] : self::DEFAULT_JOBS_QUEUE;
		$this->pdo = new PDO($options['dsn'], $options['username'], $options['password'], $options['options']);
	}

	private function insertJob(QueueMessageInterface $qMessage, string $table = self::DEFAULT_JOBS_QUEUE) : string
	{
		$uuid = md5(\rand(999, 9999999999));

		$this->pdo->prepare('INSERT INTO ' . $this->queueName . '(uuid, handler, payload, created_dt) VALUES(:uuid, :handler, :payload, :created_dt)')->
			execute([
				'uuid' => $uuid,
				'handler' => $qMessage->getHandlerName(),
				'payload' => json_encode($qMessage->getPayload()),
				'created_dt' => gmdate('Y-m-d H:i:s'),
			]
		);

		return $uuid;
	}

	private function fetchJob() : array
	{
		$job = [];

		do
		{
			$stm = $this->pdo->prepare('SELECT uuid, handler, payload, tries FROM ' . $this->queueName . '
				WHERE finished_dt IS NULL
				ORDER BY created_dt DESC
				LIMIT 1 FOR UPDATE');

			error_log('SELECT uuid, handler, payload, tries FROM ' . $this->queueName . '
			WHERE finished_dt IS NULL
			ORDER BY created_dt DESC
			LIMIT 1 FOR UPDATE');

			$stm->execute();

			$job = $stm->fetch(PDO::FETCH_ASSOC);
		}
		while ($job === false);

		return $job;
	}

	private function finishJob(string $uuid, string $startDt)
	{
		$this->pdo->prepare('UPDATE ' . $this->queueName . ' SET started_dt=:started_dt, finished_dt=:finished_dt WHERE uuid=:uuid')->execute([
			'started_dt' => $startDt,
			'finished_dt' => gmdate('Y-m-d H:i:s'),
			'uuid' => $uuid,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function enqueue(QueueMessageInterface $qMessage) : string
	{
		return $this->insertJob($qMessage);
	}

	/**
	 * {@inheritDoc}
	 */
	public function dequeue(Closure $handler)
	{
		$job = $this->fetchJob();

		$startDt = gmdate('Y-m-d H:i:s');

		error_log('job: ' . print_r($job, true));

		$handler(JobTaroMessage::fromArray($job));

		$this->finishJob($job['uuid'], $startDt);
	}

	/**
	 * {@inheritDoc}
	 */
	public function enqueueError(QueueMessageInterface $qMessage, int $attemps, string $error = null) : string
	{
		$uuid = md5(\rand(999, 9999999999));

		try
		{
			$this->pdo->beginTransaction();

			$this->pdo->prepare('DELETE FROM ' . $this->queueName . ' WHERE uuid=?')->execute([$qMessage->getId()]);
			$this->pdo->prepare('INSERT INTO ' . self::DEFAULT_FAILED_QUEUE . '(uuid, handler, payload, response, tries, created_dt)
				VALUES(:uuid, :handler, :payload, :response, :tries, :created_dt)')->
				execute([
					'uuid' => $uuid,
					'handler' => $qMessage->getHandlerName(),
					'payload' => json_encode($qMessage->getPayload()),
					'response' => $error,
					'tries' => $attemps,
					'created_dt' => gmdate('Y-m-d H:i:s'),
				]
			);

			$this->pdo->commit();
		}
		catch (Exception $e)
		{
			$this->pdo->rollBack()();

			throw $e;
		}

		return $uuid;
	}
}