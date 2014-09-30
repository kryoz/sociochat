<?php

namespace Core\DB;

use Core\BaseException;
use Core\DI;
use Monolog\Logger;
use PDO;
use PDOException;
use PDOStatement;
use Zend\Config\Config;

class DB
{
	const ERR_NO_CONNECTION = 'PDO connection fail';
	const ERR_INIT_FAIL = 'PDO init error';
	const ERR_SQL_ERROR = 'Query execution error';

	protected $scheme;
	protected $dbURL;
	protected $user;
	protected $pass;

	protected $isLogQueries = false;

	/**
	 * @var PDO
	 */
	protected $dbh;
	/**
	 * @var PDOStatement
	 */
	protected $result;

	public function __construct(Config $config)
	{
		$settings = $config->db;

		$this->scheme = $settings->scheme;
		$this->dbURL = "dbname=".$settings->name.";host=".$settings->host;
		$this->user = $settings->user;
		$this->pass = $settings->pass;
		$this->isLogQueries = $settings->logging;

		$this->init();
	}

	public function __destruct()
	{
		unset($this->dbh);
	}

	/**
	 * @param $sql
	 * @param array $params
	 * @param int $fetchFlags
	 * @param array $types
	 * @throws \PDOException
	 * @return array
	 */
	public function query($sql, array $params = [], $fetchFlags = PDO::FETCH_ASSOC, array $types = [])
	{
		$this->checkConnection();

		try {
			$sth = $this->dbh->prepare($sql);
			$this->bindParams($sth, $params, $types);
			$sth->execute();
			$this->logQuery($sql, $params);
			$result = $sth->fetchAll($fetchFlags);
			$sth->closeCursor();
		} catch (PDOException $e) {
			throw new PDOException(
				self::ERR_SQL_ERROR . ': ' . $e->getMessage()
				. "\n" . 'QUERY: ' . $sql . "\n" . 'PARAMS: ' . print_r($params, 1)
			);
		}

		return $result;
	}

	/**
	 * @param $fetchFlags
	 * @return mixed
	 * @throws PDOException
	 */
	public function fetchRow($fetchFlags = PDO::FETCH_ASSOC)
	{
		if (empty($this->result)) {
			return;
		}

		try {
			return $this->result->fetch($fetchFlags);
		} catch (PDOException $e) {
			throw new PDOException(self::ERR_SQL_ERROR . ': ' . $e->getMessage());
		}
	}

	/**
	 * @param $sql
	 * @param array $params
	 * @param string|null $sequence
	 * @param array $types
	 * @throws \PDOException
	 * @return string
	 */
	public function exec($sql, array $params = [], $sequence = null, array $types = [])
	{
		$this->checkConnection();
		try {
			$sth = $this->dbh->prepare($sql);
			$this->bindParams($sth, $params, $types);
			$sth->execute();
			$this->logQuery($sql, $params);
			$sth->closeCursor();
			unset($sth);
		} catch (PDOException $e) {
			throw new PDOException(
				self::ERR_SQL_ERROR . ': ' . $e->getMessage() . "\n" . 'QUERY: ' . $sql . "\n" . 'PARAMS: '
				. print_r($params, 1)
			);
		}

		return $this->dbh->lastInsertId($sequence);
	}

	public function begin()
	{
		$this->checkConnection();
		$this->dbh->beginTransaction();
	}

	public function commit()
	{
		$this->checkConnection();
		$this->dbh->commit();
	}

	/**
	 * Get DB PDO object
	 * @return PDO
	 */
	public function o()
	{
		$this->checkConnection();
		return $this->dbh;
	}

	protected function bindParams(PDOStatement $sth, array $params, array $types)
	{
		foreach ($params as $pName => $pVal) {
			$type = isset($types[$pName]) ? $types[$pName] : PDO::PARAM_STR;
			$sth->bindValue(':'.$pName, $pVal, $type);
		}
	}

	protected function checkConnection()
	{
		try {
			@$this->dbh->query('select 1');
		} catch (BaseException $e) {
			$this->init();
		}
	}

	protected function init()
	{
		try {
			$this->dbh = new PDO(
				$this->scheme . ':' . $this->dbURL,
				$this->user,
				$this->pass,
				[
					PDO::ATTR_PERSISTENT => true,
					1002 => "SET NAMES utf8",
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
				]
			);
		} catch (PDOException $e) {
			throw new PDOException(self::ERR_INIT_FAIL . ': ' . $e->getMessage().'; '.$this->scheme);
		}
	}

	private function logQuery($sql, array $params)
	{
		if ($this->isLogQueries) {
			/** @var $logger Logger */
			$logger = DI::get()->getLogger();
			$logger->info('SQL: '.$sql, $params);
		}
	}
}
