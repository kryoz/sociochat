<?php
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;
use SocioChat\Forms\Rules;

if (!defined('ROOT')) {
    die('not allowed');
}
?>
<div class="panel panel-default tab-pane" id="user-details">
    <div class="panel-heading name" style="font-weight: bold"></div>
    <div class="panel-body">
	    <div class="col-md-4 photo">
		    <div class="user-avatar"><span class="glyphicon glyphicon-user"></span></div>
	    </div>
	    <div class="col-md-8">
		    <div class="btn-group btn-group-sm ilb actions">
			    <a class="btn btn-default private" title="Пригласить в приват"><span class="glyphicon glyphicon-glass"></span></a>
			    <a class="btn btn-default ban" title="Игнор"><span class="glyphicon glyphicon-eye-close"></span></a>
			    <a class="btn btn-default unban" title="Разбан"><span class="glyphicon glyphicon-eye-open"></span></a>
			    <a class="btn btn-default note" title="Редактировать заметку"><span class="glyphicon glyphicon-edit"></span></a>
			    <a class="btn btn-default mail" title="Отправить сообщение"><span class="glyphicon glyphicon-envelope"></span></a>
		    </div>

		    <table>
			    <tr>
				    <td style="font-weight: bold"><?= $lang->getPhrase('profile.TIM') ?></td>
				    <td class="tim"></td>
			    </tr>
			    <tr>
				    <td style="font-weight: bold"><?= $lang->getPhrase('profile.Sex') ?></td>
				    <td class="sex"></td>
			    </tr>
			    <tr>
				    <td style="font-weight: bold"><?= $lang->getPhrase('profile.Birth') ?></td>
				    <td class="birth"></td>
			    </tr>
			    <tr>
				    <td style="font-weight: bold"><?= $lang->getPhrase('profile.Note') ?></td>
				    <td class="note-data"></td>
			    </tr>
		    </table>

	    </div>
    </div>
	<div class="panel-footer">
		<a class="btn btn-block btn-success return-to-chat"><?= $lang->getPhrase('index.Return') ?></a>
	</div>
</div>
