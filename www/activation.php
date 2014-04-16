<?php
use SocioChat\DAO\ActivationsDAO;
use SocioChat\DAO\UserDAO;
use SocioChat\DI;
use SocioChat\DIBuilder;
use SocioChat\Forms\Form;
use SocioChat\Forms\Rules;
use Zend\Config\Config;

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);
$config = $container->get('config');
/* @var $config Config */

session_start();

$email = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;
$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : null;
$password = isset($_REQUEST['password']) ? $_REQUEST['password'] : null;
$passwordRepeat = isset($_REQUEST['password-repeat']) ? $_REQUEST['password-repeat'] : null;

$validation = null;

if (!$email || !$code) {
	require_once "pages/activation_error.php";
	exit;
}

$form = new Form();
$form->import($_REQUEST);
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

if (!$validation) {
	require_once "pages/activation_error.php";
	exit;
}


$activation = ActivationsDAO::create();
$result = $activation->getActivation($email, $code);
$activation = $result[0];
/* @var $activation ActivationsDAO */

if (!$activation->getId() || $activation->getIsUsed()) {
	require_once "pages/activation_error.php";
	exit;
}

if ($activation->getCode() != $code) {
	require_once "pages/activation_error.php";
	exit;
}

if (strtotime($activation->getTimestamp()) + $config->activationTTL < time()) {
	$activation->setIsUsed(true);
	$activation->save();
	require_once "pages/activation_error.php";
	exit;
}

if (!$password) {
	require_once "pages/activation_prepare.php";
	exit;
}

$form = new Form();
$form->import($_REQUEST);
$form
	->addRule('password', Rules::password(), 'Пароль должен быть от 8 до 20 символов')
	->addRule('password-repeat', Rules::password(), 'Пароль должен быть от 8 до 20 символов');

$validation = $form->validate();

if (!$validation) {
	require_once "pages/activation_prepare.php";
	exit;
}

if ($password != $passwordRepeat) {
	$validation = false;
	$form->markWrong('password', 'Введенные пароли не совпадают');
	require_once "pages/activation_prepare.php";
	exit;
}

$user = UserDAO::create()->getByEmail($email);
$user->setPassword(password_hash($password, PASSWORD_BCRYPT));
$user->save();

$activation->setIsUsed(true);
$activation->save();

require_once "pages/activation_success.php";
