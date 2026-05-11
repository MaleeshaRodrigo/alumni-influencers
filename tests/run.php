<?php

require __DIR__ . '/bootstrap.php';

$testFiles = glob(__DIR__ . '/unit/*Test.php');
sort($testFiles);

$total = 0;
$passed = 0;
$failed = array();

foreach ($testFiles as $file) {
	require_once $file;
	$className = basename($file, '.php');
	$ref = new ReflectionClass($className);
	$methods = array_filter($ref->getMethods(ReflectionMethod::IS_PUBLIC), function ($method) {
		return strpos($method->name, 'test') === 0;
	});

	foreach ($methods as $method) {
		$total++;
		$test = $ref->newInstance();
		try {
			if (method_exists($test, 'setUp')) {
				$test->setUp();
			}
			$method->invoke($test);
			if (method_exists($test, 'tearDown')) {
				$test->tearDown();
			}
			$passed++;
			echo ".";
		} catch (Exception $ex) {
			$failed[] = array($className . '::' . $method->name, $ex->getMessage());
			echo "F";
		}
	}
}

echo PHP_EOL;
echo 'Tests: ' . $total . ', Passed: ' . $passed . ', Failed: ' . count($failed) . PHP_EOL;

if (!empty($failed)) {
	echo PHP_EOL;
	foreach ($failed as $failure) {
		echo $failure[0] . ' => ' . $failure[1] . PHP_EOL;
	}
	exit(1);
}

exit(0);