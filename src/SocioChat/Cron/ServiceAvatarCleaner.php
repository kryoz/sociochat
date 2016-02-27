<?php

namespace SocioChat\Cron;

use Silex\Application;
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

    public function run(Application $app)
    {
        $dir = ROOT.DIRECTORY_SEPARATOR.$app['config']->uploads->avatars->dir.DIRECTORY_SEPARATOR;

	    $fileList = glob("{$dir}*.{jpg,png}", GLOB_BRACE);
	    $fileList = array_flip($fileList);

	    /** @var PropertiesDAO $userProp */
	    foreach (PropertiesDAO::create()->getListWithAvatars() as $userProp) {
		    if (file_exists($dir . $userProp->getAvatarImg())) {
			    unset($fileList[$dir . $userProp->getAvatarImg()]);
		    } else {
			    $userProp->setAvatarImg(null);
		    }
		    if (file_exists($dir . $userProp->getAvatarThumb())) {
			    unset($fileList[$dir . $userProp->getAvatarThumb()]);
		    } else {
			    $userProp->setAvatarImg(null);
		    }
		    if (file_exists($dir . $userProp->getAvatarThumb2X())) {
			    unset($fileList[$dir . $userProp->getAvatarThumb2X()]);
		    } else {
			    $userProp->setAvatarImg(null);
		    }
		    if (file_exists($dir . $userProp->getAvatarImg2X())) {
			    unset($fileList[$dir . $userProp->getAvatarImg2X()]);
		    } else {
			    $userProp->setAvatarImg(null);
		    }

		    if ($userProp->getAvatarImg() === null) {
			    $userProp->save();
		    }
	    }

	    $fileList = array_keys($fileList);

	    foreach ($fileList as $file) {
		    unlink($file);
	    }
    }
}
