define(function() {
	return {
		process: function(json, $this) {
			var getAvatar = function (user) {
				var text = '<div class="user-avatar"';
				if (user && user.avatarThumb) {
					text += ' data-src="'+user.avatarImg+'">';
					text += '<img src="'+ $this.getImgUrl(user.avatarThumb) +'">';
				} else {
					text += '>';
					text += '<span class="glyphicon glyphicon-user"></span>';
				}

				return text+'</div>';
			}

			var getSexClass = function(user) {
				var colorClass = null;
				var sex = user ? user.sex : 'Аноним';
				switch (sex) {
					case 'Женщина' :
						colorClass = 'female'; break;
					case 'Аноним' :
						colorClass = 'anonym'; break;
					case 'Мужчина' :
						colorClass = 'male'; break;
					default:
						colorClass = '';
				}
				return colorClass;
			}

			var handleGuests = function(json) {
				if (json.guests) {
					$this.guests = json.guests;
					var guests = $this.guests;

					$this.domElems.guestList.empty();
					$this.domElems.address.empty();

					var guestHMTL = '';
					var guestDropdownHTML = '<option value="">Всем</option>';

					for (var i in guests) {
						var guest = guests[i];

						guestDropdownHTML += '<option value="'+guest.user_id+'">'+guest.name+'</option>';

						var colorClass = null;
						if (guest.sex == 'Мужчина') {
							colorClass = 'info';
						} else if (guest.sex == 'Женщина') {
							colorClass = 'danger';
						} else if (guest.sex == 'Аноним') {
							colorClass = 'active';
						} else if (guest.banned) {
							colorClass = 'warning';
						}

						guestHMTL += '<tr class="'+colorClass+'">';
						guestHMTL += '<td>' + getAvatar(guest) + ' <span class="user-name">' + guest.name + '</span></td><td>'+guest.tim+'</td>';
						guestHMTL += '<td><div class="pull-right btn-group btn-group-sm ilb">';

						//guestHMTL += '<a class="btn btn-default unban">Заметка</a>';

						if (guest.banned) {
							guestHMTL += '<a class="btn btn-default unban">Разбан</a>';
						} else {
							guestHMTL += '<a class="btn btn-default invite">Приват</a>';
							guestHMTL += '<a class="btn btn-default ban">Бан</a>';
						}

						guestHMTL +='</div></td></tr>';
					}

					$this.domElems.guestList.append(guestHMTL);
					$this.domElems.address.append(guestDropdownHTML);

					$this.domElems.address.find('option[value='+$this.domElems.address.data('id')+']').attr('selected', 'selected');

					$this.domElems.guestList.find('.ban').click(function() {
						var userName = $(this).parentsUntil('#guests tbody').find('.user-name').text();

						var command = {
							subject: 'Blacklist',
							action: 'ban',
							user_id: $this.getUserInfo(userName).user_id
						}
						$this.send(command);
						$this.returnToChat();
					});

					$this.domElems.guestList.find('.unban').click(function() {
						var userName = $(this).parentsUntil('#guests tbody').find('.user-name').text();

						var command = {
							subject: 'Blacklist',
							action: 'unban',
							user_id: $this.getUserInfo(userName).user_id
						}
						$this.send(command);
						$this.returnToChat();
					});

					$this.domElems.guestList.find('.invite').click(function() {
						var userName = $(this).parentsUntil('#guests tbody').find('.user-name').text();
						$this.togglePrivate($this.getUserInfo(userName).user_id);
					});

					$this.guestCount = guests.length;
					$this.domElems.guestCounter.text($this.guestCount);
				}
			}

			var handleOwnProperties = function() {
				if (json.name) {
					$this.domElems.nickname.val(json.name);
					$this.ownName = json.name;
				}

				if (json.sex) {
					$this.domElems.sex.val(json.sex);
					$this.ownSex = json.sex == 2;
				}

				if (json.tim) {
					$this.domElems.tim.val(json.tim);
				}

				if (json.email) {
					$this.domElems.email.val(json.email);
				}

				if (json.avatarImg) {
					var image = document.createElement('img');
					image.src = json.avatarImg;
					image.style.maxWidth = 'inherit';
					image.style.maxHeight = 'inherit';
					$this.domElems.avatar.find('div.avatar-placeholder').html(image);
				}

				if (json.avatarThumb) {
					var thumb = document.createElement('img');
					thumb.src = json.avatarThumb;
					$this.domElems.avatar.find('div.avatar-placeholder-mini').html(thumb);
				}
			}

			var handleDualChat = function() {
				if (json.dualChat == 'match') {
					$this.notify('Найден ваш собеседник!', 'Поздоровайтесь :)', 'private');
					$this.domElems.menuDualize.hide();
					$this.domElems.menuDualizeStop.hide();
					$this.domElems.menuExit.parent().attr('style', '');
					$this.domElems.menuExit.show();
				} else if (json.dualChat == 'exit') {
					$this.domElems.menuDualize.show();
					$this.domElems.menuDualizeStop.hide();
					$this.domElems.menuExit.hide();
				} else if (json.dualChat == 'init') {
					$this.domElems.menuDualize.hide();
					$this.domElems.menuDualizeStop.parent().attr('style', '');
					$this.domElems.menuDualizeStop.show();
				}
			}

			var handleMessage = function(response) {
				if (!response.msg) {
					return;
				}
				var msg = '';
				var msgCSStype = '';

				if (response.lastMsgId) {
					$this.lastMsgId = response.lastMsgId;
				}

				if (response.fromName) {
					var fromUser = response.userInfo ? response.userInfo : $this.getUserInfo(response.fromName);

					if ($this.chatLastFrom != response.fromName) {
						msg += getAvatar(fromUser)+' ';
						msg += '<div class="nickname ' + getSexClass(fromUser) + '" title="['+ response.time+'] ' + (fromUser ? fromUser.tim : '') + '">'+ response.fromName +'</div>';
					} else {
						msgCSStype = 'repeat';
					}
					if (response.toName) {
						var toUser = $this.getUserInfo(response.toName);
						var toWho = 'вас';

						if (fromUser && fromUser.name == $this.ownName) {
							$this.notify(response.msg, response.fromName, 'private', 5000);
							toWho = toUser.name;
						}

						msg += '<div class="private"><b>[приватно для '+toWho+']</b> '
						msg += messageParsers(response.msg) + '</div>';
					} else {
						msg += messageParsers(response.msg);
					}

				} else {
					var time = '';
					if (response.time) {
						time = '[' + response.time + '] ';
					}

					var found = response.msg.match(/приглашает вас в приват\. #(\d+)# предложение/ig);
					if (found) {
						var userName = $this.getUserInfoById(found[1]);
						$this.notify('Вас пригласил в приват пользователь '+userName+'!', $this.ownName, 'private', 30000);
						response.msg = response.msg.replace(/#(\d+)# предложение/ig, '<a href="#" class="accept-private" onclick="App.togglePrivate($1)">Принять</a> предложение');
					}

					msg += '<span>'+time + response.msg + '</span>';
					msgCSStype = 'system';
				}

				if ($this.msgCount > $this.bufferSize) {
					var $line = $this.domElems.chat.find('div').first();
					$line.unbind().remove();
				}

				$this.addLog(msg, msgCSStype);
				$this.msgCount++;

				if ($this.timer == null && ($this.guestCount > 0)) {
					$this.notify(response.msg, fromUser ? fromUser.name : '', 'msg');
				}

				// notifications timeout
				clearTimeout($this.timer);
				$this.timer = setTimeout(function () {
					$this.timer = null
				}, $this.delay);

				$this.chatLastFrom = response.fromName;
				bindClicks();
			}

			var messageParsers = function(incomingMessage) {
				var originalText = incomingMessage;

				var replaceURL = function (text) {
					var exp = /(\b(https?|ftp|file):\/\/[-A-ZА-Я0-9+&@#\/%?=~_|!:,.;]*[-A-ZА-Я0-9+&@#\/%=~_|])/ig;
					return text.replace(exp, "<a target='_blank' href='$1'>$1</a>");
				}

				var replaceWithImgLinks = function (text) {
					var exp = /([-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)(\w+)\.(?:jpg|jpeg|gif|png))/ig;
					var replacement = '<div class="img-thumbnail image-clickable"><a href="#" title="Открыть картинку"><span class="glyphicon glyphicon-picture" style="font-size: 16px"></span></a>';
					replacement += '<img src="$1" style="max-width:100%; height: auto; display: none"></div>';

					return text.replace(exp, replacement);
				}

				var replaceWithYoutube = function (text) {
					var exp = /\b(https?:\/\/(?:www\.)?youtube\.com\/watch\?v=(.*)&?(?:.*))\b/ig;
					var replacement = '<a href="$1" class="video" target="_blank"><img src="https://img.youtube.com/vi/$2/hqdefault.jpg"></a>';

					return text.replace(exp, replacement);
				}

				var replaceWithAudio = function (text) {
					var exp = /\bhttps:\/\/sociochat\.me\/audio\.php\?(?:token=.*)?track_id=(.*)\b/ig;
					var track_id = exp.exec(text);

					if (track_id) {
						track_id = track_id[1];

						var musicElId = 'music-'+track_id+'-'+Math.floor(Math.random()*100000);
						var replacement = '<div class="img-thumbnail">' +
							'<a id="'+musicElId+
							'" class="music" href="#" title="Воспроизвести музыку">' +
							'<span class="glyphicon glyphicon-play-circle"></span> ...</a>';

						$.ajax({
							type: "GET",
							url: '/audio_player.php',
							data: {
								'track_id' : track_id
							},
							success: function(response) {
								var $realTrackEl = $('#'+musicElId);
								var $audio = $('#player');

								$realTrackEl.html($realTrackEl.html().replace(/\.\.\./ig, ' '+response.artist+' - '+response.track));
								$realTrackEl.data('url', response.url);

								if ($audio.length == 0) {
									$('body').append('<audio id="player" style="display: none"></audio>');
									$audio = $('#player');
								}

								$audio.get(0).addEventListener('ended', function() {
									$realTrackEl.find('.glyphicon-pause').removeClass('glyphicon-pause').addClass('glyphicon-play-circle');
								});

								$realTrackEl.click(function() {
									var audioElRaw = $audio.get(0);

									if (audioElRaw.paused || audioElRaw.ended || $audio.data('current-track-id') != $(this).attr('id')) {
										$('#'+$audio.data('current-track-id')).find('.glyphicon-pause').removeClass('glyphicon-pause').addClass('glyphicon-play-circle');

										if ($audio.data('current-track-id') != $(this).attr('id')) {
											$audio.attr('src', $(this).data('url'));
										}

										$audio.data('current-track-id', $(this).attr('id'));

										$(this).find('.glyphicon-play-circle').removeClass('glyphicon-play-circle').addClass('glyphicon-pause');
										audioElRaw.play();
									} else {
										$(this).find('.glyphicon-pause').removeClass('glyphicon-pause').addClass('glyphicon-play-circle');
										audioElRaw.pause();
									}
								});
							},
							dataType: 'json'
						});

						return text.replace(exp, replacement);
					}

					return text;
				}

				var replaceOwnName = function (text) {
					var exp = new RegExp('(?:\\s||,||\\.)('+$this.ownName+')(?:\\s||,||\\.)', 'ig');
					return text.replace(exp , "<code class=\"private\">$1</code>");
				}

				incomingMessage = replaceOwnName(incomingMessage);

				var res = replaceWithImgLinks(incomingMessage);
				res = replaceWithYoutube(res);
				res = replaceWithAudio(res);

				if (res == incomingMessage) {
					incomingMessage = replaceURL(incomingMessage);
				} else {
					incomingMessage = res;
				}

				return incomingMessage;
			}

			var handleErrors = function() {
				if (json.disconnect) {
					$this.connection = null;
					$this.disconnect = 1;
				}

				if (json.errors) {
					for (var i in json.errors) {
						$this.addLog('Ошибка: ' + json.errors[i], 'system');
					}
				}
			}

			var bindClicks = function() {
				var newLine = $this.domElems.chat.find('div:last-child');
				var newNameOnLine = newLine.find('.nickname');

				newLine.find('.image-clickable').click(function() {
					$(this).find('img').toggle();
					$(this).find('a').toggle();
					$this.scrollDown();
				});

				newNameOnLine.click(function() {
					if ($this.clickTimer) {
						clearTimeout($this.clickTimer);
					}
					var el = $(this);
					$this.clickTimer = setTimeout(function () {
						$this.domElems.inputMessage.val(el.text() + ', ' + $this.domElems.inputMessage.val());
						$this.domElems.inputMessage.focus();
					}, 250);
				});

				newNameOnLine.dblclick(function() {
					clearTimeout($this.clickTimer);

					var userName = $(this).text();
					var userId = $this.getUserInfo(userName).user_id;
					if (userId) {
						$this.domElems.address.find('option[value='+userId+']').attr('selected', 'selected');
						$this.domElems.address.data('id', userId);
						$this.domElems.addressReset.show();
						$this.domElems.inputMessage.focus();
					}
				});

				newLine.find('.user-avatar').click(function () {
					var ava = $(this);
					var imgFull = ava.data('src');
					console.log(imgFull);
					if (imgFull != null) {
						var imgEl = $('<img src="'+imgFull+'" style="margin-left:-45px">');
						ava.parent().prepend(imgEl);
						imgEl.click(function() {
							$(this).remove();
							ava.show();
						});

						ava.hide();
					}
				});
			};

			var handleHistory = function() {
				if (json.history) {
					if (json.clear) {
						$this.msgCount = 0;
						$this.domElems.chat.empty();
					}

					for (var i in json.history) {
						handleMessage(json.history[i]);
					}

					$this.lastMsgId = json.lastMsgId;

					$this.scrollDown();
				}
			};

			var handleChannels = function() {
				if (!json.channels) {
					return;
				}

				var $channels = $this.domElems.menuChannels;

				$channels.empty();

				for (var channelId in json.channels) {
					var item = '<li><a href="#" data-id="' + channelId + '">' + json.channels[channelId].name + ' ['+json.channels[channelId].usersCount+']';
					if (channelId == json.chatId) {
						item += ' <span class="glyphicon glyphicon-ok-sign"></span>';
					}
					item += '</a></li>';

					$channels.append(item);
				}

				$this.currentChannel = json.chatId;

				$channels.find('li a').click(function (e) {
					var channelId = $(this).data('id');

					if (channelId != $this.currentChannel) {
						var command = {
							subject: 'Channel',
							action: 'join',
							channelId: channelId
						};
						$this.send(command);
					}

				});

			};

			handleGuests(json);
			handleOwnProperties();
			handleHistory();
			handleDualChat();
			handleMessage(json);
			handleErrors();
			handleChannels();
		}
	}
});