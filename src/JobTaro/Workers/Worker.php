<?php namespace Gcore\JobTaro\Workers;

use Exception;
use DateTimeImmutable;
use Gcore\JobTaro\Contracts\QueueMessageInterface;
use Gcore\JobTaro\Workers\AbstractWorker;

class Worker extends AbstractWorker
{
	/**
	 * Timeout given to run the worker. As a sanity check to avoid eternally running
	 */
	const TIMEOUT = 60*60;

	/**
	 * {@inheritDoc}
	 */
	public function run()
	{
		$expireDt = (new DateTimeImmutable('now UTC'))->modify('+' . self::TIMEOUT . ' seconds');
		$start = new DateTimeImmutable('now UTC');

		while ($start <= $expireDt)
		{
			$this->driver->dequeue(function(QueueMessageInterface $qMessage)
			{
				$job = $this->buildJobFromMessage($qMessage);

				$currentAttempts = $job->getAttempts();

				$this->logger->info(get_class($job) . ' - Payload: ' . json_encode($qMessage->getPayload()));

				try
				{
					$result = $job->process($qMessage->getPayload());

					$job->increaseAttempts();

					if (!$result && $job->canRetry())
					{
						$this->logger->info(get_class($job) . ' - Moving into error queue. Attempts');

						$this->driver->enqueueError($qMessage, $job->getAttempts());
					}

					return;
				}
				catch (Exception $e)
				{
					if ($job->getAttempts() === $currentAttempts)
					{
						$job->increaseAttempts();
					}

					if ($job->canRetry())
					{
						$this->logger->error(get_class($job) . ' - Requeuing into error because not handled exception. Exception: ' . $e->getMessage());

						$this->driver->enqueueError($qMessage, $job->getAttempts(), $e->getTraceAsString());
					}
					else
					{
						if ($this->failedJobsHandler)
						{
							$this->logger->error(get_class($job) . ' - Calling Failed job processor. Exception: ' . $e->getMessage());

							$this->failedJobsHandler->handle($job, $qMessage->getPayload(), $e);
						}
						else
						{
							$this->logger->error(get_class($job) . ' - Removing job from queue since it can not be processed. Exception: ' . $e->getMessage());
						}
					}

					return;
				}
			});

			$start = new DateTimeImmutable('now UTC');

			sleep(5);
		}

		return;
	}
}