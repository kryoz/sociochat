<?php

namespace SocioChat\Clients;

use SocioChat\Enum\TimEnum;
use SocioChat\Log;
use SocioChat\TSingleton;

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
	private $queue = [];


	public function matchDual(User $user)
	{
		if ($user->isInPrivateChat() || $user->getProperties()->getTim()->getId() == TimEnum::ANY) {
			return false;
		}

		$tim = $user->getProperties()->getTim();
		$dualExists = isset($this->queue[$this->getDualTim($tim)]);

		if (!$dualExists) {
			$this->register($user);
			return false;
		}

		$queue = $this->queue[$this->getDualTim($tim)];

		$queue = array_flip($queue);
		$userId = $queue[1];

		$this->deleteByUserId($userId);

		return $userId;
	}

	public function deleteByUserId($userId)
	{
		foreach ($this->queue as $timId => &$data) {
			if (isset($data[$userId])) {
				unset($this->queue[$timId][$userId]);

				if (empty($data)) {
					unset($this->queue[$timId]);
				} else {
					$this->recalcQueue($data);
				}
				return true;
			}
		}
	}

	public function getUserPosition(User $user)
	{
		$tim = $user->getProperties()->getTim();

		if (isset($this->queue[$tim->getId()])) {
			if (isset($this->queue[$tim->getId()][$user->getId()])) {
				return $this->queue[$tim->getId()][$user->getId()] ;
			}
		}
		return false;
	}

	public function getUsersByTim(TimEnum $tim)
	{
		return isset($this->queue[$tim->getId()]) ? array_keys($this->queue[$tim->getId()]) : [];
	}

	public function getUsersByDualTim(TimEnum $tim)
	{
		return isset($this->queue[$this->getDualTim($tim)]) ? array_keys($this->queue[$this->getDualTim($tim)]) : [];
	}

	public function getDualTim(TimEnum $tim)
	{
		return isset($this->dualsMap[$tim->getId()]) ? $this->dualsMap[$tim->getId()] : null;
	}

	protected function register(User $user)
	{
		$tim = $user->getProperties()->getTim();

		if (!isset($this->queue[$tim->getId()])) {
			$this->queue[$tim->getId()] = [];
		}
		$this->queue[$tim->getId()][$user->getId()] = count($this->queue[$tim->getId()])+1;
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
} 
