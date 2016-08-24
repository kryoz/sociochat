<?php

namespace SocioChat\Cron;

use Core\Utils\DbQueryHelper;
use Silex\Application;
use SocioChat\DAO\MailQueueDAO;
use SocioChat\DAO\OnlineDAO;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DAO\UserDAO;

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

    public function run(Application $app)
    {
        $config = $app['config'];
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

                $msg = $app['twig']->render(
                    'mail/online.twig',
                    [
                        'avatarDir' => $config->uploads->avatars->wwwfolder . DIRECTORY_SEPARATOR,
                        'hostUrl' => $config->domain->protocol . $config->domain->web,
                        'props' => PropertiesDAO::create(),
                        'onlineCount' => $onlineCount,
                        'onlineList' => $onlineList,
                        'limit' => $limit,
                    ]
                );


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
