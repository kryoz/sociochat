var getCookie = function (name) {
	var matches = document.cookie.match(new RegExp(
		"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
	));
	return matches ? decodeURIComponent(matches[1]) : undefined;
}

var setCookie = function (name, value, options) {
	options = options || {};

	var expires = options.expires;

	if (typeof expires == "number" && expires) {
		var d = new Date();
		d.setTime(d.getTime() + expires * 1000);
		expires = options.expires = d;
	}
	if (expires && expires.toUTCString) {
		options.expires = expires.toUTCString();
	}

	value = encodeURIComponent(value);

	var updatedCookie = name + "=" + value;

	for (var propName in options) {
		updatedCookie += "; " + propName;
		var propValue = options[propName];
		if (propValue !== true) {
			updatedCookie += "=" + propValue;
		}
	}

	document.cookie = updatedCookie;
}

var App = {
	connection: null,
	hostUrl: null,
	msgCount: 0,
	guestCount: 0,
	guests : [],
	notificationProperties : [],
	bufferSize: 100,

	timer: null,
	pingTimer: null,
	clickTimer: null,
	reconnectTimeout: null,
	retryTimer: null,

	isManualScrolling: false,
	chatName: 'SocioChat',
	connState: 0,
	disconnect: 0,
	lastMsgId: -1,
	delay: 1000*60,

	ownSex: 0,
	ownName: null,

	domElems : {
		guestList: $('#guests'),
		inputMessage: $('#message'),
		chat: $('#log'),
		guestCounter: $('#guest-counter'),

		nickname: $('#nickname'),
		tim: $('#tim'),
		sex: $('#sex'),
		email: $('#email'),
		password: $('#password'),

		loginName: $('#login-name'),
		loginPassword: $('#login-password'),
		address: $('#address'),
		addressReset: $('#address-reset'),
		send: $('#send'),
		setProperties: $('#set-profile-info'),
		setRegInfo: $('#set-reg-info'),
		doLogin: $('#do-login'),

		menuDualize : $('#menu-dualize'),
		menuDualizeStop : $('#menu-dualize-stop'),
		menuExit : $('#menu-exit'),
		menuChat : $('.navbar-brand'),
		navbar: $('.navbar-nav'),
		regLink: $('#reg-info'),
		regPanel: $('#reg-panel')
	},

	Init: function(hostUrl) {
		var $this = App;

		$this.hostUrl = hostUrl;
		$this.Connect();

		$this.bindEvents();
		$this.bindMenus();
	},

	Connect : function() {
		try {
			this.connection = new WebSocket(this.hostUrl);
		} catch (e) {
			this.addLog('Простите, но ваш браузер не поддерживается. Используйте свежую версию Chrome, Opera или Firefox', 1);
		}

		this.addConnectionHandlers();
	},
	handleResponse: function (json) {
		var handler = new ResponseHandler(json, this);
	},
	addConnectionHandlers: function() {
		var $this = this;

		$(window).unload(function() {
			setCookie('lastMsgId', -1, {expires: 30});
			$this.connection.close();
		});

		$this.connection.onopen = function(e) {
			$('.glyphicon-refresh').parent().remove();
			setCookie('lastMsgId', $this.lastMsgId, {expires: 1});

			clearTimeout($this.reconnectTimeout);
			$this.reconnectTimeout = null;
			clearTimeout($this.retryTimer);

			$this.domElems.inputMessage.removeAttr('disabled');
			$this.domElems.inputMessage.attr('placeholder', 'Сообщение');
		};

		$this.connection.onclose = function(e) {
			console.log('onClose');
			if ($this.disconnect) {
				return;
			}
			console.log('onClose handled');

			if (!$this.reconnectTimeout) {
				setCookie('lastMsgId', $this.lastMsgId, {expires: 30});
			}

			$this.retryTimer = setTimeout(function() {
				if (!$this.reconnectTimeout) {
					$this.addLog('<span class="glyphicon glyphicon-refresh rotate"></span>', 1);
					$this.reconnectTimeout = setTimeout(function() {
						$('.glyphicon-refresh').parent().remove();
						$this.addLog('Попытки подключиться исчерпаны. Попробуйте зайти позднее.', 1);
						$this.connection = null;
						$this.disconnect = 1;
						clearTimeout($this.retryTimer);
					}, 30000);
				}

				$this.Connect();
			}, 2000);

			$this.domElems.inputMessage.attr('disabled', 'disabled');
			$this.domElems.inputMessage.attr('placeholder', 'Обрыв соединения... подождите пожалуйста...');
		}

		$this.connection.onerror = function(e) {
			console.log('onError');
		}

		$this.connection.onmessage = function(e) {
			try {
				var json = JSON.parse(e.data);
			} catch (c) {
				return;
			}

			$this.handleResponse(json);
		};
	},
	sendMessage: function () {
		var $this = this;
		try {
			var myNotification = new Notify('test');
			if (myNotification.needsPermission()) {
				myNotification.requestPermission();
			}
		} catch (e) { }

		var command = {
			subject: "Message",
			msg: $this.domElems.inputMessage.val(),
			to: $this.domElems.address.data('id')
		}
		$this.send(command);
		$this.domElems.inputMessage.val('');
	},
	bindEvents: function () {
		var $this = this;

		$this.pingTimer = setInterval(function () {
			if (!$this.connection || $this.connection.readyState != 1) {
				return;
			}
			var cmd = {
				subject: 'Ping'
			};

			$this.connection.send(JSON.stringify(cmd));
		}, 15000);

		// Adress reset
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
	bindMenus: function() {
		var $this = this;

		$this.domElems.menuDualize.click(function (e) {
			var command = {
				subject: 'Enroll',
				action: 'submit'
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
	},
	addLog: function (msg, local) {
		var $div = $('<div' + (local ? ' class="system"' : '') + '>' + msg + '</div>');
		this.domElems.chat.append($div);
		this.scrollDown();
	},
	send: function (params) {
		if (!this.connection || this.connection.readyState == 1) {
			this.connection.send(JSON.stringify(params));
		}
	},
	returnToChat : function () {
		this.domElems.menuChat.tab('show');
		this.domElems.navbar.find('li').removeClass('active');
	},
	getUserInfo : function(name) {
		for (var i in this.guests) {
			if (this.guests[i].name == name) {
				return this.guests[i];
			}
		}
	},
	togglePrivate : function(userId) {
		var command = {
			subject: 'Enroll',
			action: 'invite',
			user_id: userId
		}
		this.send(command);
		this.returnToChat();
	},
	scrollDown: function() {
		if (!this.isManualScrolling) {
			var container = this.domElems.chat;
			var height = container[0].scrollHeight;
			container.scrollTop(height+1000);
		}
	},
	notify: function (msg, author, tag, timeout) {
		try {
			var myNotification = new Notify(author ? author : App.chatName, {
				body: msg,
				tag: tag ? tag : App.chatName,
				icon: 'img/sociochat.jpg',
				timeout : timeout ? timeout : 5000
			});

			myNotification.show();
		} catch (e) { }
	}
}

////////////////////////////////////////////////

var ResponseHandler = function(json, $this) {
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

	var handleGuests = function() {
		if (json.guests) {
			$this.guests = json.guests;
			var guests = $this.guests;

			$this.domElems.guestList.empty();
			$this.domElems.address.empty();
			$this.domElems.address.append('<option value="">Всем</option>');

			for (var i in guests) {
				var guest = guests[i];

				$this.domElems.address.append('<option value="'+guest.user_id+'">'+guest.name+'</option>');

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

				var line = '<tr class="'+colorClass+'"><td class="user-name">' + guest.name + '</td><td>'+guest.tim+'</td>';
				line += '<td><div class="pull-right btn-group btn-group-sm ilb">';

				//line += '<a class="btn btn-default unban">Заметка</a>';

				if (guest.banned) {
					line += '<a class="btn btn-default unban">Разбан</a>';
				} else {
					//line += '<a class="btn btn-default whisper">Шепнуть</a>';
					line += '<a class="btn btn-default invite">Приват</a>';
					line += '<a class="btn btn-default ban">Бан</a>';
				}

				line +='</div></td></tr>';

				$this.domElems.guestList.append(line);
			}

			/*$this.domElems.guestList.find('.whisper').click(function() {
				var userName = $(this).parentsUntil('#guests tbody').find('.user-name').text();
				$this.domElems.address.text(userName);
				$this.domElems.address.data('id', $this.getUserInfo(userName).user_id);
				$this.domElems.addressReset.show();
				$this.returnToChat();
			});*/

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

	var handleMessage = function() {
		if (!json.msg) {
			return;
		}
		var msg = '';

		if (json.lastMsgId) {
			$this.lastMsgId = json.lastMsgId;
		}

		if (json.fromName) {
			var fromUser = $this.getUserInfo(json.fromName);
			var user = fromUser;

			var span = '<span class="nickname ' + getSexClass(user) + '" title="' + (user ? user.tim : '') + '">';
			var article = ' от ';

			if (json.toName) {
				var toUser = $this.getUserInfo(json.toName);

				if (fromUser && fromUser.name == $this.ownName) {
					user = toUser;
					article = 'для ';
					span = '<span class="nickname '+ getSexClass(user) +'" title="' + (user ? user.tim : '') + '">';
				} else {
					$this.notify(json.msg, json.fromName, 'private', 5000);
				}

				var senderOrReciever = article + span + user.name + '</span>';

				msg = msg + '<span class="private"><b>(приватно ' + senderOrReciever + ')</b> '
			} else {
				msg = msg + span + json.fromName + '</span>: <span>';
			}
		} else {
			msg = msg + '<span class="system">*** ';
		}

		var incomingMessage = messageParsers(json.msg);

		msg = msg + incomingMessage + '</span>';

		if ($this.msgCount > $this.bufferSize) {
			var $line = $this.domElems.chat.find('div').first();
			$line.unbind().remove();
		}

		$this.addLog(msg);
		$this.msgCount++;

		if ($this.timer == null && ($this.guestCount > 0)) {
			$this.notify(json.msg, user ? user.name : '', 'msg');
		}

		// notifications timeout
		clearTimeout($this.timer);
		$this.timer = setTimeout(function () {
			$this.timer = null
		}, $this.delay);

		bindClicks();
	}

	var messageParsers = function(incomingMessage) {
		var replaceURL = function (text) {
			var exp = /(\b(https?|ftp|file):\/\/[-A-ZА-Я0-9+&@#\/%?=~_|!:,.;]*[-A-ZА-Я0-9+&@#\/%=~_|])/ig;
			return text.replace(exp, "<a target='_blank' href='$1'>$1</a>");
		}

		var replaceWithImgLinks = function (text) {
			var exp = /([-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)(\w+)\.(?:jpg|jpeg|gif|png))/ig;
			var replacement = '<div class="img-thumbnail"><a href="#" title="Открыть картинку"><span class="glyphicon glyphicon-picture" style="font-size: 16px"></span></a>';
			replacement += '<img src="$1" style="max-width:100%; height: auto; display: none"></div>';

			return text.replace(exp, replacement);
		}

		var replaceOwnName = function (text) {
			var exp = new RegExp('('+$this.ownName+')', 'ig');
			return text.replace(exp , "<code class=\"private\">$1</code>");
		}

		var notifyOnInvite = function (text) {
			var found = text.match(/(.+) приглашает вас в приват\. #(\d+)# предложение/i);
			if (found) {
				$this.notify('Вас пригласили в приват пользователь '+found[1]+'!', $this.ownName, 'private', 30000);
				text = text.replace(/#(\d+)# предложение/i, '<a href="#" class="accept-private" onclick="App.togglePrivate($1)">Принять</a> предложение');
			}

			return text;
		}

		var notifyOnNewUser = function (text) {
			var exp = /^Нас теперь (\d+)! Поприветствуем (.*)$/;
			var found = text.match(exp);
			if (found) {
				$this.notify('В чате появился новый участник', found[2], 'new_guest');
			}
		}

		notifyOnNewUser(json.msg);

		incomingMessage = notifyOnInvite(incomingMessage);
		incomingMessage = replaceOwnName(incomingMessage);

		var res = replaceWithImgLinks(incomingMessage);

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
				$this.addLog('*** Ошибка: ' + json.errors[i], 1);
			}
		}
	}

	var bindClicks = function() {
		var newLine = $this.domElems.chat.find('div:last-child');
		var newNameOnLine = newLine.find('.nickname');

		newLine.find('.img-thumbnail').click(function() {
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
	}

	handleGuests();
	handleOwnProperties();
	handleDualChat();
	handleMessage();
	handleErrors();
};
