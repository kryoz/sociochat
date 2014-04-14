<?php
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;
use SocioChat\Message\Lang;
use Zend\Config\Config;

$DS = DIRECTORY_SEPARATOR;
$root = dirname(__DIR__);

require $root.$DS.'config.php';

/* @var $config Config */

$httpAcceptLanguage = mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
$lang = $container->get('lang')->setLangByCode($httpAcceptLanguage);
/* @var $lang Lang */
$lifetime = time() + $config->session->lifetime;

session_start();
setcookie(session_name(), session_id(), $lifetime, '/', '.'.$config->domain->web);
setcookie('lang', $httpAcceptLanguage, $lifetime, '/', '.'.$config->domain->web);

$js = '
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
						<!--<li class="dropdown">
							<a href="#publics" data-toggle="dropdown" class="dropdown-toggle">Паблики <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li><a href="#">1 <span class="glyphicon glyphicon-ok-sign"></span></a></li>
								<li><a href="#">2</a></li>
								<li><a href="#">3</a></li>
							</ul>
						</li>-->
						<li>
							<a href="http://vk.com/topic-66015624_29370149" target="_blank" class="tip" title="<?=$lang->getPhrase('index.FAQtip')?>"><span class="glyphicon glyphicon-question-sign"></span> <?=$lang->getPhrase('index.FAQ')?></a>
						</li>
						<li>
							<a href="#login" class="tip tab-panel" data-toggle="tab" title="<?=$lang->getPhrase('index.LoginTip')?>"><span class="glyphicon glyphicon-lock"></span> <?=$lang->getPhrase('index.Login')?></a>
						</li>
					</ul>
				</div>
			</div>
		</header>

		<div class="tab-content tab-wrapper">

			<div id="chat" class="tab-pane active">
				<div class="panel panel-default chat-container">
					<div class="panel-body">
						<div id="log">
							<div class="system"><?=$lang->getPhrase('index.Connect')?></div>
						</div>

					</div>
				</div>

				<div class="well well-sm message-input">
					<div class="row">
						<div class="col-xs-10">
							<div class="input-group">
								<div class="form-inline">
									<input tabindex="1" type="text" class="form-control" placeholder="<?=$lang->getPhrase('index.Message')?>" id="message">
								</div>
								<div class="input-group-btn">
									<button type="button" class="btn btn-info" id="address-reset" title="<?=$lang->getPhrase('index.AddressReset')?>" style="display: none"><span class="glyphicon glyphicon-remove"></span></button>
									<button type="submit" class="btn btn-warning" title="<?=$lang->getPhrase('index.Send')?>" id="send"><span class="glyphicon glyphicon-send"></span></button>
								</div>
							</div>
						</div>
						<div class="col-xs-2">
							<select class="form-control" id="address" data-id="">
								<option selected="selected" value=""><?=$lang->getPhrase('index.ToAll')?></option>
							</select>
						</div>
					</div>

				</div>
			</div>


			<div class="panel panel-default tab-pane" id="who">
				<div class="panel-heading">
					<?=$lang->getPhrase('index.UserListTip')?>
				</div>
				<div class="panel-body">
					<table class="table table-striped" id="guests">
						<tbody>
						</tbody>
					</table>
				</div>
				<div class="panel-footer">
					<a class="btn btn-block btn-success" onclick="App.returnToChat()"><?=$lang->getPhrase('index.Return')?></a>
				</div>
			</div>

			<div class="panel panel-default tab-pane" id="profile">
				<div class="panel-heading"><?=$lang->getPhrase('index.ProfileTip')?></div>
				<div class="panel-body">
					<div class="row btn-vert-block form-group">
						<div class="col-md-4 btn-vert-block">
							<input type="text" class="form-control" placeholder="<?=$lang->getPhrase('profile.Name')?>" id="nickname">
						</div>
						<div class="col-md-4 btn-vert-block">
							<select class="form-control" id="tim">
								<? foreach (TimEnum::getList() as $tim) { ?>
									<option value="<?=$tim->getId()?>"><?=$tim->getName()?></option>
								<? } ?>
							</select>
						</div>
						<div class="col-md-4 btn-vert-block">
							<select class="form-control" id="sex">
								<? foreach (SexEnum::getList() as $sex) { ?>
									<option value="<?=$sex->getId()?>"><?=$sex->getName()?></option>
								<? } ?>
							</select>
						</div>
					</div>
					<div class="row btn-vert-block">
						<div class="btn-vert-block col-sm-12">
							<a class="btn btn-block btn-success" id="set-profile-info"><?=$lang->getPhrase('Save')?></a>
						</div>
					</div>
				</div>
				<div class="panel-heading" style="border-top: 1px solid #ddd;"><a href="#" id="reg-info"><?=$lang->getPhrase('profile.Registration')?> <span class="glyphicon glyphicon-info-sign"></span></a></div>
				<div class="panel-body" id="reg-panel" style="display: none">
					<p><?=$lang->getPhrase('profile.RegistrationTip')?></p>
					<div class="row btn-vert-block form-group">
						<div class="btn-vert-block col-md-6">
							<input type="email" class="form-control" placeholder="<?=$lang->getPhrase('Email')?>" id="email">
						</div>
						<div class="btn-vert-block col-md-6">
							<input type="password" class="form-control" placeholder="<?=$lang->getPhrase('Password')?>" id="password">
						</div>
					</div>
					<div class="row btn-vert-block">
						<div class="col-md-12 btn-vert-block">
							<a class="btn btn-block btn-info" id="set-reg-info"><?=$lang->getPhrase('Save')?></a>
						</div>
					</div>
				</div>
			</div>

			<div class="panel panel-default tab-pane" id="login">
				<div class="panel-heading"><?=$lang->getPhrase('index.Login')?></div>
				<div class="panel-body">
					<form action="dummy.html" method="post" target="dummy">
						<p><?=$lang->getPhrase('login.Tip')?> <a href="recovery.php" target="_blank"><?=$lang->getPhrase('login.Forgot')?></a></p>
						<div class="row btn-vert-block form-group">
							<div class="btn-vert-block col-md-6">
								<span id="email_place_holder"></span>
							</div>
							<div class="btn-vert-block col-md-6">
								<span id="password_place_holder"></span>
							</div>
						</div>
						<div class="row btn-vert-block">
							<div class="col-md-12 btn-vert-block">
								<button type="submit" class="btn btn-success btn-block" id="do-login"><?=$lang->getPhrase('login.Auth')?></button>
							</div>
						</div>
					</form>
					<iframe src="dummy.html" name="dummy" style="display: none"></iframe>
				</div>
			</div>

		</div>

	</div>
	<div id="dont_forget" style="display: none">
		<form action="" method="post">
			<input type="email" class="form-control" placeholder="<?=$lang->getPhrase('Email')?>" id="login-name">
			<input type="password" class="form-control" placeholder="<?=$lang->getPhrase('Password')?>" id="login-password">
			<input type="submit" value="Login" id="dummy_submit"/>
		</form>
	</div>
	<script type="text/javascript" src="js/<?=$config->jsappfile?>?v=5"></script>
	<script type="text/javascript">
		$(function() {
			var app = new App.Init('<?=$config->domain->ws?>');

			$('#email_place_holder').replaceWith($('#login-name'));
			$('#password_place_holder').replaceWith($('#login-password'));
			$('#dont_forget').remove();

			$(".tip").tooltip({
				placement : 'bottom'
			});
		});
	</script>
	<? if ($config->metrika) {
		include_once "metrika.html";
	}?>
</body>
</html>
