<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RateLimiter
{
	/** @var CI_Controller */
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->driver('cache', array('adapter' => 'file', 'backup' => 'dummy'));
	}

	public function is_limited($key, $max_attempts, $window_seconds)
	{
		$key = $this->cache_key($key);
		$max_attempts = max(1, (int) $max_attempts);
		$window_seconds = max(1, (int) $window_seconds);
		$data = $this->read($key);
		if (empty($data)) {
			return FALSE;
		}

		$now = time();
		$data = $this->prune($data, $now, $window_seconds);
		return count($data) >= $max_attempts;
	}

	public function hit($key, $window_seconds)
	{
		$key = $this->cache_key($key);
		$window_seconds = max(1, (int) $window_seconds);
		$now = time();
		$data = $this->read($key);
		$data = $this->prune($data, $now, $window_seconds);
		$data[] = $now;
		$this->CI->cache->save($key, $data, $window_seconds);
		return count($data);
	}

	public function clear($key)
	{
		return $this->CI->cache->delete($this->cache_key($key));
	}

	private function cache_key($key)
	{
		return 'rate_'.hash('sha256', (string) $key);
	}

	private function read($key)
	{
		$data = $this->CI->cache->get($key);
		return is_array($data) ? $data : array();
	}

	private function prune(array $data, $now, $window_seconds)
	{
		$cutoff = (int) $now - (int) $window_seconds;
		return array_values(array_filter($data, function ($ts) use ($cutoff) {
			return (int) $ts > $cutoff;
		}));
	}
}
