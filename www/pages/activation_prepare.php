<?php
$title = 'Восстановление пароля';
require_once "header.php";
?>
<body>
	<div class="container" id="wrapper">
		<div class="navbar navbar-default">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span><span class="glyphicon glyphicon-cog"></span>
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
				<p>Задайте новый пароль.</p>
				<form action="activation.php" method="post">
					<input type="hidden" name="email" value="<?=$email?>"/>
					<input type="hidden" name="code" value="<?=$code?>"/>
					<? if ($validation === false) { ?>
					<div class="has-error">
						<? foreach ($form->getErrors() as $ruleName => $errMsg) { ?>
							<label class="control-label"><?=$errMsg?></label>
						<? } ?>
					</div>
					<? } ?>
					<div class="row btn-vert-block form-group <?=$validation === false ? 'has-error' : ''?>">
						<div class="btn-vert-block col-md-6">
							<input type="password" class="form-control" placeholder="Пароль" name="password" id="password">
						</div>
						<div class="btn-vert-block col-md-6">
							<input type="password" class="form-control" placeholder="Повторите пароль" name="password-repeat" id="password-repeat">
						</div>
					</div>
					<button type="submit" class="btn btn-block btn-success">Сменить</button>
				</form>
			</div>
		</div>
	</div>
</body>
</html>