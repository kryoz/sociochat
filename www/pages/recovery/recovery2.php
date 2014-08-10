<?php
if (!defined('ROOT')) {
	die('not allowed');
}
$title = 'Письмо отправлено';
require_once dirname(__DIR__).DIRECTORY_SEPARATOR."header.php";
?>
<body>
	<div class="container" id="wrapper">
		<div class="navbar navbar-default">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span><<span class="glyphicon glyphicon-cog"></span>
					</button>
					<a href="#chat" class="navbar-brand navbar-left tab-panel" data-toggle="tab">СоциоЧат</a>
				</div>
				<div class="collapse navbar-collapse">
					<ul role="navigation" class="nav navbar-nav">
						<li>
							<a href="http://vk.com/topic-66015624_29370149" target="_blank" class="tip" title="Ответы на частые вопросы"><span class="glyphicon glyphicon-question-sign"></span> ЧаВо</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				Восстановление пароля
			</div>
			<div class="panel-body">
				<p>На ваш почтовый ящик <?=$email?> отправлено письмо с необходимыми данными для восстановления. Оно будет действительно только в течении часа.</p>
				<p>Теперь это окно можно закрыть.</p>
			</div>
		</div>
	</div>
</body>
</html>