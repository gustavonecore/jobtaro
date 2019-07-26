<?php namespace Gcore\JobTaro;

use Gcore\JobTaro\Contracts\FailedJobProcessorInterface;
use Gcore\JobTaro\Contracts\JobInterface;

class FileStorageFailedJobProcessor implements FailedJobProcessorInterface
{
	/**
	 * {@inheritDoc}
	 */
    public function handle(JobInterface $failedJob, array $payload)
	{
		file_put_contents('failed_jobs.log', 'Job: ' . $failedJob . ' - Payload: ' . json_encode($payload));
	}
}