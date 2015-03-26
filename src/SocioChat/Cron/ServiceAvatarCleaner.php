<?php

namespace SocioChat\Cron;

use SocioChat\DAO\PropertiesDAO;
use SocioChat\DI;

class ServiceAvatarCleaner implements CronService
{

    /**
     * @param array $options
     */
    public function setup(array $options)
    {

    }

    /**
     * @return boolean
     */
    public function canRun()
    {
        return true;
    }

    /**
     * @return string|null
     */
    public function getLockName()
    {
        return 'AvatarCleaner';
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return "Script to clean old images\n";
    }

    public function run()
    {
        $dir = DI::get()->getConfig()->uploads->avatars->dir;

	    $fileList = glob("{$dir}/*.{jpg,png}", GLOB_BRACE);
	    $fileList = array_flip($fileList);

	    /** @var PropertiesDAO $userProp */
	    foreach (PropertiesDAO::create()->getListWithAvatars() as $userProp) {
		    unset($fileList[$dir . $userProp->getAvatarImg()]);
            unset($fileList[$dir . $userProp->getAvatarThumb()]);
            unset($fileList[$dir . $userProp->getAvatarThumb2X()]);
	    }

	    $fileList = array_keys($fileList);
		print_r($fileList);
	    foreach ($fileList as $file) {
		    unlink($file);
	    }
    }
}
