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
        <p><span class="glyphicon glyphicon-glass"></span> - Пригласить в приват</p>

        <p><span class="glyphicon glyphicon-eye-close"></span> / <span class="glyphicon glyphicon-eye-open"></span> -
            Игнор/убрать игнор</p>

        <p><span class="glyphicon glyphicon-edit"></span> - Редактировать заметку</p>

        <div class="table-responsive">
            <table class="table table-striped" id="guests">
            </table>
        </div>
    </div>
    <div class="panel-footer">
        <a class="btn btn-block btn-success return-to-chat"><?= $lang->getPhrase('index.Return') ?></a>
    </div>
</div>