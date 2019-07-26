<?php namespace Gcore\JobTaro\Contracts;

use Gcore\JobTaro\Contracts\JobInterface;
use Exception;

/**
 * Defines a failed jobs processor
 */
interface FailedJobProcessorInterface
{
	/**
	 * Handle the failed job
	 *
	 * @param \Gcore\JobTaro\Contracts\JobInterface $failedJob
	 * @param array                                 $payload
	 * @param \Exception                            $exception
	 * @return void
	 */
    public function handle(JobInterface $failedJob, array $payload, Exception $exception = null);
}