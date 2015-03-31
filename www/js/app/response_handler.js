define(function () {
    return {
        process: function (json, $this) {
            var handleGuests = function (json) {
                if (json.guests && $this.guestEditState == 0) {
                    $this.guests = json.guests;
                    var guests = $this.guests;
                    var d = new Date();

                    $this.domElems.guestList.empty();
                    $this.domElems.address.empty();

                    var guestHMTL = '<tr><th>Имя</th><th>ТИМ</th><th>Город</th><th>Возраст</th><th></th></tr>';
                    var guestDropdownHTML = '<option value="">Всем</option>';

                    for (var i in guests) {
                        var guest = guests[i];

                        guestDropdownHTML += '<option value="' + guest.user_id + '">' + guest.name + '</option>';

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

                        guestHMTL += '<tr class="' + colorClass + '">';
                        guestHMTL += '<td>' + $this.getAvatar(guest) + ' <span class="user-name">' + guest.name + '</span></td>';
                        guestHMTL += '<td>' + guest.tim + '</td>';
                        guestHMTL += '<td>' + (guest.city ? guest.city : '') + '</td>';
                        guestHMTL += '<td>' + (guest.birth == (d.getFullYear() - 1930) ? '' : guest.birth) + '</td>';
                        guestHMTL += '</tr>';

                        if (guest.note) {
                            guestHMTL += '<tr id="user-note-' + guest.user_id + '" class="' + colorClass + '"><td colspan="5" class="no-border-top">';
                            guestHMTL += '<div class="col-md-12">';
                            guestHMTL += guest.note;
                            guestHMTL += '</div>';
                            guestHMTL += '</td></tr>';
                        }
                    }

                    $this.domElems.guestList.append(guestHMTL);
                    $this.domElems.address.append(guestDropdownHTML);

                    $this.domElems.address.find('option[value=' + $this.domElems.address.data('id') + ']').attr('selected', 'selected');

                    $this.guestCount = guests.length;
                    $this.domElems.guestCounter.text($this.guestCount);
                }
            }

            var handleOwnProperties = function () {
                if (!json.ownProperties) {
                    return;
                }

                var props = json.ownProperties;

                if (props.id) {
                    $this.user.id = props.id;
                }

                if (props.name) {
                    $this.domElems.nickname.val(props.name);
                    $this.user.name = props.name;
                }

                if (props.sex) {
                    $this.domElems.sex.val(props.sex);
                    $this.user.sex = props.sex == 2;
                }

                if (props.tim) {
                    $this.domElems.tim.val(props.tim);
                    $this.user.tim = props.tim;
                }

                if (props.email) {
                    $this.domElems.email.val(props.email);
                    $this.user.email = props.email;
                    $this.domElems.loginLink.hide();
                }

                if (props.avatarImg) {
                    var image = document.createElement('img');
                    image.src = props.avatarImg;
                    image.style.maxWidth = 'inherit';
                    image.style.maxHeight = 'inherit';
                    $this.domElems.avatar.find('div.avatar-placeholder').html(image);
                }

                if (props.avatarThumb) {
                    var thumb = document.createElement('img');
                    thumb.src = props.avatarThumb;
                    $this.domElems.avatar.find('div.avatar-placeholder-mini').html(thumb);
                }

                if (props.birth) {
                    $this.domElems.birth.val(props.birth);
                }

                if (props.city) {
                    $this.domElems.city.val(props.city);
                }

                if (props.censor) {
                    $this.domElems.censor.prop('checked', props.censor);
                }
            }

            var handleDualChat = function () {
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

            var handleMessage = function (response) {
                require(['message_handler'], function (messageHandler) {
                    messageHandler.process($this, response);
                });
            }

            var handleErrors = function () {
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

            var handleHistory = function () {
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

            var handleChannels = function () {
                if (!json.channels) {
                    return;
                }

                var $channels = $this.domElems.menuChannels;

                $channels.empty();

                for (var channelId in json.channels) {
                    var item = '<li><a href="#" data-id="' + channelId + '">' + json.channels[channelId].name + ' [' + json.channels[channelId].usersCount + ']';
                    if (channelId == json.chatId) {
                        item += ' <span class="glyphicon glyphicon-ok-sign"></span>';
                    }
                    item += '</a></li>';

                    $channels.append(item);
                }

                $this.currentChannel = json.chatId;
                $('#channel-name').text(json.channels[$this.currentChannel].name);

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

            var handleTokenRefresh = function () {
                if (!json.refreshToken) {
                    return;
                }

                $this.connection.close();
                $this.initSession(function () {
                        $this.Connect();
                    }, {
                        regenerate: 1
                    }
                );
            };

            var handleMusicInfo = function() {
                if (!json.musicInfo) {
                    return;
                }
                
                var info = json.musicInfo;
                var musicElId = 'music-' + info.track_id;
                var $realTrackEl = $('#' + musicElId);

                $realTrackEl.find('.audio-title').text(info.artist + ' - ' + info.track);
                $realTrackEl.data('src', info.url);
                $realTrackEl.click(function (e) {
                    require(['audio'], function (audio) {
                        audio.playMusic($this.domElems.audioPlayer, e, musicElId);
                    });
                });
            };

            handleTokenRefresh();
            handleGuests(json);
            handleOwnProperties();
            handleHistory();
            handleDualChat();
            handleMessage(json);
            handleErrors();
            handleChannels();
            handleMusicInfo();
        }
    }
});