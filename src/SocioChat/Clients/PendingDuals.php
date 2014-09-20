<?php

namespace SocioChat\Clients;

use Core\Utils\WrongArgumentException;
use SocioChat\DI;
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;
use Core\TSingleton;

class PendingDuals
{
	use TSingleton;

	private $dualsMap = [
		TimEnum::EIE => TimEnum::LSI,
		TimEnum::LSI => TimEnum::EIE,

		TimEnum::EII => TimEnum::LSE,
		TimEnum::LSE => TimEnum::EII,

		TimEnum::LII => TimEnum::ESE,
		TimEnum::ESE => TimEnum::LII,

		TimEnum::SLI => TimEnum::IEE,
		TimEnum::IEE => TimEnum::SLI,

		TimEnum::SLE => TimEnum::IEI,
		TimEnum::IEI => TimEnum::SLE,

		TimEnum::ILE => TimEnum::SEI,
		TimEnum::SEI => TimEnum::ILE,

		TimEnum::ESI => TimEnum::LIE,
		TimEnum::LIE => TimEnum::ESI,

		TimEnum::SEE => TimEnum::ILI,
		TimEnum::ILI => TimEnum::SEE,
	];
	private $boysQueue = [];
    private $girlsQueue = [];


	public function matchDual(User $user)
	{
		if ($user->isInPrivateChat() || !$this->isCorrectUser($user)) {
 			return false;
		}

		$tim = $user->getProperties()->getTim();

		if (!$this->dualExists($user)) {
			$this->register($user);
			return false;
		}

		$queue = $this->getDualQueue($user)[$this->getDualTim($tim)];

		$queue = array_flip($queue);
		$userId = $queue[1];

		$this->deleteByUser(DI::get()->getUsers()->getClientById($userId));

		return $userId;
	}

	public function deleteByUser(User $user)
	{
        if (!$this->isCorrectUser($user)) {
            return [];
        }

		foreach ($this->getQueue($user) as $timId => &$data) {
			if (isset($data[$user->getId()])) {
				unset($this->getQueue($user)[$timId][$user->getId()]);

				if (empty($data)) {
					unset($this->getQueue($user)[$timId]);
				} else {
					$this->recalcQueue($data);
				}
				return true;
			}
		}
	}

	public function getUserPosition(User $user)
	{
        if (!$this->isCorrectUser($user)) {
            return [];
        }
		$tim = $user->getProperties()->getTim();

		if (isset($this->getQueue($user)[$tim->getId()])) {
			if (isset($this->getQueue($user)[$tim->getId()][$user->getId()])) {
				return $this->getQueue($user)[$tim->getId()][$user->getId()] ;
			}
		}
		return false;
	}

	public function getUsersByTim(User $user)
	{
        if (!$this->isCorrectUser($user)) {
            return [];
        }
        $tim = $user->getProperties()->getTim();
		return isset($this->getQueue($user)[$tim->getId()]) ? array_keys($this->getQueue($user)[$tim->getId()]) : [];
	}

	public function getUsersByDual(User $user)
	{
        if (!$this->isCorrectUser($user)) {
            return [];
        }
        $tim = $user->getProperties()->getTim();
		return isset($this->getDualQueue($user)[$this->getDualTim($tim)]) ? array_keys($this->getDualQueue($user)[$this->getDualTim($tim)]) : [];
	}

	public function getDualTim(TimEnum $tim)
	{
		return isset($this->dualsMap[$tim->getId()]) ? $this->dualsMap[$tim->getId()] : null;
	}

	protected function register(User $user)
	{
		$tim = $user->getProperties()->getTim();

		if (!isset($this->getQueue($user)[$tim->getId()])) {
            $this->getQueue($user)[$tim->getId()] = [];
		}
        $this->getQueue($user)[$tim->getId()][$user->getId()] = count($this->getQueue($user)[$tim->getId()])+1;
	}

	private function recalcQueue(array &$data)
	{
		asort($data);
		$i = 1;
		foreach ($data as $userId => $num) {
			$data[$userId] = $i;
			$i++;
		}
	}

    private function dualExists(User $user)
    {
        return isset($this->getDualQueue($user)[$this->getDualTim($user->getProperties()->getTim())]);
    }

    /**
     * @param User $user
     * @throws WrongArgumentException
     * @return array
     */
    private function &getQueue(User $user)
    {
        $sex = $user->getProperties()->getSex()->getId();
        if ($sex == SexEnum::FEMALE) {
            return $this->girlsQueue;
        } elseif ($sex == SexEnum::MALE) {
            return $this->boysQueue;
        }

        throw new WrongArgumentException('User sex cannot be anonymous here! UserId = '.$user->getId());
    }

    /**
     * @param User $user
     * @throws WrongArgumentException
     * @return array
     */
    private function &getDualQueue(User $user)
    {
        $sex = $user->getProperties()->getSex()->getId();
        if ($sex == SexEnum::FEMALE) {
            return $this->boysQueue;
        } elseif ($sex == SexEnum::MALE) {
            return $this->girlsQueue;
        }

        throw new WrongArgumentException('User sex cannot be anonymous here! UserId = '.$user->getId());
    }

    private function isCorrectUser(User $user)
    {
        return $user->getProperties()->getTim()->getId() != TimEnum::ANY && $user->getProperties()->getSex()->getId() != SexEnum::ANONYM;
    }
} 
