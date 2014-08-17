<?php
namespace Core\Cache;

interface ICache 
{
    public function has($scope);
    public function get($scope);
    public function set($scope, $data, $ttl);
    public function flush($scope, $regexp);
}
