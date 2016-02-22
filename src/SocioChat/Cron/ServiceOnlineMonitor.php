<?php

namespace SocioChat\Cron;

use Core\Utils\DbQueryHelper;
use SocioChat\DAO\MailQueueDAO;
use SocioChat\DAO\OnlineDAO;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DAO\UserDAO;
use SocioChat\DI;
use SocioChat\Utils\Mail;

class ServiceOnlineMonitor implements CronService
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
        return 'OnlineMonitor';
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return "Script to check online users\n";
    }

    public function run()
    {
	    $config = DI::get()->getConfig();
	    $timeOut = $config->onlineMonitoringTimeout;
	    // Let it be simple for a while
	    $channelId = 1;

	    /** @var PropertiesDAO $props */
	    foreach (PropertiesDAO::create()->getRegisteredList() as $props) {
		    if (!$limit = $props->getOnlineNotificationLimit()) {
			    continue;
		    }

		    $online = OnlineDAO::create();
		    if ($online->isUserOnline($channelId, $props->getUserId())) {
			    continue;
		    }

            $onlineList = $online->getOnlineList($channelId);
		    $onlineCount = count($onlineList);

		    if ((time() - $timeOut) < strtotime($props->getOnlineNotificationLast())) {
			    continue;
		    }
		    if ($onlineCount >= $limit) {
			    $user = UserDAO::create()->getById($props->getUserId());
			    $list = '';
                $config = DI::get()->getConfig();
                $avatarDir = $config->uploads->avatars->wwwfolder . DIRECTORY_SEPARATOR;
                $hostURL = $config->domain->protocol . $config->domain->web;
			    foreach ($onlineList as $userId => $userName) {
                    $guest = PropertiesDAO::create()->getByUserId($userId);
                    $avatarThumb = '<div style="width:36px; height: 36px; display: inline-block; background-color: #ccc"></div>';
                    if ($guest->getAvatarThumb()) {
                        $guestAvatarURL = $hostURL.'/'.$avatarDir.$guest->getAvatarThumb2X();
                        $avatarThumb = '<img src="'.$guestAvatarURL.'" style="width:36px; height: 36px;">';
                    }

				    $list .= "<div style='height:36px; margin-bottom: 12px'>$avatarThumb <span style='display: inline-block; height: 100%;line-height: 100%'>$userName</span></div>";
			    }

			    $msg = "<h2>Достижение заданного онлайна в Sociochat.me</h2>
<p>Вы получили данное письмо, потому что пожелали уведомить вас, когда в чате будет более $limit человек.</p>
<p>Сейчас на основном канале общается $onlineCount человек</p>
$list
<p><a href=\"" . $hostURL . "\">Присоединяйтесь</a>!</p>";

			    $message = MailQueueDAO::create();
			    $message
				    ->setEmail($user->getEmail())
				    ->setTopic('Заходите поговорить :)')
			        ->setMessage($msg);
			    $message->save();

			    $props->setOnlineNotificationLast(DbQueryHelper::timestamp2date());
			    $props->save(false);
		    }
        }
    }
}
