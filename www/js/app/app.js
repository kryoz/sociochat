define('app', function () {
    return {
        connection: null,
        hostUrl: null,
        domain: null,
        token: null,
        isRetina: (window.devicePixelRatio > 1 || (window.matchMedia && window.matchMedia("(-webkit-min-device-pixel-ratio: 1.5),(-moz-min-device-pixel-ratio: 1.5),(min-device-pixel-ratio: 1.5)").matches)),
        msgCount: 0,
        guestCount: 0,
        guests : [],
        currentChannel: 1,
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
        chatLastFrom: null,
	    isFirstConnect: true,

        domElems : {
            guestList: $('#guests'),
            inputMessage: $('#message'),
            chat: $('#log'),
            guestCounter: $('#guest-counter'),

            nickname: $('#profile-nickname'),
            tim: $('#profile-tim'),
            sex: $('#profile-sex'),
            email: $('#profile-email'),
            password: $('#profile-password'),
            avatar: $('#profile-avatar'),
	        city: $('#profile-city'),
	        birth: $('#profile-year'),
            censor: $('#profile-censor'),

            loginName: $('#login-name'),
            loginPassword: $('#login-password'),
            address: $('#address'),
            addressReset: $('#address-reset'),
            sendMessageButton: $('#send'),
            setProperties: $('#set-profile-info'),
            setRegInfo: $('#set-reg-info'),
            doLogin: $('#do-login'),
            doMusicSearch: $('#do-music-search'),
	        musicInput: $("#music input[name=song]"),

            menuDualize : $('#menu-dualize'),
            menuDualizeStop : $('#menu-dualize-stop'),
            menuExit : $('#menu-exit'),
            menuChat : $('.navbar-brand'),
            menuChannels: $('#menu-channels'),
            navbar: $('.navbar-nav'),
            regLink: $('#reg-info'),
            regPanel: $('#reg-panel'),
	        audioPlayer : $('#player')
        },

        Init: function(hostUrl, domain) {
	        var $this = this;

	        this.hostUrl = hostUrl;
            this.domain = domain;

            this.initSession(function() {
                $this.Connect();
            });

            require(['init_events'], function(binders) {
                binders.bindEvents($this);
                binders.bindMenus($this);
                binders.AvatarUploadHandler($this);
            });

        },
		initSession: function (callback, params) {
			var $this = this;
			$.ajax({
				type: "GET",
				data: params,
				url: '/session.php',
                    cache: false,
				    success: function(response) {
                        $this.token = response.token;
                        var options = {
                            expires : response.ttl
                        };

                        if (response.isSecure) {
                            $.extend(options, {secure: response.isSecure});
                        }
                        $this.setCookie(
                            'token',
                            response.token,
                            options
                        );
                        $this.Connect();
				},
				dataType: 'json'
			});
		},
        Connect : function() {
            try {
                this.connection = new WebSocket(this.hostUrl);
            } catch (e) {
                this.addLog(e, 1);
            }

            this.addConnectionHandlers();
        },
        handleResponse: function (json) {
            var $this = this;
            require(['response_handler'], function(response) {
                response.process(json, $this)
            });
        },
        addConnectionHandlers: function() {
            var $this = this;
            var startPendingStatus = function () {
                $this.domElems.sendMessageButton
                    .find('span')
                    .removeClass('glyphicon-send')
                    .addClass('glyphicon-refresh')
                    .addClass('rotate');
            }
            var endPendingStatus = function() {
                $this.domElems.sendMessageButton
                    .find('span')
                    .addClass('glyphicon-send')
                    .removeClass('glyphicon-refresh')
                    .removeClass('rotate');
            }

            $(window).unload(function() {
                $this.connection.close();
            });

            $this.connection.onopen = function(e) {
	            if ($this.isFirstConnect) {
		            $this.domElems.chat.empty();
		            $this.isFirstConnect = false;
	            }

                endPendingStatus();

                $this.setCookie('lastMsgId', $this.lastMsgId, {expires: 30});

                clearTimeout($this.reconnectTimeout);
                $this.reconnectTimeout = null;
                clearTimeout($this.retryTimer);

                $this.domElems.inputMessage.removeAttr('disabled');
                $this.domElems.inputMessage.attr('placeholder', 'Сообщение');
            };

            $this.connection.onclose = function(e) {
                if ($this.disconnect) {
                    return;
                }

                if (!$this.reconnectTimeout) {
                    $this.setCookie('lastMsgId', $this.lastMsgId, {expires: 30});
                }

                $this.retryTimer = setTimeout(function() {
                    if (!$this.reconnectTimeout) {
                        startPendingStatus();

                        $this.reconnectTimeout = setTimeout(function() {
                            endPendingStatus();
                            $this.addLog('Попытки подключиться исчерпаны. Попробуйте зайти позднее.', 'system');
                            $this.connection = null;
                            $this.disconnect = 1;
                            clearTimeout($this.retryTimer);
                        }, 30000);
                    }

                    $this.Connect();
                }, 2000);

                $this.domElems.inputMessage.attr('disabled', 'disabled');
                $this.domElems.inputMessage.attr('placeholder', 'Обрыв соединения... подождите, пожалуйста...');
            }

            $this.connection.onerror = function(e) {
                console.log(e);
            }

            $this.connection.onmessage = function(e) {
                try {
                    var json = JSON.parse(e.data);
                } catch (c) {
                    console.log(c);
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
        addLog: function (msg, cssclass) {
            var $div = $('<div class="'+cssclass+'">' + msg + '</div>');
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
        getUserInfoById : function(id) {
            for (var i in this.guests) {
                if (this.guests[i].id == id) {
                    return this.guests[i];
                }
            }
        },
        togglePrivate : function(userId) {
            var command = {
                subject: 'Channel',
                action: 'join',
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
        },
        getImgUrl: function (url) {
            if (this.isRetina && url) {
                var exp = /(\.\w+)/i
                return url.replace(exp , "@2x$1");
            }
            return url;
        },
        setCookie: function (name, value, options) {
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
            console.log(updatedCookie);
            document.cookie = updatedCookie;
        },
        getCookie: function(name) {
            var matches = document.cookie.match(new RegExp(
                "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
            ));
            return matches ? decodeURIComponent(matches[1]) : undefined;
        }
    }
});
