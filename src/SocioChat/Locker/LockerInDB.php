<?php

namespace SocioChat\Locker;

use SocioChat\DAO\LockerDAO;
use Core\Utils\DbQueryHelper;

class LockerInDB implements Locker
{
    public function lock($key, $expireTime = self::DEFAULT_EXPIRE_TIME)
    {
        if ($this->isLocked($key)) {
            throw new AlreadyLockedException();
        }

        try {
            $locker = LockerDAO::create();
            $locker
                ->setKey($key)
                ->setTimestamp(DbQueryHelper::timestamp2date(time() + $expireTime));
            $locker->save();
        } catch (\PDOException $e) {
            throw new AlreadyLockedException($e->getMessage());
        }
    }

    public function isLocked($key)
    {
        if ($lock = LockerDAO::create()->getByKey($key)) {
            /* @var  $lock  LockerDAO */
            $isLocked = (time() - $lock->getTimestamp()) > static::DEFAULT_EXPIRE_TIME;
            if (!$isLocked) {
                $lock->dropById($lock->getId());
            }
            return $isLocked;
        }

        return false;
    }

    public function unlock($key)
    {
        if ($lock = LockerDAO::create()->getByKey($key)) {
            /** @var $lock LockerDAO */
            $lock->dropById($lock->getId());
        }
    }
}
