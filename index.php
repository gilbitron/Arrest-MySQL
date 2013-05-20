<?php

$dsn = '';
$clients = array
(
);

/**
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
* ArrestDB 1.0.0 (github.com/alixaxel/ArrestDB/)
* Copyright (c) 2013 Alix Axel <alix.axel@gmail.com>
**/

if (strcmp('cli', PHP_SAPI) === 0)
{
	exit('Arrest-DB should not be ran from CLI.');
}

header('Content-Type: application/json');

if ((empty($clients) !== true) && (in_array($_SERVER['REMOTE_ADDR'], (array) $clients) !== true))
{
	$result = array
	(
		'error' => array
		(
			'code' => 403,
			'status' => 'Forbidden',
		),
	);

	exit(json_encode($result, 480));
}

else if (ArrestDB::Query($dsn) === false)
{
	$result = array
	(
		'error' => array
		(
			'code' => 503,
			'status' => 'Service Unavailable',
		),
	);

	exit(json_encode($result, 480));
}

ArrestDB::Serve('GET', '/(#any)/(#num)?', function ($table, $id = null)
{
	$query = array
	(
		sprintf('SELECT * FROM `%s`', $table),
	);

	if (isset($id) === true)
	{
		$query[] = sprintf('WHERE `%s` = ? LIMIT 1', 'id');
	}

	else
	{
		if (isset($_GET['by']) === true)
		{
			if (isset($_GET['order']) !== true)
			{
				$_GET['order'] = 'ASC';
			}

			$query[] = sprintf('ORDER BY `%s` %s', $_GET['by'], $_GET['order']);
		}

		if (isset($_GET['limit']) === true)
		{
			$query[] = sprintf('LIMIT %u', $_GET['limit']);

			if (isset($_GET['offset']) === true)
			{
				$query[] = sprintf('OFFSET %u', $_GET['offset']);
			}
		}
	}

	$query = sprintf('%s;', implode(' ', $query));
	$result = (isset($id) === true) ? ArrestDB::Query($query, $id) : ArrestDB::Query($query);

	if ($result === false)
	{
		$result = array
		(
			'error' => array
			(
				'code' => 404,
				'status' => 'Not Found',
			),
		);
	}

	else if (empty($result) === true)
	{
		$result = array
		(
			'error' => array
			(
				'code' => 204,
				'status' => 'No Content',
			),
		);
	}

	else if (isset($id) === true)
	{
		$result = array_shift($result);
	}

	return json_encode($result, 480);
});

ArrestDB::Serve('POST', '/(#any)', function ($table)
{
	if (empty($_POST) === true)
	{
		$result = array
		(
			'error' => array
			(
				'code' => 204,
				'status' => 'No Content',
			),
		);
	}

	else
	{
		$data = array();

		foreach ($_POST as $key => $value)
		{
			$data[sprintf('`%s`', $key)] = '?';
		}

		$query = array
		(
			sprintf('INSERT INTO `%s` (%s) VALUES (%s)', $table, implode(', ', array_keys($data)), implode(', ', $data)),
		);

		$query = sprintf('%s;', implode(' ', $query));
		$result = ArrestDB::Query($query, $_POST);

		if ($result === false)
		{
			$result = array
			(
				'error' => array
				(
					'code' => 404,
					'status' => 'Not Found',
				),
			);
		}

		else
		{
			$result = array
			(
				'success' => array
				(
					'code' => 200,
					'status' => 'OK',
				),
			);
		}
	}

	return json_encode($result, 480);
});

ArrestDB::Serve('PUT', '/(#any)/(#num)', function ($table, $id)
{
	parse_str(file_get_contents('php://input'), $_PUT);

	if (empty($_PUT) === true)
	{
		$result = array
		(
			'error' => array
			(
				'code' => 204,
				'status' => 'No Content',
			),
		);
	}

	else
	{
		$data = array();

		foreach ($_PUT as $key => $value)
		{
			$data[$key] = sprintf('`%s` = ?', $key);
		}

		$query = array
		(
			sprintf('UPDATE `%s` SET %s WHERE `%s` = ?', $table, implode(', ', $data), 'id'),
		);

		$query = sprintf('%s;', implode(' ', $query));
		$result = ArrestDB::Query($query, $_PUT);

		if ($result === false)
		{
			$result = array
			(
				'error' => array
				(
					'code' => 404,
					'status' => 'Not Found',
				),
			);
		}

		else
		{
			$result = array
			(
				'success' => array
				(
					'code' => 200,
					'status' => 'OK',
				),
			);
		}
	}

	return json_encode($result, 480);
});

ArrestDB::Serve('DELETE', '/(#any)/(#num)', function ($table, $id)
{
	$query = array
	(
		sprintf('DELETE FROM `%s` WHERE `%s` = ?', $table, 'id'),
	);

	$query = sprintf('%s;', implode(' ', $query));
	$result = ArrestDB::Query($query, $id);

	if ($result === false)
	{
		$result = array
		(
			'error' => array
			(
				'code' => 404,
				'status' => 'Not Found',
			),
		);
	}

	else if (empty($result) === true)
	{
		$result = array
		(
			'error' => array
			(
				'code' => 204,
				'status' => 'No Content',
			),
		);
	}

	else
	{
		$result = array
		(
			'success' => array
			(
				'code' => 200,
				'status' => 'OK',
			),
		);
	}

	return json_encode($result, 480);
});

$result = array
(
	'error' => array
	(
		'code' => 400,
		'status' => 'Bad Request',
	),
);

exit(json_encode($result, 480));

class ArrestDB
{
	public static function Query($query = null)
	{
		static $db = null;
		static $result = array();

		try
		{
			if (isset($db, $query) === true)
			{
				if (empty($result[$hash = crc32($query)]) === true)
				{
					$result[$hash] = $db->prepare($query);
				}

				$data = array_slice(func_get_args(), 1);

				if (count($data, COUNT_RECURSIVE) > count($data))
				{
					$data = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($data)), false);
				}

				if ($result[$hash]->execute($data) === true)
				{
					switch (strstr($query, ' ', true))
					{
						case 'INSERT':
						case 'REPLACE':
							return $db->lastInsertId();

						case 'UPDATE':
						case 'DELETE':
							return $result[$hash]->rowCount();

						case 'SELECT':
						case 'EXPLAIN':
						case 'PRAGMA':
						case 'SHOW':
							return $result[$hash]->fetchAll();
					}

					return true;
				}

				return false;
			}

			else if (isset($query) === true)
			{
				$options = array
				(
					\PDO::ATTR_CASE => \PDO::CASE_NATURAL,
					\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
					\PDO::ATTR_EMULATE_PREPARES => false,
					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
					\PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
					\PDO::ATTR_STRINGIFY_FETCHES => false,
				);

				if (preg_match('~^sqlite://([[:print:]]++)$~i', $query, $dsn) > 0)
				{
					$options += array
					(
						\PDO::ATTR_TIMEOUT => 0,
					);

					$db = new \PDO(sprintf('sqlite:%s', $dsn[1]), null, null, $options);
					$pragmas = array
					(
						'busy_timeout' => '0',
						'cache_size' => '8192',
						'encoding' => '"UTF-8"',
						'foreign_keys' => 'ON',
						'journal_mode' => 'WAL',
						'journal_size_limit' => '67110000',
						'legacy_file_format' => 'OFF',
						'page_size' => '4096',
						'recursive_triggers' => 'ON',
						'secure_delete' => 'ON',
						'synchronous' => 'NORMAL',
						'temp_store' => 'MEMORY',
						'wal_autocheckpoint' => '4096',
					);

					if (strncasecmp('WIN', PHP_OS, 3) !== 0)
					{
						if (($page = intval(shell_exec('getconf PAGESIZE'))) > 0)
						{
							$pragmas['page_size'] = $page;
						}

						if ((is_file('/proc/meminfo') === true) && (is_readable('/proc/meminfo') === true))
						{
							$memory = 131072;

							if (is_resource($handle = fopen('/proc/meminfo', 'rb')) === true)
							{
								while (($line = fgets($handle, 1024)) !== false)
								{
									if (sscanf($line, 'MemTotal: %d kB', $memory) == 1)
									{
										$memory = round($memory / 131072) * 131072; break;
									}
								}

								fclose($handle);
							}

							$pragmas['cache_size'] = intval($memory * 0.25 / ($pragmas['page_size'] / 1024));
							$pragmas['wal_autocheckpoint'] = $pragmas['cache_size'] / 2;
						}
					}

					foreach ($pragmas as $key => $value)
					{
						$db->exec(sprintf('PRAGMA %s=%s;', $key, $value));
					}
				}

				else if (preg_match('~^mysql://(?:(.+?)(?::(.+?))?@)?([^/:@]++)(?::(\d++))?/(\w++)/?$~i', $query, $dsn) > 0)
				{
					$options += array
					(
						\PDO::ATTR_AUTOCOMMIT => true,
						\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8" COLLATE "utf8_general_ci", time_zone = "+00:00";',
						\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
					);

					$db = new \PDO(sprintf('mysql:host=%s;port=%s;dbname=%s', $dsn[3], $dsn[4], $dsn[5]), $dsn[1], $dsn[2], $options);
				}
			}
		}

		catch (\PDOException $e)
		{
			return false;
		}

		catch (\Exception $e)
		{
			return false;
		}

		return (isset($db) === true) ? $db : false;
	}

	public static function Serve($on = null, $route = null, $callback = null)
	{
		static $root = null;

		if (isset($_SERVER['REQUEST_METHOD']) !== true)
		{
			$_SERVER['REQUEST_METHOD'] = 'CLI';
		}

		if ((empty($on) === true) || (strcasecmp($on, $_SERVER['REQUEST_METHOD']) === 0))
		{
			if (is_null($root) === true)
			{
				$root = preg_replace('~/++~', '/', substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME'])) . '/');
			}

			if (preg_match('~^' . str_replace(array('#any', '#num'), array('[^/]++', '[0-9]++'), $route) . '~i', $root, $parts) > 0)
			{
				return (empty($callback) === true) ? true : exit(call_user_func_array($callback, array_slice($parts, 1)));
			}
		}

		return false;
	}
}
