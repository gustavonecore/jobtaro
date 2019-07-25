<?php namespace Gcore\JobTaro\Jobs;

class DummyMailerJob extends AbstractJob
{
	/**
	 * {@inheritDoc}
	 */
	protected $maxAttempts = 3;

    /**
	 * {@inheritDoc}
	 */
	public function process(array $payload) : bool
	{
		throw new \RuntimeException('Some weird issue here');
		//error_log('Job: ' . $this->getJobId() . ' - Sending email for payload: ' . print_r($payload, true));
		return true;
	}
}