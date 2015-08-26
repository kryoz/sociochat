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
        $dir = ROOT.DIRECTORY_SEPARATOR.DI::get()->getConfig()->uploads->avatars->dir.DIRECTORY_SEPARATOR;

	    $fileList = glob("{$dir}*.{jpg,png}", GLOB_BRACE);
	    $fileList = array_flip($fileList);
		echo "Found images: ".count($fileList)."\n";

	    print_r($fileList);
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
			    echo "Fixed avatar link to null for ".$userProp->getName()."\n";
		    }
	    }

	    $fileList = array_keys($fileList);
		echo "Images to delete: ".count($fileList)."\n";

	    foreach ($fileList as $file) {
		    unlink($file);
	    }
    }
}
