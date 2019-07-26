<?php namespace Gcore\JobTaro;

use Symfony\Component\Process\Process;

class JobTaroServer
{
	const DEFAULT_WORKERS = 3;
	const PATH_BIN = __DIR__ . '/../../bin/run-worker';

	/**
	 * @var array List of configurations for job server
	 */
	protected $options;

/**
	 * @var array List of configured workers
	 */
	protected $workers;

	/**
	 * @var array List of running workers
	 */
	protected $runningWorkers;

	/**
	 * @var int Number of workers
	 */
	protected $numberOfWorkers;

	/**
	 * @var string  Path to the binary script to spawn workers
	 */
	protected $pathForWorker;

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
		$this->numberOfWorkers = isset($options['workers']) ? $options['workers'] : self::DEFAULT_WORKERS;
		$this->pathForWorker = isset($options['worker_path']) ? $options['worker_path'] : self::PATH_BIN;
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

		error_log($this->numberOfWorkers . ' workers started');
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
			$death = [];

			foreach ($this->runningWorkers as $pid => $process)
			{
				if (!$process->isRunning())
				{
					$death[$pid] = $process;
				}
			}

			// Restart death processes
			if ($death !== [])
			{
				error_log('Restarting ' . count($death) . ' workers');

				foreach ($death as $pid => $process)
				{
					// Just in case, force stop
					$process->stop();

					// Unregister from running ones
					unset($this->runningWorkers[$pid]);

					// Restart
					$restarted = new Process(['php', $this->pathForWorker]);
					$restarted->start();
					$this->runningWorkers[$restarted->getPid()] = $restarted;
				}
			}

			\sleep(5);
		}
	}
}