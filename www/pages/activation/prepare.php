<?php
if (!defined('ROOT')) {
	die('not allowed');
}

$title = 'Восстановление пароля';
require_once dirname(__DIR__).DIRECTORY_SEPARATOR."header.php";
require_once "head.php";
?>
				<p>Задайте новый пароль.</p>
				<form action="activation.php" method="post">
					<input type="hidden" name="email" value="<?=$email?>"/>
					<input type="hidden" name="code" value="<?=$code?>"/>
					<?php if ($validation === false) { ?>
					<div class="has-error">
						<?php foreach ($form->getErrors() as $ruleName => $errMsg) { ?>
							<label class="control-label"><?=$errMsg?></label>
						<? } ?>
					</div>
					<?php } ?>
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