define(function () {
	return {
		bindEvents: function ($this) {
			$this.pingTimer = setInterval(function () {
				if (!$this.connection || $this.connection.readyState != 1) {
					return;
				}
				var cmd = {
					subject: 'Ping'
				};

				$this.connection.send(JSON.stringify(cmd));
			}, 15000);

			// Address reset
			$this.domElems.addressReset.click(function () {
				$this.domElems.address.children().first().attr('selected', 'selected');
				$this.domElems.address.data('id', '');
				$this.domElems.addressReset.hide();
			});

			// Select
			$this.domElems.address.on('change', function () {
				$this.domElems.address.data('id', this.value);
				if (this.value) {
					$this.domElems.addressReset.show();
				} else {
					$this.domElems.addressReset.hide();
				}
			});

			$this.domElems.send.click(function () {
				$this.sendMessage();
				$this.domElems.inputMessage.focus();
			});

			$this.domElems.inputMessage.keypress(function (e) {
				var code = e.keyCode || e.which;
				var isEntered = code == 10 || code == 13;
				if ((e.ctrlKey && isEntered) || isEntered) {
					$this.sendMessage();
				}
			});

			$this.domElems.setProperties.click(function (e) {
				var command = {
					subject: 'Properties',
					action: 'submit',

					tim: $this.domElems.tim.val(),
					sex: $this.domElems.sex.val(),
					name: $this.domElems.nickname.val()
				}
				$this.send(command);
				$this.returnToChat();
			});

			$this.domElems.setRegInfo.click(function (e) {
				var command = {
					subject: 'Login',
					action: 'register',

					login: $this.domElems.email.val(),
					password: $this.domElems.password.val()
				}
				$this.send(command);
				$this.domElems.password.val('');
				$this.returnToChat();
			});

			$this.domElems.doLogin.click(function (e) {
				var command = {
					subject: 'Login',
					action: 'enter',

					login: $this.domElems.loginName.val(),
					password: $this.domElems.loginPassword.val()
				}
				$this.send(command);
				$this.domElems.loginPassword.val('');
				$this.returnToChat();
			});

			$(window).resize(function() {
				$this.scrollDown();
			});

			var checkManualScroll = function() {
				var container = $this.domElems.chat;

				if (container[0].scrollTop > (container[0].scrollHeight - 1.5*container.height())) {
					$this.isManualScrolling = false;
				}
			}

			$this.domElems.chat.on('touchstart', function () {
				$this.isManualScrolling = true;
			});

			$this.domElems.chat.on('touchstop', function () {
				checkManualScroll();
			});

			$this.domElems.chat.on('mousewheel', function() {
				$this.isManualScrolling = true;
				var timer = $.data(this, 'timer');
				clearTimeout($.data(this, 'timer'));
				$.data(this, 'timer', setTimeout(function() {
					checkManualScroll();
				}, 250));
			});
		},
		bindMenus: function($this) {
			$this.domElems.menuDualize.click(function (e) {
				var command = {
					subject: 'Channel',
					action: 'dualSearch'
				}
				$this.send(command);
			});

			$this.domElems.menuDualizeStop.click(function (e) {
				var command = {
					subject: 'MainChat'
				}
				$this.send(command);
			});

			$this.domElems.menuExit.click(function (e) {
				var command = {
					subject: 'MainChat'
				}
				$this.send(command);
			});

			$this.domElems.menuChat.click(function() {
				$this.returnToChat();
			});

			$this.domElems.regLink.click(function() {
				$this.domElems.regPanel.toggle();
			});

			$('.tab-panel').click(function(e) {
				e.preventDefault();
				$(this).tab('show');
			});

			$('.return-to-chat').click(function() {
				$this.returnToChat();
			});
		},
		AvatarUploadHandler: function($this) {
			var avatar = $this.domElems.avatar;
			var uploadButtonContainer = avatar.find('.do-upload');
			var response = avatar.find('.alert');
			var placeHolder = avatar.find('div.avatar-placeholder');
			var cropHolder = null;
			var jcropAPI = null;
			var dim = null;

			avatar.find('.upload').change(function() {
				var fileReader = new FileReader();
				var file = this.files[0];
				var image = new Image();


				if (jcropAPI) {
					jcropAPI.destroy();
				}

				cropHolder = $('<div></div>');
				cropHolder.attr('style', placeHolder.attr('style'));
				placeHolder.after(cropHolder);

				fileReader.onload = function(e) {
					placeHolder.hide();
					image.src = e.target.result;
				};

				fileReader.onloadend = function() {
					setTimeout(function() {cropHolder.Jcrop({
						bgColor: '#fff',
						minSize: [64, 64],
						maxSize: [0, 0],
						setSelect: [0, 0, cropHolder.width(), cropHolder.height()],
						aspectRatio: 1,
						onSelect: function (coords){
							dim = coords;
						}
					},function(){
						jcropAPI = this;
					});}, 500);

				}

				fileReader.readAsDataURL(file);

				image.style.maxWidth = 'inherit';
				image.style.maxHeight = 'inherit';

				cropHolder.html(image);
				uploadButtonContainer.data('file', file).show();
				response.removeClass('.alert-success').removeClass('.alert-danger').hide();
			});

			uploadButtonContainer.find('a').click(function () {
				var file = uploadButtonContainer.data('file');
				var xhr = new XMLHttpRequest();
				var formData = new FormData();
				var progressbarContainer = avatar.find('.progress');
				var progressbar = avatar.find('.progress-bar');
				var percentage = progressbar.find('.sr-only');

				var dim = jcropAPI.tellSelect();
				dim = {x: dim.x, y: dim.y, w: dim.w, h: dim.h, portW: cropHolder.width(), portH: cropHolder.height()};

				formData.append('img', file);
				formData.append('token', $this.token);
				formData.append('dim', JSON.stringify(dim));

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
					uploadButtonContainer.hide();
					response.addClass('alert-info').html('Фотография обрабатывается, подождите...').show();
				}

				xhr.onload = function(e) {
					response.removeClass('alert-info').removeClass('alert-danger');

					jcropAPI.destroy();
					placeHolder.show();

					try {
						var responseText = JSON.parse(e.target.responseText);
					} catch (e) {
						response.addClass('alert-danger').html('Файл слишком велик').show();
						return;
					}

					if (e.target.status != 200) {
						response.addClass('alert-danger').html(responseText.response).show();
						return;
					}

					response.addClass('alert-success').html(responseText.response).show();

					var command = {
						subject: 'Properties',
						action: 'uploadAvatar',
						image: responseText.image
					}
					$this.send(command);
				}

				xhr.open("POST", "upload.php");
				xhr.send(formData);
			});
		}
	}
});