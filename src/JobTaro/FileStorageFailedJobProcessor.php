<?php namespace Gcore\JobTaro;

use Gcore\JobTaro\Contracts\FailedJobProcessorInterface;
use Gcore\JobTaro\Contracts\JobInterface;
use Exception;

class FileStorageFailedJobProcessor implements FailedJobProcessorInterface
{
	/**
	 * {@inheritDoc}
	 */
    public function handle(JobInterface $failedJob, array $payload, Exception $exception = null)
	{
		$error = $exception ? $exception->getTraceAsString() : '';

		file_put_contents('failed_jobs.log', 'Job: ' . $failedJob . ' - Payload: ' . json_encode($payload) . ' - Error: ' . $error);
	}
}