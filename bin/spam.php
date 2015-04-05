<?php
use Core\Utils\PasswordUtils;
use SocioChat\DAO\ActivationsDAO;
use SocioChat\DI;
use SocioChat\DIBuilder;
use Zend\Config\Config;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);
$config = $container->get('config');
/* @var $config Config */

$db = new \Core\DB\DB($config);
$list = $db->query("SELECT email, p.name, p.sex FROM users AS u
JOIN sessions AS s ON s.user_id = u.id
JOIN user_properties AS p ON u.id = p.user_id
WHERE access < '2015-03-01 00:00:00'
AND access > '2014-06-01 00:00:00'
AND email IS NOT NULL");

foreach ($list as $item) {
	$email = $item['email'];
	$name = $item['name'];

	$ending = $item['sex'] == 1 || $item['sex'] == 3 ? 'ой' : 'ая';

	$activation = ActivationsDAO::create();
	$activation->fillParams(
		[
			ActivationsDAO::EMAIL => $email,
			ActivationsDAO::CODE => substr(base64_encode(PasswordUtils::get(64)), 0, 64),
			ActivationsDAO::TIMESTAMP => date('Y-m-d H:i:s'),
			ActivationsDAO::USED => false
		]
	);
	$activation->save();

	$msg = "<h2>Возвращайтесь в СоциоЧат!</h2>
<p>Дорог$ending $name!</p><br>
<p>Мы очень давно вас не видели. За время вашего отсутствия появилось много новых интересных людей :)</p>
<p>Также произошло множество улучшений, сделавших общение более комфортным. (<a href=\"https://vk.com/sociochat\">Подробнее</a>)</p>
<p>Вы получили данное письмо, потому что однажды регистрировались на <a target=\"_blank\" href=\"" . $config->domain->protocol . $config->domain->web . "\">SocioChat.Me</a></p>
<p></p>
<p>Если вы забыли свой пароль, то можно восстановить <a target=\"_blank\" href=\"" . $config->domain->protocol . $config->domain->web . "/recovery.php?email=$email\">здесь</a></p>
<p>Если вы не желаете больше возвращаться, то как бы нам это не хотелось :( жмите <a target=\"_blank\" href=\"" . $config->domain->protocol . $config->domain->web . "/unsubsribe.php?email=$email&code=" . $activation->getCode() . "\">сюда</a></p>";

	$mailer = \SocioChat\DAO\MailQueueDAO::create();
	$mailer
		->setEmail($email)
		->setTopic('Вы давно не были на SocioChat.Me')
		->setMessage($msg);
	$mailer->save();
}

