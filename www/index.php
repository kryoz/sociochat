<?php
use SocioChat\DI;
use SocioChat\DIBuilder;
use SocioChat\Message\Lang;
use Zend\Config\Config;

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

$js = '
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
		<form action="" method="post">
			<input type="email" class="form-control" placeholder="<?=$lang->getPhrase('Email')?>" id="login-name">
			<input type="password" class="form-control" autocomplete="on" placeholder="<?=$lang->getPhrase('Password')?>" id="login-password">
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
