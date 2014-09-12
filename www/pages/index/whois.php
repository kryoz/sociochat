<?php
if (!defined('ROOT')) {
	die('not allowed');
}
?>
<div class="panel panel-default tab-pane" id="who">
	<div class="panel-heading">
		<?=$lang->getPhrase('index.UserListTip')?>
	</div>
	<div class="panel-body">
		<table class="table table-striped" id="guests">
		</table>
	</div>
	<div class="panel-footer">
		<a class="btn btn-block btn-success return-to-chat"><?=$lang->getPhrase('index.Return')?></a>
	</div>
</div>