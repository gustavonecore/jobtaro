<?php namespace Gcore\JobTaro\Jobs;

use Gcore\JobTaro\Contracts\JobInterface;
use RuntimeException;

/**
 * Class to define the basic behavior of jobs
 */
abstract class AbstractJob implements JobInterface
{
	/**
	 * @var int Number of attempts
	 */
	protected $attenmpts;

	/**
	 * @var int Number of max attempts
	 */
	protected $maxAttempts;

	/**
	 * @var string Job id
	 */
	protected $jobId;

	/**
	 * @var string Computed Id
	 */
	protected $cid;

    /**
	 * {@inheritDoc}
	 */
	public function setAttempts(int $attempt)
	{
		$this->attenmpts = $attempt;
	}

    /**
	 * {@inheritDoc}
	 */
	public function getAttempts() : int
	{
		return $this->attenmpts;
	}

    /**
	 * {@inheritDoc}
	 */
	public function process(array $payload) : bool
	{
		throw new RuntimeException('You must define this method in your job');
		return false;
	}

    /**
	 * {@inheritDoc}
	 */
	public function getMaxAttempts() : int
	{
		return $this->maxAttempts;
	}

    /**
	 * {@inheritDoc}
	 */
	public function setJobId(string $id)
	{
		$this->jobId = $id;
	}

    /**
	 * {@inheritDoc}
	 */
	public function getJobId() : string
	{
		return $this->jobId;
	}

    /**
	 * {@inheritDoc}
	 */
	public function setComputedId(string $cid)
	{
		$this->cid = $cid;
	}

    /**
	 * {@inheritDoc}
	 */
	public function getComputedId() : string
	{
		return $this->cid;
	}

    /**
	 * {@inheritDoc}
	 */
	public function increaseAttempts() : int
	{
		$this->attenmpts++;

		return $this->attenmpts;
	}

    /**
	 * {@inheritDoc}
	 */
	public function canRetry() : bool
	{
		return $this->getAttempts() < $this->getMaxAttempts();
	}

	/**
	 * Convert to string the  job instance
	 *
	 * @return string
	 */
	public function __toString()
	{
		return json_encode([
			'id' => $this->getJobId(),
			'attempts' => $this->getAttempts(),
			'max_attempts' => $this->getMaxAttempts(),
		]);
	}
}