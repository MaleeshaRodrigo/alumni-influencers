<?php

class FakeDbResult
{
	private $rows;

	public function __construct(array $rows = array())
	{
		$this->rows = array_values($rows);
	}

	public function row_array()
	{
		return isset($this->rows[0]) ? $this->rows[0] : NULL;
	}

	public function result_array()
	{
		return $this->rows;
	}
}

class FakeDb
{
	public $insertCalls = array();
	public $updateCalls = array();
	public $deleteCalls = array();
	public $queryCalls = array();
	public $whereCalls = array();
	public $selectCalls = array();
	public $joinCalls = array();
	public $orderByCalls = array();
	public $limitCalls = array();
	public $groupByCalls = array();
	public $countCalls = array();
	public $insertId = 1;
	public $insertReturn = TRUE;
	public $updateReturn = TRUE;
	public $deleteReturn = TRUE;
	public $transStatus = TRUE;
	public $countResults = array();
	public $getResults = array();
	public $queryResults = array();
	public $lastSelectMax = array();
	public $transactions = array();

	public function reset()
	{
		$this->insertCalls = array();
		$this->updateCalls = array();
		$this->deleteCalls = array();
		$this->queryCalls = array();
		$this->whereCalls = array();
		$this->selectCalls = array();
		$this->joinCalls = array();
		$this->orderByCalls = array();
		$this->limitCalls = array();
		$this->groupByCalls = array();
		$this->countCalls = array();
		$this->countResults = array();
		$this->getResults = array();
		$this->queryResults = array();
		$this->lastSelectMax = array();
		$this->transactions = array();
		$this->insertReturn = TRUE;
		$this->updateReturn = TRUE;
		$this->deleteReturn = TRUE;
		$this->transStatus = TRUE;
		$this->insertId = 1;
	}

	public function queueGet(array $rows)
	{
		$this->getResults[] = array_values($rows);
	}

	public function queueQuery(array $rows)
	{
		$this->queryResults[] = array_values($rows);
	}

	public function queueCount($count)
	{
		$this->countResults[] = (int) $count;
	}

	public function select($select, $escape = NULL)
	{
		$this->selectCalls[] = array($select, $escape);
		return $this;
	}

	public function select_max($select, $alias = '')
	{
		$this->lastSelectMax = array($select, $alias);
		return $this;
	}

	public function from($table)
	{
		$this->queryCalls[] = array('from', array($table));
		return $this;
	}

	public function join($table, $condition, $type = '')
	{
		$this->joinCalls[] = array($table, $condition, $type);
		return $this;
	}

	public function where($key, $value = NULL, $escape = NULL)
	{
		$this->whereCalls[] = array('where', $key, $value, $escape);
		return $this;
	}

	public function where_in($key, array $values)
	{
		$this->whereCalls[] = array('where_in', $key, $values);
		return $this;
	}

	public function or_where($key, $value = NULL, $escape = NULL)
	{
		$this->whereCalls[] = array('or_where', $key, $value, $escape);
		return $this;
	}

	public function like($field, $match = '', $side = 'both')
	{
		$this->whereCalls[] = array('like', $field, $match, $side);
		return $this;
	}

	public function group_start()
	{
		$this->whereCalls[] = array('group_start');
		return $this;
	}

	public function group_end()
	{
		$this->whereCalls[] = array('group_end');
		return $this;
	}

	public function group_by($groupBy)
	{
		$this->groupByCalls[] = $groupBy;
		return $this;
	}

	public function order_by($field, $direction = '')
	{
		$this->orderByCalls[] = array($field, $direction);
		return $this;
	}

	public function limit($limit, $offset = 0)
	{
		$this->limitCalls[] = array((int) $limit, (int) $offset);
		return $this;
	}

	public function insert($table, array $data)
	{
		$this->insertCalls[] = array($table, $data);
		return $this->insertReturn;
	}

	public function update($table, array $data = array())
	{
		$this->updateCalls[] = array($table, $data);
		return $this->updateReturn;
	}

	public function delete($table)
	{
		$this->deleteCalls[] = array($table);
		return $this->deleteReturn;
	}

	public function count_all_results($table = '')
	{
		$this->countCalls[] = $table;
		if (!empty($this->countResults)) {
			return (int) array_shift($this->countResults);
		}

		return 0;
	}

	public function get($table = '')
	{
		$this->queryCalls[] = array('get', array($table));
		$rows = !empty($this->getResults) ? array_shift($this->getResults) : array();
		return new FakeDbResult($rows);
	}

	public function query($sql, array $params = array())
	{
		$this->queryCalls[] = array($sql, $params);
		$rows = !empty($this->queryResults) ? array_shift($this->queryResults) : array();
		return new FakeDbResult($rows);
	}

	public function insert_id()
	{
		return (int) $this->insertId;
	}

	public function trans_begin($test_mode = FALSE)
	{
		$this->transactions[] = array('begin', (bool) $test_mode);
		return TRUE;
	}

	public function trans_commit()
	{
		$this->transactions[] = array('commit');
		return TRUE;
	}

	public function trans_rollback()
	{
		$this->transactions[] = array('rollback');
		return TRUE;
	}

	public function trans_status()
	{
		return (bool) $this->transStatus;
	}
}

class FakeLoader
{
	private $ci;

	public function __construct($ci)
	{
		$this->ci = $ci;
	}

	public function database()
	{
		return TRUE;
	}

	public function helper()
	{
		return TRUE;
	}

	public function config()
	{
		return TRUE;
	}

	public function driver($name, array $params = array())
	{
		if ($name === 'cache' && !isset($this->ci->cache)) {
			$this->ci->cache = new FakeCache();
		}

		return TRUE;
	}

	public function library($library, $params = NULL, $alias = NULL)
	{
		$class = is_string($library) ? $library : '';
		if ($class === '' || !class_exists($class)) {
			return TRUE;
		}

		$object = new $class();
		$property = $alias ?: strtolower($class);
		$this->ci->{$property} = $object;
		return $object;
	}

	public function model($model, $alias = NULL)
	{
		$class = is_string($model) ? $model : '';
		if ($class === '' || !class_exists($class)) {
			return TRUE;
		}

		$object = new $class();
		$property = $alias ?: strtolower($class);
		$this->ci->{$property} = $object;
		return $object;
	}
}

class FakeConfig
{
	private $items = array();
	private $groups = array();

	public function setItem($key, $value)
	{
		$this->items[$key] = $value;
	}

	public function setGroup($group, array $items)
	{
		$this->groups[$group] = $items;
	}

	public function item($key, $index = '')
	{
		if ($index !== '' && isset($this->groups[$index]) && array_key_exists($key, $this->groups[$index])) {
			return $this->groups[$index][$key];
		}

		return array_key_exists($key, $this->items) ? $this->items[$key] : NULL;
	}

	public function slash_item($key)
	{
		$value = $this->item($key);
		if ($value === NULL || $value === '') {
			return '';
		}

		return rtrim((string) $value, '/') . '/';
	}

	public function base_url($uri = '', $protocol = NULL)
	{
		$base = $this->slash_item('base_url');
		return $base . ltrim((string) $uri, '/');
	}
}

class FakeInput
{
	public $methodValue = 'GET';
	public $ipAddress = '127.0.0.1';
	public $userAgent = 'PHPUnit';
	public $headers = array();
	public $postData = array();
	public $getData = array();

	public function method($upper = FALSE)
	{
		return $upper ? strtoupper($this->methodValue) : strtolower($this->methodValue);
	}

	public function ip_address()
	{
		return $this->ipAddress;
	}

	public function user_agent()
	{
		return $this->userAgent;
	}

	public function get_request_header($name, $xss = TRUE)
	{
		return array_key_exists($name, $this->headers) ? $this->headers[$name] : NULL;
	}

	public function post($key = NULL, $xss = NULL)
	{
		if ($key === NULL) {
			return $this->postData;
		}

		return array_key_exists($key, $this->postData) ? $this->postData[$key] : NULL;
	}

	public function get($key = NULL, $xss = NULL)
	{
		if ($key === NULL) {
			return $this->getData;
		}

		return array_key_exists($key, $this->getData) ? $this->getData[$key] : NULL;
	}
}

class FakeSession
{
	public $flashdata = array();
	public $userdata = array();
	public $unsetCalls = array();
	public $regenerateCalls = array();

	public function set_flashdata($key, $value)
	{
		$this->flashdata[$key] = $value;
	}

	public function userdata($key)
	{
		return array_key_exists($key, $this->userdata) ? $this->userdata[$key] : NULL;
	}

	public function set_userdata(array $data)
	{
		$this->userdata = array_merge($this->userdata, $data);
	}

	public function unset_userdata(array $keys)
	{
		$this->unsetCalls[] = $keys;
		foreach ($keys as $key) {
			unset($this->userdata[$key]);
		}
	}

	public function sess_regenerate($destroy = FALSE)
	{
		$this->regenerateCalls[] = (bool) $destroy;
	}
}

class FakeOutput
{
	public $contentType;
	public $statusCode;
	public $output;

	public function set_content_type($type, $charset = 'utf-8')
	{
		$this->contentType = array($type, $charset);
		return $this;
	}

	public function set_status_header($code)
	{
		$this->statusCode = (int) $code;
		return $this;
	}

	public function set_output($output)
	{
		$this->output = $output;
		return $this;
	}
}

class FakeUri
{
	public $uriString = '';

	public function uri_string()
	{
		return $this->uriString;
	}
}

class FakeCache
{
	public $store = array();

	public function get($key)
	{
		return array_key_exists($key, $this->store) ? $this->store[$key] : FALSE;
	}

	public function save($key, $value, $ttl = 60)
	{
		$this->store[$key] = $value;
		return TRUE;
	}

	public function delete($key)
	{
		$exists = array_key_exists($key, $this->store);
		unset($this->store[$key]);
		return $exists;
	}
}

class FakeService
{
	public $calls = array();
	private $returns = array();

	public function setReturn($method, $value)
	{
		$this->returns[$method] = $value;
	}

	public function callCount($method)
	{
		return isset($this->calls[$method]) ? count($this->calls[$method]) : 0;
	}

	public function lastCall($method)
	{
		if (empty($this->calls[$method])) {
			return NULL;
		}

		return $this->calls[$method][count($this->calls[$method]) - 1];
	}

	public function __call($method, array $arguments)
	{
		$this->calls[$method][] = $arguments;
		return array_key_exists($method, $this->returns) ? $this->returns[$method] : NULL;
	}
}

class FakeFormValidation extends FakeService
{
	public function error_array()
	{
		return array();
	}
}

class TestCiContainer
{
	public $load;
	public $db;
	public $config;
	public $input;
	public $session;
	public $output;
	public $uri;
	public $cache;

	public function __construct()
	{
		$this->db = new FakeDb();
		$this->config = new FakeConfig();
		$this->input = new FakeInput();
		$this->session = new FakeSession();
		$this->output = new FakeOutput();
		$this->uri = new FakeUri();
		$this->cache = new FakeCache();
		$this->load = new FakeLoader($this);
	}
}