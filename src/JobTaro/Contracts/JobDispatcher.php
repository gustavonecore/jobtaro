<?php namespace Gcore\JobTaro\Contracts;

interface JobDispatcher
{
	/**
	 * Dispatch a new job into the server
	 *
	 * @param string $handler  Class name of job responsible of processing payload
	 * @param array  $payload  Data to be processed
	 * @return string
	 */
    public function dispatch(string $handler, array $payload) : string;
}