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
		    $onlineCount = $online->getOnlineCount($channelId);

		    if ((time() - $timeOut) < strtotime($props->getOnlineNotificationLast())) {
			    continue;
		    }
		    if ($onlineCount >= $limit) {
			    $user = UserDAO::create()->getById($props->getUserId());

			    $msg = "<h2>Достижение заданного онлайна в SocioChat.Me</h2>
<p>Вы получили данное письмо, потому что пожелали уведомить вас, когда в чате будет более $limit человек.</p>
<p>Сейчас на основном канале общается $onlineCount человек</p>
<p><a href=\"" . $config->domain->protocol . $config->domain->web . "\">Присоединяйтесь</a>!</p>";

			    $message = MailQueueDAO::create();
			    $message
				    ->setEmail($user->getEmail())
				    ->setTopic('SocioChat.Me - Заходите к нам!')
			        ->setMessage($msg);
			    $message->save();

			    $props->setOnlineNotificationLast(DbQueryHelper::timestamp2date());
			    $props->save(false);
		    }
        }
    }
}
