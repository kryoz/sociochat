<?php

namespace SocioChat\Cron;

interface CronService
{
	/**
	 * @param array $options
	 */
	public function setup(array $options);

	/**
	 * @return boolean
	 */
	public function canRun();

	/**
	 * @return string|null
	 */
	public function getLockName();

	/**
	 * @return string
	 */
	public function getHelp();

	public function run();
}
