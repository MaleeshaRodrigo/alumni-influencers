<?php

class RateLimiterTest extends TestCase
{
	public function testHitStoresRollingWindow()
	{
		$limiter = new RateLimiter();
		$this->setProperty($limiter, 'CI', $this->ci);

		$result = $limiter->hit('login:127.0.0.1', 60);

		$this->assertSame(1, $result);
		$this->assertCount(1, $this->ci->cache->store);
	}

	public function testIsLimitedReflectsCachedHits()
	{
		$limiter = new RateLimiter();
		$this->setProperty($limiter, 'CI', $this->ci);
		$key = 'rate_' . hash('sha256', 'auth_login_ip:127.0.0.1');
		$this->ci->cache->store[$key] = array(time() - 1, time());

		$this->assertTrue($limiter->is_limited('auth_login_ip:127.0.0.1', 2, 60));
		$this->assertFalse($limiter->is_limited('auth_login_ip:127.0.0.1', 3, 60));
	}

	public function testClearRemovesBucket()
	{
		$limiter = new RateLimiter();
		$this->setProperty($limiter, 'CI', $this->ci);
		$key = 'rate_' . hash('sha256', 'auth_register:127.0.0.1');
		$this->ci->cache->store[$key] = array(time());

		$this->assertTrue($limiter->clear('auth_register:127.0.0.1'));
		$this->assertArrayNotHasKey($key, $this->ci->cache->store);
	}
}