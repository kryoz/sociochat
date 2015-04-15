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
	    <div class="notifications"></div>
	    <div class="col-md-4">
		    <div class="photo">
			    <div class="user-avatar"><span class="glyphicon glyphicon-user"></span></div>
		    </div>
			<br/>
		    <div class="btn-group btn-group-sm actions">
			    <a class="btn btn-default private" title="Пригласить в приват"><span class="glyphicon glyphicon-glass"></span></a>
			    <a class="btn btn-default ban" title="Игнор"><span class="glyphicon glyphicon-eye-close"></span></a>
			    <a class="btn btn-default unban" title="Разбан"><span class="glyphicon glyphicon-eye-open"></span></a>
			    <a class="btn btn-default note" title="Редактировать заметку"><span class="glyphicon glyphicon-edit"></span></a>
			    <a class="btn btn-default mail" title="Отправить сообщение на почту"><span class="glyphicon glyphicon-envelope"></span></a>
		    </div>
	    </div>
	    <div class="col-md-8">
		    <table class="table">
			    <tr>
				    <td><?= $lang->getPhrase('profile.TIM') ?></td>
				    <td class="tim"></td>
			    </tr>
			    <tr>
				    <td><?= $lang->getPhrase('profile.Sex') ?></td>
				    <td class="sex"></td>
			    </tr>
			    <tr>
				    <td><?= $lang->getPhrase('profile.City') ?></td>
				    <td class="city"></td>
			    </tr>
			    <tr>
				    <td><?= $lang->getPhrase('profile.Birth') ?></td>
				    <td class="birth"></td>
			    </tr>
			    <tr>
				    <td><?= $lang->getPhrase('profile.Names') ?></td>
				    <td class="names"></td>
			    </tr>
			    <tr>
				    <td><?= $lang->getPhrase('profile.Karma') ?></td>
				    <td>
					    <div class="btn-group btn-group-sm actions">
						    <a class="btn btn-danger karma-minus" title="-"><span class="glyphicon glyphicon-minus"></span></a>
						    <a class="btn btn-default karma" title="">0</a>
						    <a class="btn btn-success karma-plus" title="+"><span class="glyphicon glyphicon-plus"></span></a>
					    </div>
				    </td>
			    </tr>
			    <tr>
				    <td><?= $lang->getPhrase('profile.DateRegister') ?></td>
				    <td class="date-register"></td>
			    </tr>
			    <tr>
				    <td><?= $lang->getPhrase('profile.OnlineTime') ?></td>
				    <td class="online-time"></td>
			    </tr>
			    <tr>
				    <td><?= $lang->getPhrase('profile.WordRating') ?></td>
				    <td class="word-rating"></td>
			    </tr>
			    <tr>
				    <td><?= $lang->getPhrase('profile.RudeRating') ?></td>
				    <td class="rude-rating"></td>
			    </tr>
			    <tr>
				    <td><?= $lang->getPhrase('profile.MusicRating') ?></td>
				    <td class="music-rating"></td>
			    </tr>
			    <tr>
				    <td><?= $lang->getPhrase('profile.Note') ?></td>
				    <td class="note-data"></td>
			    </tr>
		    </table>

	    </div>
    </div>
	<div class="panel-footer">
		<a class="btn btn-block btn-success return-to-chat"><?= $lang->getPhrase('index.Return') ?></a>
	</div>
</div>
