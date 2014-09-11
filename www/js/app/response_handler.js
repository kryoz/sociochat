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
                require(['message_handler'], function(messageHandler) {
                    messageHandler.process($this, response);
                });
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

            var handleTokenRefresh = function () {
                if (!json.refreshToken) {
                    return;
                }

                $this.connection.close();

                $this.setCookie('token', null);

                $.ajax({
                    type: "GET",
                    url: '/session.php',
                    success: function(response) {
                        $this.token = $this.getCookie('token');
                        $this.Connect();
                    },
                    dataType: 'json'
                })
            };

            handleTokenRefresh();
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