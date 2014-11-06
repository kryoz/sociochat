define(function () {
    return {
        app: {},
        process: function ($this, response) {
            if (!response.msg) {
                return;
            }
            this.app = $this;
            var msg = '';
            var msgCSStype = '';

            if (response.lastMsgId) {
                $this.lastMsgId = response.lastMsgId;
            }

            if (response.time) {
                var time = $this.timeUTCConvert(response.time);
            }

            if (response.fromName) {
                var fromUser = response.userInfo ? response.userInfo : $this.getUserInfo(response.fromName);

                if ($this.chatLastFrom != response.fromName) {
                    msg += this.getAvatar(fromUser) + ' ';
                    if (time) {
                        msg += '<div class="time">' + time + '</div>';
                    }
                    msg += '<div class="nickname ' + this.getSexClass(fromUser) + '" title="' + (fromUser ? fromUser.tim : '') + '">' + response.fromName + '</div>';
                } else {
                    msgCSStype = 'repeat';
                }
                if (response.toName) {
                    var toUser = $this.getUserInfo(response.toName);
                    var toWho = 'вас';

                    if (fromUser && fromUser.name == $this.user.name) {
                        $this.notify(response.msg, response.fromName, 'private', 5000);
                        toWho = toUser.name;
                    }

                    msg += '<div class="private"><b>[приватно для ' + toWho + ']</b> '
                }

                msg += this.parse(response.msg);

                if (response.toName) {
                    msg += '</div>';
                }
            } else {
                var found = response.msg.match(/приглашает вас в приват\. #(\d+)# предложение/ig);

                if (found) {
                    var userName = $this.getUserInfoById(found[1]);
                    $this.notify('Вас пригласил в приват пользователь ' + userName + '!', $this.user.name, 'private', 30000);
                    response.msg = response.msg.replace(/#(\d+)# предложение/ig, '<a href="#" class="accept-private" data-id="$1">Принять</a> предложение');
                }

                if (time) {
                    msg += '<div class="time">' + time + '</div>';
                }

                msg += '<span>' + response.msg + '</span>';
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
            this.bindClicks();
        },
        parse: function (incomingMessage) {
            var originalText = incomingMessage;
            var $this = this.app;

            String.prototype.hashCode = function () {
                var hash = 0, i, chr, len;
                if (this.length == 0) return hash;
                for (i = 0, len = this.length; i < len; i++) {
                    chr = this.charCodeAt(i);
                    hash = ((hash << 5) - hash) + chr;
                    hash |= 0; // Convert to 32bit integer
                }
                return hash;
            };

            var getImgReplacementString = function (holder) {
                var replacement = '<div class="img-thumbnail image-clickable"><a href="#" title="Открыть картинку"><span class="glyphicon glyphicon-picture" style="font-size: 16px"></span></a>';
                replacement += '<img src="' + holder + '" style="max-width:100%; height: auto; display: none"></div>';
                return replacement;
            }


            var replaceWithImgLinks = function (text) {
                var exp = /\b((https?):\/\/[-A-ZА-Я0-9+&@#\/%?=~_|!:,.;]*[-A-ZА-Я0-9+&@#\/%=~_|()]\.(?:jpg|gif|png)(?:\??.*))\b/ig;

                return text.replace(exp, getImgReplacementString('$1'));
            }

            var replaceURL = function (text) {
                var exp = /(\b(https?|ftp|file):\/\/[-A-ZА-Я0-9+&@#\/%?=~_|!:,.;]*[-A-ZА-Я0-9+&@#\/%=~_|()])/ig;
                var url = exp.exec(text);

                if (url) {
                    url = url[1];

                    var imgRegExp = replaceWithImgLinks(text);
                    if (imgRegExp != text) {
                        return imgRegExp;
                    }

                    var hash = url.hashCode();

                    $.ajax({
                        type: "GET",
                        url: '/img.php',
                        data: {
                            'url': url
                        },
                        success: function (response) {
                            var $img = $('#url-' + hash);

                            $img.hide();

                            var replacement = $(getImgReplacementString($img.attr('href')));

                            replacement.insertAfter($img);
                            $img.remove();

                            replacement.click(function () {
                                $(this).find('img').toggle();
                                $(this).find('a').toggle();
                                $this.scrollDown();
                            });
                        },

                        dataType: 'json'
                    });

                    return text.replace(exp, '<a target="_blank" href="$1" id="url-' + hash + '">$1</a>');
                }
                return text;
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

                    var musicElId = 'music-' + track_id + '-' + Math.floor(Math.random() * 100000);
                    var replacement = '<div class="img-thumbnail">' +
                        '<a id="' + musicElId +
                        '" class="music" href="#" title="Воспроизвести музыку">' +
                        '<span class="glyphicon glyphicon-play-circle"></span> ...</a>';

                    $.ajax({
                        type: "GET",
                        url: '/audio_player.php',
                        data: {
                            'track_id': track_id
                        },
                        success: function (response) {
                            var $realTrackEl = $('#' + musicElId);

                            $realTrackEl.html($realTrackEl.html().replace(/\.\.\./ig, ' ' + response.artist + ' - ' + response.track));
                            $realTrackEl.data('src', response.url);
                            $realTrackEl.click(function (e) {
                                require(['audio'], function (audio) {
                                    audio.playMusic($this.domElems.audioPlayer, e, musicElId);
                                });
                            });
                        },
                        dataType: 'json'
                    });

                    return text.replace(exp, replacement);
                }

                return text;
            }

            var replaceOwnName = function (text) {
                var exp = new RegExp('(?:\\s||,||\\.)(' + $this.user.name + ')(?:\\s||,||\\.)', 'ig');
                return text.replace(exp, "<code class=\"private\">$1</code>");
            }

            var replaceHash = function (text) {
                var exp = / (#[a-zа-я0-9-_]+)/ig;
                var replacement = '<a href="#hashes" data-src="$1" class="hash tab-panel">$1</a>';

                return text.replace(exp, replacement);
            }

            incomingMessage = replaceOwnName(incomingMessage);

            var res = replaceWithAudio(incomingMessage);
            res = replaceWithYoutube(res);
            res = replaceHash(res);

            if (res == incomingMessage) {
                incomingMessage = replaceURL(incomingMessage);
            } else {
                incomingMessage = res;
            }

            return incomingMessage;
        },
        bindClicks: function () {
            var $this = this.app;
            var newLine = $this.domElems.chat.find('div:last-child');
            var newNameOnLine = newLine.find('.nickname');

            newLine.find('.image-clickable').click(function () {
                $(this).find('img').toggle();
                $(this).find('a').toggle();
                $this.scrollDown();
            });

            newNameOnLine.click(function () {
                if ($this.clickTimer) {
                    clearTimeout($this.clickTimer);
                }
                var el = $(this);
                $this.clickTimer = setTimeout(function () {
                    $this.domElems.inputMessage.val(el.text() + ', ' + $this.domElems.inputMessage.val());
                    $this.domElems.inputMessage.focus();
                }, 250);
            });

            newNameOnLine.dblclick(function () {
                clearTimeout($this.clickTimer);

                var userName = $(this).text();
                var userId = $this.getUserInfo(userName).user_id;
                if (userId) {
                    $this.domElems.address.find('option[value=' + userId + ']').attr('selected', 'selected');
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
                    var imgEl = $('<img src="' + imgFull + '" style="margin-left:-45px">');
                    ava.parent().prepend(imgEl);
                    imgEl.click(function () {
                        $(this).remove();
                        ava.show();
                    });

                    ava.hide();
                }
            });

            newLine.find('.accept-private').click(function () {
                var userId = $(this).data('id');
                if (userId) {
                    $this.togglePrivate(userId);
                }
            });

            newLine.find('.hash').click(function () {
                $(this).tab('show');
                $this.domElems.hashInput.val($(this).data('src'));
                $this.domElems.doHashSearch.click();
            });
        },
        getSexClass: function (user) {
            var colorClass = null;
            var sex = user ? user.sex : 'Аноним';
            switch (sex) {
                case 'Женщина' :
                    colorClass = 'female';
                    break;
                case 'Аноним' :
                    colorClass = 'anonym';
                    break;
                case 'Мужчина' :
                    colorClass = 'male';
                    break;
                default:
                    colorClass = '';
            }
            return colorClass;
        },
        getAvatar: function (user) {
            var $this = this.app;
            var text = '<div class="user-avatar"';

            if (user && user.avatarThumb) {
                text += ' data-src="' + user.avatarImg + '">';
                text += '<img src="' + $this.getImgUrl(user.avatarThumb) + '">';
            } else {
                text += '>';
                text += '<span class="glyphicon glyphicon-user"></span>';
            }

            return text + '</div>';
        }
    }
});
