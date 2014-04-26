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

	<div class="panel-heading"><?=$lang->getPhrase('profile.AvatarTip')?></div>
	<div class="panel-body" id="avatar">
		<div class="row btn-vert-block form-group">
			<div class="col-sm-12 btn-vert-block">
				<input type="file" class="form-control upload" accept="image/*" onchange="handleFile(this.files)" name="img">
			</div>
			<br>
			<div class="avatar-placeholder" style="max-width: 100%;height: auto"></div>

			<div class="progress progress-striped active" style="display: none">
				<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
					<span class="sr-only">0% Complete</span>
				</div>
			</div>
		</div>
		<div class="row btn-vert-block do-upload" style="display: none">
			<div class="btn-vert-block col-sm-12">
				<a class="btn btn-block btn-success" onclick="uploadFile()"><?=$lang->getPhrase('Save')?></a>
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

<script>
	var avatar = $('#avatar');
	var progressbarContainer = avatar.find('.progress');
	var progressbar = avatar.find('.progress-bar');
	var percentage = progressbar.find('.sr-only');
	var uploadButton = avatar.find('div.do-upload');

	function handleFile(files) {
		var fileReader = new FileReader();
		var file = files[0];
		var imageElem = document.createElement('img');

		fileReader.onload = (function(img) { return function(e) { img.src = e.target.result; }; })(imageElem);
		fileReader.readAsDataURL(file);

		avatar.find('div.avatar-placeholder').html(imageElem);
		uploadButton.data('file', file).show();
	}

	function uploadFile() {
		var file = avatar.find('.do-upload').data('file');
		var xhr = new XMLHttpRequest();
		var formData = new FormData();

		formData.append('img', file);
		formData.append('token', '<?=session_id()?>');

		xhr.upload.addEventListener("progress", function(e) {
			if (e.lengthComputable) {
				var percent = Math.round((e.loaded * 100) / e.total);
				progressbar.css('width', percent+'%').attr('aria-valuenow', percent)
				percentage.html(percent+"%");
			}
		}, false);

		xhr.upload.addEventListener("loadstart", function(e) {
			progressbarContainer.show();
			progressbar.css('width', '0%').attr('aria-valuenow', 0)
			percentage.html("0%");
		}, false);

		xhr.upload.addEventListener("load", function(e) {
			progressbarContainer.hide();
			uploadButton.hide();
		}, false);

		xhr.open("POST", "upload.php");
		xhr.send(formData);
	}
</script>