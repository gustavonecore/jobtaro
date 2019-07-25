<?php namespace Gcore\JobTaro\Contracts;

/**
 * Define a job instance to be processed
 */
interface JobInterface
{
	/**
	 * Get the number of attempts to run the job
	 *
	 * @return integer
	 */
	public function getAttempts() : int;

	/**
	 * Set the attempts of execution of the job
	 *
	 * @param integer $attempt
	 * @return void
	 */
	public function setAttempts(int $attempt);

	/**
	 * Do the work with the given payload
	 *
	 * @param array $payload
	 * @return bool True if was processed ok, False if something goes wrong
	 */
	public function process(array $payload) : bool;

	/**
	 * Get max number of attempts defined for the job
	 *
	 * @return integer
	 */
	public function getMaxAttempts() : int;

	/**
	 * Set job id
	 *
	 * @param string $id
	 * @return void
	 */
	public function setJobId(string $id);

	/**
	 * Get job id
	 *
	 * @return string
	 */
	public function getJobId() : string;

	/**
	 * Increase the attempts of processing the job and return the related value
	 *
	 * @return integer
	 */
	public function increaseAttempts() : int;

	/**
	 * Evaluate if the job can retry
	 *
	 * @return boolean
	 */
	public function canRetry() : bool;

	/**
	 * Set a computed id based in the class handler and payload
	 *
	 * @return string
	 */
	public function setComputedId(string $cid);

	/**
	 * Get a computed id based in the class handler and payload
	 *
	 * @return string
	 */
	public function getComputedId() : string;
}