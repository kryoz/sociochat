<?php
if (!defined('ROOT')) {
	die('not allowed');
}

$title = 'Ошибка';
require_once dirname(__DIR__).DIRECTORY_SEPARATOR."header.php";
require_once "head.php";
?>

				<h3>Ошибка!</h3>
				<p>Либо неправильный формат ссылки, либо активация просрочена.</p>
				<p>Повторите процедуру сначала.</p>
			</div>
		</div>
	</div>
	<script type="text/javascript" src="/js/jquery.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
</body>
</html>