<?php

use SocioChat\DAO\ActivationsDAO;
use SocioChat\DAO\UserDAO;
use SocioChat\DI;
use SocioChat\DIBuilder;
use Core\Form\Form;
use SocioChat\Forms\Rules;
use Core\Utils\PasswordUtils;
use SocioChat\Utils\Mail;
use Zend\Config\Config;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);
$config = $container->get('config');
/* @var $config Config */

session_start();

$email = isset($_REQUEST['email']) ? trim($_REQUEST['email']) : null;
$token = isset($_POST['token']) ? $_POST['token'] : null;
$sessionToken = isset($_SESSION['token']) ? $_SESSION['token'] : null;

function showFirst($email, $validation = null, Form $form = null)
{
    $token = PasswordUtils::get(20);
    $_SESSION['token'] = $token;
    require_once "pages/recovery/recovery1.php";
}

if (!$email || !$token) {
    showFirst($email);
    exit;
}

$form = new Form();
$form->import($_POST);
$form
    ->addRule(ActivationsDAO::EMAIL, Rules::email(), 'email в таком формате не может существовать.', 'emailPattern')
    ->addRule(
        ActivationsDAO::EMAIL,
        function ($val) {
            $user = UserDAO::create()->getByEmail($val);
            return (bool)$user->getId();
        },
        'Такой email не найден в системе.',
        'userSearch'
    );

$validation = $form->validate();

if (!$validation || $sessionToken != $token) {
    showFirst($email, $validation, $form);
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
        ActivationsDAO::EMAIL => $email,
        ActivationsDAO::CODE => substr(base64_encode(PasswordUtils::get(64)), 0, 64),
        ActivationsDAO::TIMESTAMP => date('Y-m-d H:i:s'),
        ActivationsDAO::USED => false
    ]
);
$activation->save();

$msg = "<h2>Восстановление пароля в СоциоЧате</h2>
<p>Была произведена процедура восстановления пароля с использованием вашего email.</p>
<p>Для подтверждения сброса пароля перейдите по <a href=\"" . $config->domain->protocol . $config->domain->web . "/activation.php?email=$email&code=" . $activation->getCode() . "\">ссылке</a></p>
<p>Данная ссылка действительна до " . date('Y-m-d H:i', time() + $config->activationTTL) . "</p>";

$mailer = \SocioChat\DAO\MailQueueDAO::create();
$mailer
	->setEmail($email)
	->setTopic('Sociochat.me - Восстановление пароля')
	->setMessage($msg);
$mailer->save();

require_once "pages/recovery/recovery2.php";
