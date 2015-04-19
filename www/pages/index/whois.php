<?php
if (!defined('ROOT')) {
    die('not allowed');
}
?>
<div class="panel panel-default tab-pane" id="who">
    <div class="panel-heading">
        <?= $lang->getPhrase('index.UserListTip') ?>
    </div>
    <div class="panel-body">
	    <div class="notifications"></div>
	    <p><?= $lang->getPhrase('who.ReferralTip')?></p>
	    <div class="input-group input-group-sm">
		    <span class="input-group-addon" id="sizing-addon3"><span class="glyphicon glyphicon-link"></span></span>
		    <input type="text" class="form-control" id="profile-ref-link" value="" readonly aria-describedby="sizing-addon3">
	    </div>
	    <br><br>
        <p><span class="glyphicon glyphicon-glass"></span> - <?= $lang->getPhrase('who.InviteTip')?>;

        <span class="glyphicon glyphicon-eye-close"></span> / <span class="glyphicon glyphicon-eye-open"></span> -
	        <?= $lang->getPhrase('who.ToggleBanTip')?></p>

        <div class="table-responsive">
            <table class="table table-striped" id="guests">
            </table>
        </div>
    </div>
    <div class="panel-footer">
        <a class="btn btn-block btn-success return-to-chat"><?= $lang->getPhrase('index.Return') ?></a>
    </div>
</div>