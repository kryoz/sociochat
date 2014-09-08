<?php
use Core\Form\Form;
use Monolog\Logger;
use SocioChat\Chat;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\Server;
use SocioChat\Clients\Channel;
use SocioChat\Clients\ChannelsCollection;
use Core\DI;
use SocioChat\Clients\User;
use SocioChat\DIBuilder;
use SocioChat\Message\MsgContainer;
use Zend\Config\Config;

set_error_handler(
	function ($errno, $errstr, $errfile, $errline) {
		$func = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
		$line = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['line'];
		echo "ERROR (calling {$func}() on l.$line) : $errstr</p>";
		return true;
	}
);

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);
$config = $container->get('config');
/* @var $config Config */
$logger = $container->get('logger');
/* @var $logger Logger */

ini_set("session.gc_maxlifetime", $config->session->lifetime);

$pidFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'chat-server.pid';

if (file_exists($pidFile)) {
	$pid = file_get_contents($pidFile);
	if (file_exists("/proc/$pid")) {
		$logger->error("Found already running daemon instance [pid = $pid], aborting.");
		exit(1);
	} else {
		unlink($pidFile);
	}
}

$fh = fopen($pidFile, 'w');
if ($fh) {
	fwrite($fh, getmypid());
}
fclose($fh);

$app = new Chat();

$loop = $container->get('eventloop');
$webSock = new Server($loop);
$webSock->listen($config->daemon->port, $config->daemon->host);

$server = new IoServer(
	new HttpServer(new WsServer($app)),
	$webSock
);

$logger->info("Starting chat server daemon on ".$config->daemon->host.":".$config->daemon->port, ['CHAT-SERVER']);

$reg = new Channel(2, 'Для зарегистрированных', false);
$reg->setOnJoinRule(function (Form $form, User $user) {
		if (!$user->isRegistered()) {
			$form->markWrong('channelId', 'Вход разрешён только зарегистрированным участникам');
		}

		return $user->isRegistered();
	}
);

$channels = ChannelsCollection::get()
	->addChannel(new Channel(1, 'Гостевая', false))
	->addChannel($reg);


$dumperCallback = function () use ($config) {
	$logger = DI::get()->getLogger();
	$logger->info('Dumping chat log', ['CHATLOG']);
	$fn = ROOT.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'chatlog.txt';

	if (!$fh = fopen($fn, 'w')) {
		$logger->err('Unable to open file '.$fn.' to dump!');
		return;
	}

	$responses = ChannelsCollection::get()->getChannelById(1)->getHistory(0);

	foreach ($responses as $response) {
		if (!isset($response[Channel::TO_NAME])) {
			if (isset($response[Channel::USER_INFO])) {
				$info = $response[Channel::USER_INFO];
				$line = '<div>';
				if (isset($info[Channel::AVATAR_IMG])) {
					$line .= '<div class="user-avatar" data-src="'.$info[Channel::AVATAR_IMG].'">';
					$line .= '<img src="'.$info[Channel::AVATAR_THUMB].'"></div>';
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
			$lang->setLangByCode('ru');
			$line .= $msg->getMsg($lang);
			$line .= "</div>\n";
			fputs($fh, $line);
		}
	}

	fclose($fh);
};

$timer = $loop->addPeriodicTimer($config->chatlog->interval, $dumperCallback);

$loop->run();
