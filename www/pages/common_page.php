<?php
if (!defined('ROOT')) {
    die('not allowed');
}

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
						<a href="/faq.php" target="_blank" class="tip" title="Ответы на частые вопросы"><span
								class="glyphicon glyphicon-question-sign"></span> ЧаВо</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<?=$heading?>
		</div>
		<div class="panel-body">
		<?=$message?>
		</div>
	</div>
</div>
<script type="text/javascript" src="/js/jquery.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
</body>
</html>