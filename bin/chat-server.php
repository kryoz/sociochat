<?php
use Monolog\Logger;
use Orno\Di\Container;
use SocioChat\Chat;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\Server;

require_once 'config.php';
/* @var $container Container */
$pidFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'chat-server.pid';
$logger = $container->get('logger');
/* @var $logger Logger */

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

$loop->run();
