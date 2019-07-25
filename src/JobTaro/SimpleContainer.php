<?php namespace Gcore\JobTaro;

use Psr\Container\ContainerInterface;

class SimpleContainer implements ContainerInterface
{
    public function get($id)
	{
		return new $id;
	}
	public function has($id)
	{
		return true;
	}
}