<?php

namespace SocioChat\Cache;

class Cache implements ICache
{
	/**
	 * @var ICache
	 */
	private $cache;
	protected static $instance;
	
	public function __construct(ICache $cache)
	{
		$this->cache = $cache;
	}

	/**
	 * Check if key exists in cache
	 * @param string $scope key name
	 * @return bool
	 */
	public function has($scope)
	{
		return $this->cache->has($scope);
	}

	/**
	 * Get data from cache
	 * @param string $scope key name
	 * @return mixed
	 */
	public function get($scope)
	{
		return $this->cache->has($scope) ? $this->cache->get($scope) : false;
	}

	/**
	 * @param string $scope Cell name
	 * @param mixed $data
	 * @param int $ttl timeout to invalidate value
	 * @return bool
	 */
	public function set($scope, $data, $ttl = 3600)
	{
		return $this->cache->set($scope, $data, $ttl);
	}

	/**
	 * Erases cache cell
	 * @param string $scope cache cell name
	 * @param bool $regular is regexp?
	 */
	public function flush($scope, $regular = false)
	{
		$this->cache->flush($scope, $regular);
	}

}
