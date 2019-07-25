<?php

require __DIR__ . '/bootstrap.php';

use Gcore\Event\Managers\RabbitMqEventManager;
use Gcore\Event\Managers\RedisEventManager;
use Gcore\Event\EventManager;
use Gcore\Event\Customer;
use Gcore\Event\WorkOrder;
use Gcore\Event\WorkOrderFinishedEvent;

$eventsSignature = require 'events.php';

/*
$eventManager = new EventManager(new RabbitMqEventManager([
	'host' => '127.0.0.1',
	'port' => 5672,
	'user' => 'guest',
	'pass' => 'guest',
]));
*/

$eventManager = new EventManager(new RedisEventManager([
	'host' => '127.0.0.1',
	'port' => 6379,
	'scheme_extensions' => ['phpredis'],
]));

$eventManager->setSignature($eventsSignature);

// Client code
# Work order was created at this point
$wo = new WorkOrder(new Customer('onecore'), 1000);
$event = new WorkOrderFinishedEvent($wo);

//print_r(unserialize($serie));
$eventManager->attach($event);
