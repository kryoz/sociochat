<?php
use SocioChat\Clients\Channel;
use SocioChat\Clients\ChannelsCollection;
use SocioChat\DI;

$dumperCallback = function () use ($config) {
	$logger = DI::get()->getLogger();
	$logger->info('Dumping chat log to file', ['CHATLOG']);
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
					$line .= '<img src="' . $info[Channel::AVATAR_THUMB] . '"></div>';
				} else {
					$line .= '<div class="user-avatar"><span class="glyphicon glyphicon-user"></span></div>';
				}

				$line .= ' <div class="nickname" title="[' . $response[Channel::TIME] . '] ' . $info[Channel::TIM] . '">' . $response[Channel::FROM_NAME] . '</div>';
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

	$fn = ROOT . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'sitemap.xml';

	if (!$fh = fopen($fn, 'w')) {
		$logger->err('Unable to open file ' . $fn . ' to dump!');
		return;
	}

	$date = date('Y-m-d');
	$xml = <<< EOD
<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>
    <url>
        <loc>https://sociochat.me/</loc>
        <lastmod>$date</lastmod>
    </url>
    <url>
        <loc>https://sociochat.me/faq.php</loc>
        <lastmod>2014-09-12</lastmod>
    </url>
</urlset>
EOD;
	fputs($fh, $xml);
	fclose($fh);
};

$timer = $loop->addPeriodicTimer($config->chatlog->interval, $dumperCallback);