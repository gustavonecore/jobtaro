#!/usr/bin/env php
<?php require __DIR__ . '/../vendor/autoload.php';

$dispatcher = new \Gcore\JobTaro\JobTaroDispatcher(new \Gcore\JobTaro\Drivers\MysqlQueueDriver([
	'dsn' => 'mysql:host=db;dbname=lautaro;charset=utf8mb4',
	'username' => 'root',
	'password' => 'root',
	'options' => []
]));

$dispatcher->dispatch(\Gcore\JobTaro\Jobs\DummyMailerJob::class, [
	'email' => 'gustavo@onecore.cl',
	'subject' => 'Hello Job Server!',
	'message' => 'Process me!'
]);
