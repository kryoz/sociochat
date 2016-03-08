<?php

namespace SocioChat;

use Core\BaseException;
use Core\Form\Form;
use Core\Memcache\Wrapper;
use Monolog\Logger;
use React\EventLoop\LoopInterface;
use SocioChat\Application\Chat;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\Server;
use SocioChat\Clients\Channel;
use SocioChat\Clients\ChannelsCollection;
use SocioChat\Clients\User;
use SocioChat\Message\MsgContainer;
use Zend\Config\Config;

class ServerStarter
{
    public function run()
    {
        $container = DI::get()->container();
        DIBuilder::setupNormal($container);
        $config = $container->get('config');
        /* @var $config Config */
        $logger = $container->get('logger');
        /* @var $logger Logger */

        ini_set("session.gc_maxlifetime", $config->session->lifetime);

        $this->checkDupProcess($logger);

        $app = new Chat();
        $loop = $container->get('eventloop');

        $socketServer = new Server($loop);
        $socketServer->listen($config->daemon->port, $config->daemon->host);

        $server = new IoServer(
            new HttpServer(new WsServer($app)),
            $socketServer
        );

        $logger->info("Starting chat server daemon on " . $config->daemon->host . ":" . $config->daemon->port, ['CHAT-SERVER']);

        $channels = $this->setupChannels();
        $memcache = DI::get()->getMemcache();

        $this->messageRestore($memcache, $channels, $logger);

        $loop->addPeriodicTimer($config->chatlog->memcacheInterval, $this->messageBackup($memcache, $channels, $config, $logger));
        $loop->addPeriodicTimer($config->chatlog->interval, $this->seoDumper($config, $logger));

        $loop->run();
    }

    /**
     * @param Logger $logger
     * @throws BaseException
     */
    private function checkDupProcess(Logger $logger)
    {
        $pidFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'chat-server.pid';

        if (file_exists($pidFile)) {
            $pid = file_get_contents($pidFile);
            if (file_exists("/proc/$pid")) {
                $error = "Found already running daemon instance [pid = $pid], aborting.";
                $logger->error($error);
                throw new BaseException($error);
            }

            unlink($pidFile);
        }

        $fh = fopen($pidFile, 'w');
        if ($fh) {
            fwrite($fh, getmypid());
        }
        fclose($fh);

        cli_set_process_title('sociochat.me');
    }

    private function setupChannels()
    {
        return ChannelsCollection::get()
            ->addChannel(
                (new Channel(1, 'Гостевая', false))->setOnJoinRule(function (Form $form, User $user) {
                    return true;
                })
            )
            ->addChannel(
                (new Channel(2, 'Храм просветленных', false))->setOnJoinRule(function (Form $form, User $user) {
                    if ($user->getProperties()->getKarma() <= 1) {
                        $form->markWrong('channelId', 'Вход разрешён только пользователям с положительной кармой!');
                        return false;
                    }
                    return true;
                })
            );
    }

    private function messageRestore(Wrapper $memcache, ChannelsCollection $channels, Logger $logger)
    {
        $logger->info('Restoring history from memcache');
        $memcache->get('sociochat.channels', $json);

        if (!$list = json_decode($json, 1)) {
            return;
        }

        foreach ($list as $id => $channelInfo) {
            $channel = $channels->getChannelById($id);
            if (null === $channel) {
                $logger->info('Creating channel id = '.$id);
                $channel = new Channel($id, $channelInfo['name'], $channelInfo['isPrivate']);
                $channels->addChannel($channel);
            }

            $logger->info('Loading messages in channelId '.$id);
            $logger->debug(print_r($channelInfo['responses'], 1));

            if (!isset($channelInfo['responses'])) {
                continue;
            }
            foreach ($channelInfo['responses'] as $response) {
                $channel->pushRawResponse($response);
            }
            $channel->setLastMsgId($channelInfo['lastMsgId']);
        }
    }

    private function messageBackup(Wrapper $memcache, ChannelsCollection $channels, Config $config, Logger $logger)
    {
        return function () use ($config, $logger, $memcache, $channels) {
            $logger->debug('Dumping chat log to memcached');
            $memcache->set('sociochat.channels', json_encode($channels->exportChannels()));
        };
    }

    private function seoDumper(Config $config, Logger $logger)
    {
        return function () use ($config, $logger) {
            $logger->debug('Dumping chat log to file', ['CHATLOG']);
            $fn = ROOT . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'chatlog.txt';

            if (!$fh = fopen($fn, 'w')) {
                $logger->err('Unable to open file ' . $fn . ' to dump!');
                return;
            }

            $responses = ChannelsCollection::get()->getChannelById(1)->getHistory(0);

            foreach ($responses as $response) {
                if (!isset($response[Channel::TO_NAME])) {
                    if (isset($response[Channel::USER_INFO])) {
                        $info = $response[Channel::USER_INFO];
                        $line = '<div>';
                        if (isset($info[Channel::AVATAR_IMG])) {
                            $line .= '<div class="user-avatar" data-src="' . $info[Channel::AVATAR_IMG] . '">';
                            $line .= '<img src="'.$config->domain->protocol.$config->domain->web.'/'.$info[Channel::AVATAR_THUMB].'"></div>';
                        } else {
                            $line .= '<div class="user-avatar"><span class="glyphicon glyphicon-user"></span></div>';
                        }

                        $line .= ' <div class="nickname" title="['.$response[Channel::TIME].'] '.$info[Channel::TIM].'">'.$response[Channel::FROM_NAME].'</div>';
                    } else {
                        $line = '<div class="system">';
                    }

                    /** @var $msg MsgContainer */
                    $msg = $response[Channel::MSG];
                    $lang = DI::get()->container()->get('lang');
                    $lang->setLanguage('ru');
                    $line .= $msg->getMsg($lang);
                    $line .= "</div>\n";
                    fwrite($fh, $line);
                }
            }

            fclose($fh);

            $fn = ROOT . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'sitemap.xml';

            if (!$fh = fopen($fn, 'w')) {
                $logger->err('Unable to open file ' . $fn . ' to dump!');
                return;
            }

            $date = date('Y-m-d');
            $year = date('Y');
            $xml = "
<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>
    <url>
        <loc>{$config->domain->protocol}{$config->domain->web}</loc>
        <lastmod>$date</lastmod>
    </url>
    <url>
        <loc>{$config->domain->protocol}{$config->domain->web}/faq.php</loc>
        <lastmod>{$year}-09-12</lastmod>
    </url>
</urlset>";
            fwrite($fh, $xml);
            fclose($fh);
        };
    }
}