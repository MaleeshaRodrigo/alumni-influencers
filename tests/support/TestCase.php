<?php

abstract class TestCase
{
	protected $ci;

	public function setUp()
	{
		$this->ci = new TestCiContainer();
		$this->bindCi($this->ci);
	}

	public function tearDown()
	{
		$this->bindCi(NULL);
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
		$controller->load = new FakeLoader($controller, $this->ci);
		$controller->config = $this->ci->config;
		$controller->input = $this->ci->input;
		$controller->output = $this->ci->output;
		$controller->session = $this->ci->session;
		$controller->uri = $this->ci->uri;
		return $controller;
	}

	protected function setProperty($object, $property, $value)
	{
		if (property_exists($object, $property)) {
			$ref = new ReflectionProperty(get_class($object), $property);
			$ref->setAccessible(TRUE);
			$ref->setValue($object, $value);
			return;
		}

		$object->{$property} = $value;
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

	protected function assertTrue($condition, $message = '')
	{
		if ($condition !== TRUE) {
			$this->fail($message !== '' ? $message : 'Failed asserting that condition is true.');
		}
	}

	protected function assertFalse($condition, $message = '')
	{
		if ($condition !== FALSE) {
			$this->fail($message !== '' ? $message : 'Failed asserting that condition is false.');
		}
	}

	protected function assertSame($expected, $actual, $message = '')
	{
		if ($expected !== $actual) {
			$this->fail($message !== '' ? $message : 'Failed asserting that two values are identical. Expected ' . var_export($expected, TRUE) . ' got ' . var_export($actual, TRUE) . '.');
		}
	}

	protected function assertCount($expectedCount, $haystack, $message = '')
	{
		if (count($haystack) !== (int) $expectedCount) {
			$this->fail($message !== '' ? $message : 'Failed asserting count matches expected value.');
		}
	}

	protected function assertNull($actual, $message = '')
	{
		if (!is_null($actual)) {
			$this->fail($message !== '' ? $message : 'Failed asserting that value is null.');
		}
	}

	protected function assertArrayNotHasKey($key, array $array, $message = '')
	{
		if (array_key_exists($key, $array)) {
			$this->fail($message !== '' ? $message : 'Failed asserting that array does not have the specified key.');
		}
	}

	protected function assertStringContainsString($needle, $haystack, $message = '')
	{
		if (strpos((string) $haystack, (string) $needle) === FALSE) {
			$this->fail($message !== '' ? $message : 'Failed asserting that string contains the expected substring.');
		}
	}

	protected function fail($message)
	{
		throw new Exception($message);
	}
}