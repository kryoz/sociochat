<?php

namespace SocioChat\Cron;

use Core\Utils\DbQueryHelper;
use Silex\Application;
use SocioChat\DAO\MailQueueDAO;
use SocioChat\DAO\OnlineDAO;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DAO\SessionDAO;
use SocioChat\DAO\UserDAO;

class ServiceReminder implements CronService
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
        return 'Reminder';
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return "Reminder for people who have'nt come back long\n";
    }

    public function run(Application $app)
    {
        $config = $app['config'];
        $channelId = 1;
        $timeOut = 604800;

        foreach (SessionDAO::create()->getUsersToRemind(DbQueryHelper::timestamp2date(time() - $timeOut)) as $userId) {
            $user = UserDAO::create()->getById($userId);
            if (!$user->getEmail()) {
                continue;
            }

            $online = OnlineDAO::create();
            if ($online->isUserOnline($channelId, $userId)) {
                continue;
            }

            $prop = $user->getPropeties();
            if (!$prop->hasSubscription()) {
                continue;
            }

            if ((time() - $timeOut) < strtotime($prop->getOnlineNotificationLast())) {
                continue;
            }

            $prop->setOnlineNotificationLast(DbQueryHelper::timestamp2date());
            $prop->save(false);

            $onlineList = $online->getOnlineList($channelId);

            $msg = $app['twig']->render(
                'mail/reminder.twig',
                [
                    'hostUrl' => $config->domain->protocol . $config->domain->web,
                    'props' => $prop,
                    'onlineList' => $onlineList,
                    'onlineCount' => count($onlineList),
                ]
            );

            $message = MailQueueDAO::create();
            $message
                ->setEmail($user->getEmail())
                ->setTopic('Вы давно к нам не заходили')
                ->setMessage($msg);
            $message->save();

            $app['logger']->addInfo('Sent reminder to: '.$user->getEmail());
        }
    }
}
