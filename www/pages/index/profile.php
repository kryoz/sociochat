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

	<div class="panel-heading"><?=$lang->getPhrase('profile.AvatarTip')?> <?=$config->uploads->avatars->maxsize/1024?>KB</div>
	<div class="panel-body" id="avatar">
		<div class="row btn-vert-block form-group">
			<div class="col-sm-12 btn-vert-block">
				<span class="btn btn-default btn-file">
					<?=$lang->getPhrase('profile.Browse')?> <input type="file" class="upload" accept="image/*" onchange="handleFile(this.files)" name="img">
				</span>
			</div>
		</div>
		<div class="row btn-vert-block form-group">
			<div class="col-sm-12 btn-vert-block">
				<p><?=$lang->getPhrase('profile.PreviewMini')?></p>

				<div class="img-thumbnail avatar-placeholder-mini">

				</div>

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
	var uploadButton = avatar.find('.do-upload');
	var response = avatar.find('.alert');

	function handleFile(files) {
		var fileReader = new FileReader();
		var file = files[0];
		var image = document.createElement('img');
		var thumb = document.createElement('img');

		fileReader.onload = (function(img, thumb) {
			return function(e) {
				img.src = e.target.result;
				thumb.src = img.src;
			};
		})(image, thumb);

		fileReader.readAsDataURL(file);


		thumb.style.maxWidth = '<?=$config->uploads->avatars->thumbdim?>px';
		thumb.style.maxHeight = '<?=$config->uploads->avatars->thumbdim?>px';
		image.style.maxWidth = 'inherit';
		image.style.maxHeight = 'inherit';

		avatar.find('div.avatar-placeholder-mini').html(thumb);
		avatar.find('div.avatar-placeholder').html(image);
		uploadButton.data('file', file).show();
		response.removeClass('.alert-success').removeClass('.alert-danger').hide();
	}

	function uploadFile() {
		var file = avatar.find('.do-upload').data('file');
		var xhr = new XMLHttpRequest();
		var formData = new FormData();

		formData.append('img', file);
		formData.append('token', '<?=session_id()?>');

		xhr.upload.onprogress = function(e) {
			if (e.lengthComputable) {
				var percent = Math.round((e.loaded * 100) / e.total);
				progressbar.css('width', percent+'%').attr('aria-valuenow', percent)
				percentage.html(percent+"%");
			}
		};

		xhr.upload.onloadstart = function(e) {
			progressbarContainer.show();
			progressbar.css('width', '0%').attr('aria-valuenow', 0)
			percentage.html("0%");
		}

		xhr.upload.onload = function(e) {
			progressbarContainer.hide();
			uploadButton.hide();
		}
		xhr.onload = function(e) {
			var responseText = JSON.parse(e.target.responseText);

			if (e.target.status != 200) {
				response.addClass('alert-danger').html(responseText.response).show();
				return;
			}

			response.addClass('alert-success').html(responseText.response).show();
		}

		xhr.open("POST", "upload.php");
		xhr.send(formData);
	}
</script>