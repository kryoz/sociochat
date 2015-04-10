<?php
use SocioChat\DAO\ActivationsDAO;
use SocioChat\DAO\UserDAO;
use SocioChat\DI;
use SocioChat\DIBuilder;
use Core\Form\Form;
use SocioChat\Forms\Rules;
use Zend\Config\Config;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);
$config = $container->get('config');
/* @var $config Config */

$email = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;
$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : null;

$validation = null;

if (!$email || !$code) {
    exit;
}

$form = new Form();
$form->import($_REQUEST);
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

if (!$validation) {
	$heading = 'Ошибка!';
	$message = 'Email невалиден.';
	require_once "pages/common_page.php";
	exit;
}


$activation = ActivationsDAO::create();
$result = $activation->getActivation($email, $code);
$activation = $result[0];
/* @var $activation ActivationsDAO */

if (!$activation->getId() || $activation->getIsUsed()) {
	$heading = 'Ошибка!';
	$message = 'Извините, но код невалиден.';
	require_once "pages/common_page.php";
    exit;
}

if ($activation->getCode() != $code) {
	$heading = 'Ошибка!';
	$message = 'Извините, но код невалиден.';
	require_once "pages/common_page.php";
    exit;
}

if (strtotime($activation->getTimestamp()) + $config->activationTTL < time()) {
    $activation->setIsUsed(true);
    $activation->save();
	$heading = 'Ошибка!';
	$message = 'Извините, но валидационный код просрочен.';
    require_once "pages/common_page.php";
    exit;
}

$user = UserDAO::create()->getByEmail($email);
$props = \SocioChat\DAO\PropertiesDAO::create()->getByUserId($user->getId());

$props->setSubscription(false);
$props->save(false);

$activation->setIsUsed(true);
$activation->save();

$heading = 'Готово!';
$message = 'Ваша учётная запись удалена из рассылки!';
require_once "pages/common_page.php";
