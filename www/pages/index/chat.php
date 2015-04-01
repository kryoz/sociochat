<?php
if (!defined('ROOT')) {
    die('not allowed');
}
?>
<div id="chat" class="tab-pane active">
    <div class="panel panel-default chat-container">
        <div class="panel-body">
	        <header class="post-item post-header"></header>
            <div id="log">
                <div class="system">
                    Подключаемся...
                </div>
                <?php
                if (isset($_GET['_escaped_fragment_'])) {
                    $fn = ROOT . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'chatlog.txt';
                    echo file_get_contents($fn);
                }
                ?>
            </div>
        </div>
    </div>

    <div class="well well-sm message-input">
        <div class="row">
            <div class="col-xs-10">
                <div class="input-group">
                    <div class="form-inline">
                        <textarea tabindex="1" class="form-control"
                               placeholder="<?= $lang->getPhrase('index.Message') ?>" id="message" maxlength="<?=$maxMsgLength?>"></textarea>

	                    <div id="charsLeft"></div>
                    </div>
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-info" id="address-reset"
                                title="<?= $lang->getPhrase('index.AddressReset') ?>" style="display: none"><span
                                class="glyphicon glyphicon-remove"></span></button>
                        <button type="submit" class="btn btn-warning" title="<?= $lang->getPhrase('index.Send') ?>"
                                id="send"><span class="glyphicon glyphicon-send"></span></button>
                    </div>
                </div>
            </div>
            <div class="col-xs-2">
                <select class="form-control" id="address" data-id="">
                    <option selected="selected" value=""><?= $lang->getPhrase('index.ToAll') ?></option>
                </select>
            </div>
        </div>

    </div>
</div>