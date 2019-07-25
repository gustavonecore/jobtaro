<?php require __DIR__ . '/../../vendor/autoload.php';

use Exception;
use Enqueue\Redis\RedisConnectionFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\Process\Process;
use Interop\Queue\Message;


class JobTaroServer
{
	const DEFAULT_WORKERS = 3;
	protected $container;
	protected $options;
	protected $runningProcesses;
	public function __construct(ContainerInterface $container, array $options = [])
	{
		$this->driver = $driver;
		$this->container = $container;
		$this->setOptions($options);
	}
	public function setOptions(array $options)
	{
		$this->workers = $options['workers'];
	}
	public function start()
	{
		foreach ($this->workers as $w)
		{
			$process =  new Process(['php', 'bin/run-worker']);
			$process->start();
			$this->runningProcesses[] = $process;
		}
	}
}

$container = new class implements ContainerInterface{
	public function get($id)
	{
		return new $id;
	}
	public function has($id)
	{
		return true;
	}
};

$queueProducer = new JobTaroQueuer(
	new RedisQueueDriver([
		'host' => '0.0.0.0',
		'port' => 6279,
		'queue' => 'jobstaro_main'
	])
);

$queueProducer->enqueue(NotifyUserJob::class, [
	'user' => new User,
	'message' => 'some message',
	'subject' => 'hello biatch',
]);

/**** */

$queueConsumer = new JobtaroServer($container, [
		'workers' => 10,
		'timeout' => 50000,
		'max_attempts' => 3,
	]
);

$queueConsumer->start();