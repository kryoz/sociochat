<?php

namespace SocioChat\Cache;

class CacheApc implements ICache
{

	public function __construct()
	{
		if (!function_exists('apc_cache_info'))
			throw new CacheException(__CLASS__.' class error: APC module does not exist');
	}

	public function has($scope)
	{
		return apc_exists($scope);
	}

	/**
	 * 
	 * @param string $scope
	 * @return mixed 
	 */
	public function get($scope)
	{
		return apc_fetch($scope);
	}

	/**
	 *
	 * @param string $scope
	 * @param mixed $data
	 * @param int $ttl
	 * @return boolean
	 */
	public function set($scope, $data, $ttl)
	{
		return apc_store($scope, $data, $ttl);
	}

	/**
	 *
	 * @param string $scope
	 * @param bool $regular
	 * @return bool
	 */
	public function flush($scope, $regular = false)
	{
		if ($regular) {
			$success = true;
			foreach (new \APCIterator('user', $scope) as $counter) {
				$success = $success && apc_delete($counter['key']);
			}
			return $success;
		}
		else
			return apc_delete($scope);
	}

}
