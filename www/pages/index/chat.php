<?php
if (!defined('ROOT')) {
	die('not allowed');
}
?>
<div id="chat" class="tab-pane active">
	<div class="panel panel-default chat-container">
		<div class="panel-body">
			<div id="log">
				<div class="system">
					<?=$lang->getPhrase('index.Connect')?>
				</div>
			</div>

		</div>
	</div>

	<div class="well well-sm message-input">
		<div class="row">
			<div class="col-xs-10">
				<div class="input-group">
					<div class="form-inline">
						<input tabindex="1" autocomplete="off" type="text" class="form-control" placeholder="<?=$lang->getPhrase('index.Message')?>" id="message">
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