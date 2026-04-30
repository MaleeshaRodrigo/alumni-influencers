<?php

use PHPUnit\Framework\TestCase as PhpUnitTestCase;

abstract class TestCase extends PhpUnitTestCase
{
	protected $ci;

	protected function setUp(): void
	{
		parent::setUp();
		$this->ci = new TestCiContainer();
		$this->bindCi($this->ci);
	}

	protected function tearDown(): void
	{
		$this->bindCi(NULL);
		parent::tearDown();
	}

	protected function bindCi($instance)
	{
		$ref = new ReflectionProperty('CI_Controller', 'instance');
		$ref->setAccessible(TRUE);
		$ref->setValue($instance);
	}

	protected function makeWithoutConstructor($className)
	{
		$ref = new ReflectionClass($className);
		return $ref->newInstanceWithoutConstructor();
	}

	protected function newController($className)
	{
		$controller = $this->makeWithoutConstructor($className);
		$controller->load = $this->ci->load;
		$controller->config = $this->ci->config;
		$controller->input = $this->ci->input;
		$controller->output = $this->ci->output;
		$controller->session = $this->ci->session;
		$controller->uri = $this->ci->uri;
		return $controller;
	}

	protected function setProperty($object, $property, $value)
	{
		$ref = new ReflectionProperty(get_class($object), $property);
		$ref->setAccessible(TRUE);
		$ref->setValue($object, $value);
	}

	protected function invokeMethod($object, $method, array $arguments = array())
	{
		$ref = new ReflectionMethod(get_class($object), $method);
		$ref->setAccessible(TRUE);
		return $ref->invokeArgs($object, $arguments);
	}

	protected function jsonDecodeOutput(FakeOutput $output)
	{
		return json_decode((string) $output->output, TRUE);
	}
}