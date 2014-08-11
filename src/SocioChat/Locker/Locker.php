<?php

namespace SocioChat\Locker;

interface Locker
{
	const DEFAULT_EXPIRE_TIME = 3600; //60 second * 60 minutes

	public function lock($key, $expireTime = self::DEFAULT_EXPIRE_TIME);

	public function isLocked($key);

	public function unlock($key);
}
