<?php

class RoutesAndSchemaTest extends TestCase
{
	public function testRoutesIncludeCoreCourseworkEndpoints()
	{
		$routes = file_get_contents(APPPATH . 'config/routes.php');

		$this->assertStringContainsString("auth/do-register", $routes);
		$this->assertStringContainsString("bids/run-daily-winner", $routes);
		$this->assertStringContainsString("api/analytics/trends", $routes);
	}

	public function testSchemaContainsDirectUserNameAndProfileDisplayName()
	{
		$schema = file_get_contents(FCPATH . 'database/schema.sql');

		$this->assertStringContainsString('`full_name` varchar(150) NOT NULL', $schema);
		$this->assertStringContainsString('`display_name` varchar(150) NOT NULL', $schema);
	}
}