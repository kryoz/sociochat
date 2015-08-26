<?php

namespace SocioChat\Utils;

use SocioChat\DI;

class Mail
{
	public function send($email, $topic, $msg)
	{
		$config = DI::get()->getConfig()->mail;
		$mailerName = $config->name;

		$headers = "MIME-Version: 1.0 \n"
			. "From: " . mb_encode_mimeheader($mailerName)
			. "<" . $config->adminEmail . "> \n"
			. "Reply-To: " . mb_encode_mimeheader($mailerName)
			. "<" . $config->adminEmail . "> \n"
			. "Content-Type: text/html;charset=UTF-8\n";

		mb_send_mail($email, $topic, $msg, $headers);
	}
}