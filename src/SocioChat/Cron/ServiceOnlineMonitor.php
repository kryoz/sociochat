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
	    /** @var OnlineDAO[] $online */
	    $online = OnlineDAO::create()->getAllList();
	    $config = DI::get()->getConfig();
	    $timeOut = $config->onlineMonitoringTimeout;

	    /** @var PropertiesDAO $props */
	    foreach (PropertiesDAO::create()->getRegisteredList() as $props) {
		    if (!$limit = $props->getOnlineNotificationLimit()) {
			    continue;
		    }

		    if (OnlineDAO::create()->getByUserId($props->getUserId())->getId()) {
			    continue;
		    }

		    if ((time() - $timeOut) < strtotime($props->getOnlineNotificationLast())) {
			    continue;
		    }
		    if (count($online) >= $limit) {
			    $user = UserDAO::create()->getById($props->getUserId());
			    $list = '';
			    foreach ($online as $item) {
				    $guest = PropertiesDAO::create()->getByUserId($item->getUserId());
				    $list .= '<li>'.$guest->getName().'</li>';
			    }

			    $msg = "<h2>Достижение заданного онлайна в SocioChat.Me</h2>
<p>Вы получили данное письмо, потому что пожелали уведомить вас, когда в чате будет более $limit человек.</p>
<p>Сейчас общаются:</p>
<ul>$list</ul>
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
