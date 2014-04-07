<?php
use MyApp\Chat;
use MyApp\ChatConfig;
use MyApp\Log;
use MyApp\MightyLoop;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\Server;

require_once 'config.php';

$pidFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'chat-server.pid';
$logger = Log::get()->fetch();

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

$loop = MightyLoop::get()->fetch();
$webSock = new Server($loop);
$webSock->listen(8080, 'localhost');

$server = new IoServer(
	new HttpServer(new WsServer($app)),
	$webSock
);

$logger->info("Starting chat server daemon...");

$loop->run();
