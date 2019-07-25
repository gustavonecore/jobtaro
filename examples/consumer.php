<?php

require __DIR__ . '/bootstrap.php';

use Gcore\Event\EventManager;
use Gcore\Event\Managers\RabbitMqEventManager;
use Gcore\Event\Managers\RedisEventManager;

$eventsSignature = require 'events.php';

/*
$rabbitDriver = new RabbitMqEventManager([
	'host' => '127.0.0.1',
	'port' => 5672,
	'user' => 'guest',
	'pass' => 'guest',
]);
*/
$redisDriver = new RedisEventManager([
	'host' => '127.0.0.1',
	'port' => 6379,
	'scheme_extensions' => ['phpredis'],
]);

$eventManager = new EventManager($redisDriver, function($className)
{
	// This should be replaced for a proper container->make method here
	return new $className;
});

$eventManager->setSignature($eventsSignature);
$eventManager->detach();

