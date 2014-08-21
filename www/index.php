<?php
use Core\DI;
use SocioChat\DIBuilder;
use SocioChat\Message\Lang;
use Zend\Config\Config;


if (isset($_GET['_escaped_fragment_']) && $_GET['_escaped_fragment_'] == '') {
?>
	<p><b>СоциоЧат</b> - удобный, современный и бесплатный сайт знакомств, оптимизированный для мобильных устройств. Для начала общения вам не требуется регистрация!</p>
	<p>Мы предполагаем, что вы знакомы с соционикой, но если это не так, то вот о чём это.</p>
	<p><b>Соционика</b> - это концепция из сферы психологии, имеющая в основе типологию Юнга. </p>
	<p>Вы наверняка замечали, что с одними людьми нам бывает комфортно и легко находится общий язык, с другими возникают непримиримые противоречия в мировосприятии.
	Соционика позволяет вполне чётко обрисовать причины таких ситуаций.<br>
	Прежде всего, соционика постулирует информационную модель личности на уровне архетипа, то есть нечто фундаментальное, сохраняющее стабильность на протяжении всей жизни человека.<br>
	Опираясь на  эту модель можно спрогнозировать как сильные, так и слабые стороны индивида, рассмотреть взаимодействия с другими моделями - то есть с окружающими людьми.<br>
	Это также позволяет дать рекомендации по выбору профессиональной деятельности, выстроить стратегию общения с проблемными людьми и конечно же по поиску максимально совместимого партнёра.<br>
	В соционике существует только 16 типов личности или как ещё называют их "типов информационного метаболизма", сокращённо ТИМ. <br>
	Вы можете здесь возмутиться: ведь люди представляют собой гораздо более разнообразную массу. Но противоречия здесь нет. Во-первых, вспомним, что мы говорим о моделях, а модели всего лишь абстрактное упрощение реального, во-вторых, разглядеть архетип за огромными пластами жизненного опыта человека задача непростая. Можете представить ТИМ как скелет, а жизненный опыт в виде мягких тканей, покрывающих его.<br>
	Нельзя называть соционику наукой в строгом смысле слова. Тем не менее, неослабевающий интерес к ней на протяжении многих лет доказывает, что это не просто теория о сферических личностях в вакууме, а нечто находящее подтверждение в реальном мире.</p>
	<p>СоциоЧат, принимая во внимание эти знания, позволяет искать дуала или общаться в произвольной квадре</p>
	<p>Постепено вы обнаружите, что в нём реализовано множество приятных мелочей, выгодно отличающих СоциоЧат от конкурентов :)</p>
<?
	return;
}

$DS = DIRECTORY_SEPARATOR;
$root = dirname(__DIR__);

require $root.$DS.'config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);
$config = $container->get('config');
/* @var $config Config */

$httpAcceptLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : 'en';
$lang = $container->get('lang')->setLangByCode($httpAcceptLanguage);
/* @var $lang Lang */
$lifetime = time() + $config->session->lifetime;

session_start();
setcookie(session_name(), session_id(), $lifetime, '/', '.'.$config->domain->web);
setcookie('lang', $httpAcceptLanguage, $lifetime, '/', '.'.$config->domain->web);

$meta = '<meta name="fragment" content="!">
';

$js = '
	<link rel="stylesheet" href="js/jcrop/jquery.Jcrop.min.css">
	<script type="text/javascript" src="js/jcrop/jquery.Jcrop.min.js"></script>
	<script type="text/javascript" src="js/notify.min.js"></script>
';

require_once "pages/header.php";
?>
<body>
	<div class="container" id="wrapper">
		<header class="navbar navbar-default">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span><span class="glyphicon glyphicon-cog"></span>
					</button>
					<div class="navbar-left">
						<a href="#chat" class="navbar-brand tab-panel" data-toggle="tab"><?=$lang->getPhrase('index.SocioChat')?></a>
						<a href="#who" class="navbar-cobrand tip tab-panel" data-toggle="tab" title="<?=$lang->getPhrase('index.UserListTip')?>"><span class="glyphicon glyphicon-user"></span> <?=$lang->getPhrase('index.UserList')?> <span class="badge" id="guest-counter">0</span></a>
					</div>
				</div>
				<div class="collapse navbar-collapse">
					<ul role="navigation" class="nav navbar-nav">
						<li class="dropdown">
							<a href="#" data-toggle="dropdown" class="dropdown-toggle" title="<?=$lang->getPhrase('index.Channels')?>"><span class="glyphicon glyphicon-th-list"></span> <?=$lang->getPhrase('index.Channels')?> <b class="caret"></b></a>
							<ul class="dropdown-menu" id="menu-channels">

							</ul>
						</li>

						<li>
							<a href="#profile" class="tip tab-panel" data-toggle="tab" title="<?=$lang->getPhrase('index.ProfileTip')?>"><span class="glyphicon glyphicon-cog"></span> <?=$lang->getPhrase('index.Profile')?></a>
						</li>

						<li>
							<a href="#" id="menu-dualize" class="tip" title="<?=$lang->getPhrase('index.StartDualSearchTip')?>"><span class="glyphicon glyphicon-search"></span> <?=$lang->getPhrase('index.StartDualSearch')?></a>
						</li>
						<li style="display: none">
							<a href="#" id="menu-dualize-stop" class="tip" title="<?=$lang->getPhrase('index.StopDualSearchTip')?>"><span class="glyphicon glyphicon-remove"></span> <?=$lang->getPhrase('index.StopDualSearch')?></a>
						</li>
						<li style="display: none">
							<a href="#" id="menu-exit" class="tip" title="<?=$lang->getPhrase('index.ReturnToPublicTip')?>"><span class="glyphicon glyphicon-home"></span> <?=$lang->getPhrase('index.ReturnToPublic')?></a>
						</li>

						<li>
							<a href="/faq.php" target="_blank" class="tip" title="<?=$lang->getPhrase('index.FAQtip')?>"><span class="glyphicon glyphicon-question-sign"></span> <?=$lang->getPhrase('index.FAQ')?></a>
						</li>
						<li>
							<a href="#login" class="tip tab-panel" data-toggle="tab" title="<?=$lang->getPhrase('index.LoginTip')?>"><span class="glyphicon glyphicon-lock"></span> <?=$lang->getPhrase('index.Login')?></a>
						</li>
					</ul>
				</div>
			</div>
		</header>

		<div class="tab-content tab-wrapper">

			<?php include "pages/index/chat.php"; ?>
			<?php include "pages/index/whois.php"; ?>
			<?php include "pages/index/profile.php"; ?>
			<?php include "pages/index/login.php"; ?>

		</div>

	</div>
	<div id="dont_forget" style="display: none">
		<form action="" method="post" autocomplete="on">
			<input type="email" required class="form-control" autocomplete="on" placeholder="<?=$lang->getPhrase('Email')?>" id="login-name">
			<input type="password" required class="form-control" autocomplete="on" placeholder="<?=$lang->getPhrase('Password')?>" id="login-password">
			<input type="submit" value="Login" id="dummy_submit"/>
		</form>
	</div>
	<script src="js/require.js"></script>
	<script type="text/javascript">
		require.config({
			baseUrl: 'js/app'
		});

		define('config', function() {
			return {
				wsDomain: '<?=$config->domain->ws?>',
				sessionId: '<?=session_id()?>'
			};
		});

		var App = requirejs(['./main']);

		$(function() {
			$('#email_place_holder').replaceWith($('#login-name'));
			$('#password_place_holder').replaceWith($('#login-password'));
			$('#dont_forget').remove();

			$(".tip").tooltip({
				placement : 'bottom'
			});
		});
	</script>
	<?php if ($config->metrika) {
		include_once "metrika.html";
	}?>
</body>
</html>
