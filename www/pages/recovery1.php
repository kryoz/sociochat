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
				<p>Введите ваш email, на который была произведена регистрация учётной записи.</p>
				<p>На него прийдет письмо с дальнейшими инструкциями для восстановления доступа.</p>
				<form action="recovery.php" method="post">
					<div class="row btn-vert-block form-group <?=$validation === false ? 'has-error' : ''?>">
						<div class="btn-vert-block col-md-6">
								<input type="text" class="form-control" placeholder="E-mail" name="email" id="email" value="<?=$email?>">
							<? if ($validation === false) {
									foreach ($form->getErrors() as $ruleName => $errMsg) {
								?>
								<label class="control-label" for="email"><?=$errMsg?></label>
							<?      }
								}
							?>
							<input type="hidden" name="token" value="<?=$token?>"/>
						</div>
						<div class="btn-vert-block col-md-6">
							<button type="submit" class="btn btn-block btn-success">Выслать</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>