<?php
use MyApp\Enum\SexEnum;
use MyApp\Enum\TimEnum;
use Zend\Config\Config;
use Zend\Config\Reader\Ini;

$DS = DIRECTORY_SEPARATOR;
$root = dirname(dirname(__FILE__));

require $root.$DS.'vendor/autoload.php';

$confPath = $root.$DS.'conf'.$DS;

$reader = new Ini();
$config = new Config($reader->fromFile($confPath . 'default.ini'));
if (file_exists($confPath . 'local.ini')) {
	$config->merge(new Config($reader->fromFile($confPath . 'local.ini')));
}
$lifetime = time() + $config->session->lifetime;

session_start();
setcookie(session_name(), session_id(), $lifetime, '/', '.'.$config->domain->web);

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
						<a href="#chat" class="navbar-brand tab-panel" data-toggle="tab">СоциоЧат</a>
						<a href="#who" class="navbar-cobrand tip tab-panel" data-toggle="tab" title="Список пользователей чата"><span class="glyphicon glyphicon-user"></span> Кто в чате <span class="badge" id="guest-counter">0</span></a>
					</div>
				</div>
				<div class="collapse navbar-collapse">
					<ul role="navigation" class="nav navbar-nav">

						<li>
							<a href="#profile" class="tip tab-panel" data-toggle="tab" title="Настройки учетной записи"><span class="glyphicon glyphicon-cog"></span> Профиль</a>
						</li>

						<li>
							<a href="#" id="menu-dualize" class="tip" title="Дуал-рулетка!"><span class="glyphicon glyphicon-search"></span> Искать дуала</a>
						</li>
						<li style="display: none">
							<a href="#" id="menu-dualize-stop" class="tip" title="Выйти из режима поиска дуала"><span class="glyphicon glyphicon-remove"></span> Прекратить поиск</a>
						</li>
						<li style="display: none">
							<a href="#" id="menu-exit" class="tip" title="Закрыть чат и вернуться в общий канал"><span class="glyphicon glyphicon-home"></span> Вернуться в паблик</a>
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
							<a href="http://vk.com/topic-66015624_29370149" target="_blank" class="tip" title="Ответы на частые вопросы"><span class="glyphicon glyphicon-question-sign"></span> ЧаВо</a>
						</li>
						<li>
							<a href="#login" class="tip tab-panel" data-toggle="tab" title="Авторизация под своим аккаунтом"><span class="glyphicon glyphicon-lock"></span> Логин</a>
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
							<div class="system">Подключение...</div>
						</div>

					</div>
				</div>

				<div class="well well-sm message-input">
					<div class="row">
						<div class="col-xs-10">
							<div class="input-group">
								<div class="form-inline">
									<input tabindex="1" type="text" class="form-control" placeholder="Сообщение" id="message">
								</div>
								<div class="input-group-btn">
									<button type="button" class="btn btn-info" id="address-reset" title="Сброс адресата" style="display: none"><span class="glyphicon glyphicon-remove"></span></button>
									<button type="submit" class="btn btn-warning" title="Отправить" id="send"><span class="glyphicon glyphicon-send"></span></button>
								</div>
							</div>
						</div>
						<div class="col-xs-2">
							<select class="form-control" id="address" data-id="">
								<option selected="selected" value="">Всем</option>
							</select>
						</div>
					</div>

				</div>
			</div>


			<div class="panel panel-default tab-pane" id="who">
				<div class="panel-heading">
					Кто сейчас в чате
				</div>
				<div class="panel-body">
					<table class="table table-striped" id="guests">
						<tbody>
						</tbody>
					</table>
				</div>
				<div class="panel-footer">
					<a class="btn btn-block btn-success" onclick="App.returnToChat()">Вернуться</a>
				</div>
			</div>

			<div class="panel panel-default tab-pane" id="profile">
				<div class="panel-heading">Настройки профиля</div>
				<div class="panel-body">
					<div class="row btn-vert-block form-group">
						<div class="col-md-4 btn-vert-block">
							<input type="text" class="form-control" placeholder="Ваше имя" id="nickname">
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
							<a class="btn btn-block btn-success" id="set-profile-info">Настроить</a>
						</div>
					</div>
				</div>
				<div class="panel-heading" style="border-top: 1px solid #ddd;"><a href="#" id="reg-info">Регистрация учётной записи <span class="glyphicon glyphicon-info-sign"></span></a></div>
				<div class="panel-body" id="reg-panel" style="display: none">
					<p>Заполнять поля ниже не обязательно, но они позволят входить под своим именем с любого устройства в дальнейшем:</p>
					<p>Регистрируясь вы соглашаетесь на обработку своих персональных данных в соответствии с законодательством РФ (см. ЧаВо)</p>
					<div class="row btn-vert-block form-group">
						<div class="btn-vert-block col-md-6">
							<input type="email" class="form-control" placeholder="E-mail" id="email">
						</div>
						<div class="btn-vert-block col-md-6">
							<input type="password" class="form-control" placeholder="Пароль" id="password">
						</div>
					</div>
					<div class="row btn-vert-block">
						<div class="col-md-12 btn-vert-block">
							<a class="btn btn-block btn-info" id="set-reg-info">Сохранить</a>
						</div>
					</div>
				</div>
			</div>

			<div class="panel panel-default tab-pane" id="login">
				<div class="panel-heading">Авторизация</div>
				<div class="panel-body">
					<form action="dummy.html" method="post" target="dummy">
						<p>Если вы ранее сохраняли в настройках email и пароль, то можете зайти под своим профилем. <a href="recovery.php" target="_blank">Забыли пароль?</a></p>
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
								<button type="submit" class="btn btn-success btn-block" id="do-login">Войти</button>
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
			<input type="email" class="form-control" placeholder="E-mail" id="login-name">
			<input type="password" class="form-control" placeholder="Пароль" id="login-password">
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
