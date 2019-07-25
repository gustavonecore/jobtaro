#!/usr/bin/env php
<?php require __DIR__ . '/../vendor/autoload.php';

$dispatcher = new \Gcore\JobTaro\JobTaroDispatcher(new \Gcore\JobTaro\Drivers\RedisQueueDriver([
	'host' => '127.0.0.1',
	'port' => 6379,
	'scheme_extensions' => ['phpredis'],
]));

$dispatcher->dispatch(\Gcore\JobTaro\Jobs\DummyMailerJob::class, [
	'email' => 'gustavo@onecore.cl',
	'subject' => 'Hello Job Server!',
	'message' => 'Process me!'
]);
