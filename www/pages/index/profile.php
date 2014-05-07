<?php
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;

if (!defined('ROOT')) {
	die('not allowed');
}
?>
<div class="panel panel-default tab-pane" id="profile">
	<div class="panel-heading"><?=$lang->getPhrase('index.ProfileTip')?></div>
	<div class="panel-body">
		<p><?=$lang->getPhrase('profile.NameChangePolicy', floor($config->nameChangeFreq/3600))?></p>
		<div class="row btn-vert-block form-group">
			<div class="col-md-4 btn-vert-block">
				<input type="text" class="form-control" placeholder="<?=$lang->getPhrase('profile.Name')?>" id="nickname">
			</div>
			<div class="col-md-4 btn-vert-block">
				<select class="form-control" id="tim">
					<?php foreach (TimEnum::getList() as $tim) { ?>
						<option value="<?=$tim->getId()?>"><?=$tim->getName()?></option>
					<?php } ?>
				</select>
			</div>
			<div class="col-md-4 btn-vert-block">
				<select class="form-control" id="sex">
					<?php foreach (SexEnum::getList() as $sex) { ?>
						<option value="<?=$sex->getId()?>"><?=$sex->getName()?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="row btn-vert-block">
			<div class="btn-vert-block col-sm-12">
				<a class="btn btn-block btn-success" id="set-profile-info"><?=$lang->getPhrase('Save')?></a>
			</div>
		</div>
	</div>

	<div class="panel-heading" style="border-top: 1px solid #ddd;">
		<?=$lang->getPhrase('profile.AvatarTip')?> <?=$config->uploads->avatars->maxsize/1024?>KB
	</div>
	<div class="panel-body" id="avatar">
		<div class="row btn-vert-block form-group">
			<div class="col-sm-12 btn-vert-block">
				<span class="btn btn-default btn-file">
					<?=$lang->getPhrase('profile.Browse')?> <input type="file" class="upload" accept="image/*" name="img">
				</span>
			</div>
		</div>
		<div class="row btn-vert-block form-group">
			<div class="col-sm-12 btn-vert-block">
				<p><?=$lang->getPhrase('profile.PreviewMini')?></p>
				<div class="img-thumbnail avatar-placeholder-mini"></div>
				<p></p>
				<p><?=$lang->getPhrase('profile.Preview')?></p>
				<div class="img-thumbnail avatar-placeholder" style="max-width: 100%; max-height: <?=$config->uploads->avatars->maxdim?>px"></div></p>
			</div>
		</div>
		<div class="progress progress-striped active" style="display: none">
			<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
				<span class="sr-only">0% Complete</span>
			</div>
		</div>
		<div class="alert" style="display: none"></div>
		<div class="row btn-vert-block do-upload" style="display: none">
			<div class="btn-vert-block col-sm-12">
				<a class="btn btn-block btn-success"><?=$lang->getPhrase('Save')?></a>
			</div>
		</div>

	</div>

	<div class="panel-heading" style="border-top: 1px solid #ddd;"><a href="#" id="reg-info"><?=$lang->getPhrase('profile.Registration')?> <span class="glyphicon glyphicon-info-sign"></span></a></div>
	<div class="panel-body" id="reg-panel" style="display: none">
		<p><?=$lang->getPhrase('profile.RegistrationTip')?></p>
		<div class="row btn-vert-block form-group">
			<div class="btn-vert-block col-md-6">
				<input type="email" class="form-control" placeholder="<?=$lang->getPhrase('Email')?>" id="email">
			</div>
			<div class="btn-vert-block col-md-6">
				<input type="password" class="form-control" placeholder="<?=$lang->getPhrase('Password')?>" id="password">
			</div>
		</div>
		<div class="row btn-vert-block">
			<div class="col-md-12 btn-vert-block">
				<a class="btn btn-block btn-info" id="set-reg-info"><?=$lang->getPhrase('Save')?></a>
			</div>
		</div>
	</div>
</div>