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
$lifetime = $config->session->lifetime;
$domain = $config->domain->protocol.$config->domain->web;

setcookie('lang', $httpAcceptLanguage, time() + $lifetime, '/', '.'.$config->domain->web);

$js = '
	<meta name="fragment" content="!">
	<link rel="stylesheet" href="js/jcrop/jquery.Jcrop.min.css">
';
$version = $config->version;
require_once "pages/header.php";
?>
<body>
	<div class="container" id="wrapper">
		<header class="navbar navbar-default">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only"></span><span class="glyphicon glyphicon-collapse-down"></span>
					</button>
					<div class="navbar-left">
						<div class="navbar-brand">
							<a href="#chat" class="tab-panel" data-toggle="tab"><?=$lang->getPhrase('index.SocioChat')?></a>
						</div>
						<div class="dropdown navbar-cobrand">
							<a href="#profile" class="tip tab-panel space cog" data-toggle="tab" title="<?=$lang->getPhrase('index.ProfileTip')?>">
								<span class="glyphicon glyphicon-cog"></span>
							</a>
							<span class="space">
								<a href="#who" class="tip tab-panel" data-toggle="tab" title="<?=$lang->getPhrase('index.UserListTip')?>">
									<span class="badge"><span class="glyphicon glyphicon-user"></span> <span id="guest-counter">0</span></span>
								</a>
								<a href="#" data-toggle="dropdown" class="dropdown-toggle" title="<?=$lang->getPhrase('index.Channels')?>">
									<span id="channel-name"><?=$lang->getPhrase('index.Channels')?></span> <b class="caret"></b>
								</a>
								<ul class="dropdown-menu" id="menu-channels"></ul>
							</span>

							<a href="#login" class="tip tab-panel space" data-toggle="tab" title="<?=$lang->getPhrase('index.LoginTip')?>">
								<span class="glyphicon glyphicon-log-in"></span> <?=$lang->getPhrase('index.Login')?>
							</a>
						</div>

					</div>
				</div>
				<div class="collapse navbar-collapse">
					<ul role="navigation" class="nav navbar-nav">
						<li>
							<a href="#music" class="tip tab-panel" data-toggle="tab" title="<?=$lang->getPhrase('index.MusicTip')?>"><span class="glyphicon glyphicon-headphones"></span> <?=$lang->getPhrase('index.Music')?></a>
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
							<a href="/faq.php" target="_blank" class="tip" title="<?=$lang->getPhrase('index.FAQtip')?>"><span class="glyphicon glyphicon-question-sign"></span></a>
						</li>
					</ul>
				</div>
			</div>
		</header>

		<div class="tab-content tab-wrapper">

			<?php include "pages/index/chat.php"; ?>
			<?php include "pages/index/whois.php"; ?>
			<?php include "pages/index/music.php"; ?>
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
	<audio id="player" style="display: none"></audio>

	<script type="text/javascript" src="/js/jquery.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/jcrop/jquery.Jcrop.min.js"></script>
	<script type="text/javascript" src="js/notify.min.js"></script>
	<script type="text/javascript" src="js/ladda.js"></script>
	<script src="js/require.js"></script>
	<script type="text/javascript">
		require.config({
			baseUrl: 'js/app',
			urlArgs: 'bust=v'+<?=$version?>
		});

		define('config', function() {
			return {
				wsDomain: '<?=$config->domain->ws?>',
                webDomain: '<?=$config->domain->web?>',
				lifeTime: '<?=$lifetime?>'
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
