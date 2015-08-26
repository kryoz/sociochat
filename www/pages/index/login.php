<?php
if (!defined('ROOT')) {
    die('not allowed');
}
?>
<div class="panel panel-default tab-pane" id="login">
    <div class="panel-heading"><?= $lang->getPhrase('index.Login') ?></div>
    <div class="panel-body">
        <form action="dummy.html" method="post" target="dummy" autocomplete="on">
            <p><?= $lang->getPhrase('login.Tip') ?> <a href="recovery.php"
                                                       target="_blank"><?= $lang->getPhrase('login.Forgot') ?></a></p>

            <div class="row btn-vert-block form-group">
                <div class="btn-vert-block col-md-6">
                    <input type="text" id="email_place_holder" autocomplete="on"/>
                </div>
                <div class="btn-vert-block col-md-6">
                    <input type="password" id="password_place_holder" autocomplete="on"/>
                </div>
            </div>
            <div class="row btn-vert-block">
                <div class="col-md-12 btn-vert-block">
                    <button type="submit" class="btn btn-success btn-block"
                            id="do-login"><?= $lang->getPhrase('login.Auth') ?></button>
                </div>
            </div>
        </form>
        <iframe src="dummy.html" name="dummy" style="display: none"></iframe>
    </div>
</div>