<?php

use MyApp\ChatConfig;
use MyApp\DAO\ActivationsDAO;
use MyApp\DAO\UserDAO;
use MyApp\Forms\Form;
use MyApp\Forms\Rules;
use MyApp\Utils\PasswordUtils;

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.php';
session_start();

$email = isset($_POST['email']) ? trim($_POST['email']) : null;
$token = isset($_POST['token']) ? $_POST['token'] : null;
$sessionToken = isset($_SESSION['token']) ? $_SESSION['token'] : null;
$validation = null;

if (!$email || !$token) {
	$token = PasswordUtils::get(20);
	$_SESSION['token'] = $token;
	require_once "pages/recovery1.php";
	exit;
}

$form = new Form();
$form->import($_POST);
$form
	->addRule('email', Rules::email(), 'email в таком формате не может существовать.', 'emailPattern')
	->addRule(
		'email',
		function($val) {
			$user = UserDAO::create()->getByEmail($val);
			return (bool) $user->getId();
		},
		'Такой email не найден в системе.',
		'userSearch'
	);

$validation = $form->validate();

if (!$validation || $sessionToken != $token) {
	$token = PasswordUtils::get(20);
	$_SESSION['token'] = $token;
	require_once "pages/recovery1.php";
	exit;
}


// Поиск прежних активаций и аннуляция
$activation = ActivationsDAO::create();
$activation->getByEmail($email);

if ($activation->getId() && !$activation->getIsUsed()) {
	$activation->setIsUsed(true);
	$activation->save();
}

// Делаем активационный код
$activation = ActivationsDAO::create();
$activation->fillParams(
	[
		'email' => $email,
		'code' => substr(base64_encode(PasswordUtils::get(64)), 0, 64),
		'timestamp' => date('Y-m-d H:i:s'),
		'used' => false
	]
);
$activation->save();

$adminEmail = 'webmaster@sociochat.ru';
$mailerName = 'СоциоЧат';
$headers  = "MIME-Version: 1.0 \n"
	."From: ".mb_encode_mimeheader($mailerName)
	."<".$adminEmail."> \n"
	."Reply-To: ".mb_encode_mimeheader($mailerName)
	."<".$adminEmail."> \n"
	."Content-Type: text/html;charset=UTF-8\n";

$msg = "<h2>Восстановление пароля в Социочате</h2>
<p>Была произведена процедура восстановления пароля с использованием вашего email.</p>
<p>Для подтверждения сброса пароля перейдите по <a href=\"https://".ChatConfig::get()->getConfig()->domain->web."/activation.php?email=$email&code=".$activation->getCode()."\">ссылке</a></p>
<p>Данная ссылка действительна до ".date('Y-m-d H:i', time() + 3600)."</p>";

mb_send_mail($email, 'SocioChat - Восстановление пароля', $msg, $headers);
require_once "pages/recovery2.php";