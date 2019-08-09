<?php namespace Gcore\JobTaro;

use Symfony\Component\Process\Process;
use InvalidArgumentException;

class JobTaroServer
{
	const DEFAULT_WORKERS = 3;
	const DEFAULT_FAILED_WORKERS = 1;

	/**
	 * @var array List of configurations for job server
	 */
	protected $options;

	/**
	 * @var array List of configured workers
	 */
	protected $workers;

	/**
	 * @var array List of workers for death letter
	 */
	protected $deathLetterWorkers;

	/**
	 * @var array List of running workers
	 */
	protected $runningWorkers;

	/**
	 * @var array List of runn
	 */
	protected $runningDeathLetterWorkers;

	/**
	 * @var int Number of workers
	 */
	protected $numberOfWorkers;

	/**
	 * @var int Number of fail handler workers
	 */
	protected $numberOfFailingWorkers;

	/**
	 * @var string  Path to the binary script to spawn workers
	 */
	protected $pathForWorker;

	/**
	 * @var string  Path to the binary script to spawn error handling workers
	 */
	protected $pathForFailedWorker;

	/**
	 * Constructs the server
	 *
	 * @param array $options
	 */
	public function __construct(array $options = [])
	{
		$this->workers = [];
		$this->setOptions($options);
	}

	/**
	 * Set default options for server
	 *
	 * @param array $options
	 * @return void
	 */
	public function setOptions(array $options)
	{
		if (!isset($options['worker_path']) || isset($options['worker_failed_path']))
		{
			throw new InvalidArgumentException('You must define the path for worker and death letter worker binary paths');
		}

		$this->numberOfWorkers = isset($options['workers']) ? $options['workers'] : self::DEFAULT_WORKERS;
		$this->numberOfFailingWorkers = isset($options['death_letter_workers']) ? $options['death_letter_workers'] : self::DEFAULT_FAILED_WORKERS;

		$this->pathForWorker = $options['worker_path'];
		$this->pathForFailedWorker = $options['worker_failed_path'];
	}

	/**
	 * Initial Setup of workers
	 *
	 * @return void
	 */
	private function setupWorkers()
	{
		for ($i = 0; $i < $this->numberOfWorkers; $i++)
		{
			$this->workers[] = new Process(['php', $this->pathForWorker]);
		}

		for ($i = 0; $i < $this->numberOfFailingWorkers; $i++)
		{
			$this->deathLetterWorkers[] = new Process(['php', $this->pathForFailedWorker]);
		}
	}

	/**
	 * Start every worker as an independent process
	 *
	 * @return void
	 */
	private function startWorkers()
	{
		foreach ($this->workers as $process)
		{
			$process->start();

			$this->runningWorkers[$process->getPid()] = $process;
		}

		foreach ($this->deathLetterWorkers as $process)
		{
			$process->start();

			$this->runningDeathLetterWorkers[$process->getPid()] = $process;
		}

		error_log($this->numberOfWorkers . ' workers started');
		error_log($this->numberOfFailingWorkers . ' death letter workers started');
	}

	/**
	 * Start job server
	 *
	 * @return void
	 */
	public function run()
	{
		$this->setupWorkers();

		$this->startWorkers();

		while (true)
		{
			$this->runningWorkers = $this->restartFinishedWorkers($this->runningWorkers, $this->pathForWorker);

			$this->runningDeathLetterWorkers = $this->restartFinishedWorkers($this->runningDeathLetterWorkers, $this->pathForFailedWorker);

			\sleep(5);
		}
	}

	/**
	 * Restart finished workers
	 *
	 * @param array $contextList
	 * @return array
	 */
	private function restartFinishedWorkers(array $contextList, string $binary) : array
	{
		$death = [];

		foreach ($contextList as $pid => $process)
		{
			if (!$process->isRunning())
			{
				$death[$pid] = $process;
			}
		}

		foreach ($death as $pid => $process)
		{
			// Just in case, force stop
			$process->stop();

			// Unregister from running ones
			unset($contextList[$pid]);

			// Restart
			$restarted = new Process(['php', $binary]);
			$restarted->start();
			$contextList[$restarted->getPid()] = $restarted;
		}

		return $contextList;
	}
}