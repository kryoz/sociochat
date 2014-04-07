<?php

namespace MyApp;

use PDO;
use PDOException;

class DB
{
	use TSingleton;

	const ERR_NO_CONNECTION = 'PDO connection fail';
	const ERR_INIT_FAIL = 'PDO init error';
	const ERR_SQL_ERROR = 'Query execution error';

	protected $scheme;
	protected $dbURL;
	protected $user;
	protected $pass;

	/**
	 * @var PDO
	 */
	protected $dbh;
	protected $result;

	public function __construct()
	{
		$this->scheme = DB_SCHEME;
		$this->dbURL = "dbname=".DB_NAME.";host=".DB_HOST;
		$this->user = DB_USER;
		$this->pass = DB_PASS;
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
	 * @return array
	 * @throws PDOException
	 */
	public function query($sql, array $params = array(), $fetchFlags = PDO::FETCH_ASSOC)
	{
		$this->checkConnection();

		try {
			$sth = $this->dbh->prepare($sql);
			$sth->execute($params);
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
	 * @return string
	 * @throws PDOException
	 */
	public function exec($sql, array $params = [])
	{
		$this->checkConnection();
		try {
			$sth = $this->dbh->prepare($sql);
			$sth->execute($params);
			$sth->closeCursor();
			unset($sth);
		} catch (PDOException $e) {
			throw new PDOException(
				self::ERR_SQL_ERROR . ': ' . $e->getMessage() . "\n" . 'QUERY: ' . $sql . "\n" . 'PARAMS: '
				. print_r($params, 1)
			);
		}

		return $this->dbh->lastInsertId();
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
	 * @return mixed
	 */
	public function o()
	{
		$this->checkConnection();
		return $this->dbh;
	}

	protected function checkConnection()
	{
		try {
			@$this->dbh->query('select 1');
        } catch (\Exception $e) {
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
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
				]
			);
		} catch (PDOException $e) {
			throw new PDOException(self::ERR_INIT_FAIL . ': ' . $e->getMessage());
		}
	}

}
