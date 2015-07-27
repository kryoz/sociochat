define(function () {
    return {
        app: {},
        process: function ($app, response) {
            if (!response.msg) {
                return;
            }
            this.app = $app;
            var $this = this;
            var msg = '';
            var msgCSStype = $this.getAnimationType($app.user) + ' ';

            if (response.lastMsgId) {
                $app.lastMsgId = response.lastMsgId;
            }

            if (response.time) {
                var time = $app.timeUTCConvert(response.time);
            }

            if (response.fromName) {
                var fromUser = response.userInfo ? response.userInfo : $app.getUserInfo(response.fromName);

                if ($app.chatLastFrom != response.fromName) {
                    msg += $app.getAvatar(fromUser) + ' ';
                    if (time) {
                        msg += '<div class="time">' + time + '</div>';
                    }
                    msg += '<div class="nickname ' + this.getSexClass(fromUser) + '" title="' + (fromUser ? fromUser.tim : '') + '">' + response.fromName + '</div>';
                } else {
                    msgCSStype += 'repeat';
                }

                if (response.toName) {
                    var toUser = $app.getUserInfo(response.toName);
                    var toWho = 'вас';

                    if (fromUser && fromUser.name == $app.user.name) {
                        toWho = toUser.name;
                    }

                    msg += '<div class="private"><b>[приватно для ' + toWho + ']</b> '
                }

                msg += '<div>'+this.parse(response.msg)+'</div>';
                msg += '<div class="clearfix"></div>';

                if (response.toName) {
                    msg += '</div>';
                }
            } else {
                var found = response.msg.match(/приглашает вас в приват\. #(\d+)# предложение/ig);

                if (found) {
                    var userName = $app.getUserInfoById(found[1]);
                    $this.notify('Вас пригласил в приват пользователь ' + userName + '!', $app.user.name, 'private', 30000);
                    response.msg = response.msg.replace(/#(\d+)# предложение/ig, '<a href="#" class="accept-private" data-id="$1">Принять</a> предложение');
                }

                if (time) {
                    msg += '<div class="time">' + time + '</div>';
                }

                msg += '<span>' + response.msg + '</span>';
                msgCSStype += 'system';
            }

            if ($app.msgCount > $app.bufferSize) {
                var $line = $app.domElems.chat.find('div').first();
                $line.unbind().remove();
            }

            $app.addLog(msg, msgCSStype);
            $app.msgCount++;
            $app.chatLastFrom = response.fromName;

            this.notify(response, fromUser);
            this.bindClicks();
        },
        getAnimationType: function (user) {
            switch (user.msgAnimationType) {
                case '1':
                    return 'simple';
                    break;
                case '2':
                    return 'left';
                    break;
                case '3':
                default:
                    return 'fadein';
            }
        },
        notify: function (response, fromUser) {
            var $this = this.app;

            if (!$this.isTabActive) {
                if ($this.user.notifyVisual) {
                    try {
                        var myNotification = new Notify(fromUser ? fromUser.name : $this.chatName, {
                            body: response.msg.replace(/<\/?[^>]+>/gi, ''),
                            tag: 'msg',
                            icon: 'img/sociochat.jpg',
                            timeout: 5000
                        });

                        myNotification.show();
                    } catch (e) {}
                }

                if ($this.user.notifySound) {
                    $this.sound.play();
                }
            }
        },
        parse: function (incomingMessage) {
            var $this = this.app;

            var isHTML = function (str) {
                var a = document.createElement('div');
                a.innerHTML = str;
                for (var c = a.childNodes, i = c.length; i--;) {
                    if (c[i].nodeType == 1 && c[i].tagName != 'CODE') {
                        return true;
                    }
                }
                return false;
            };

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
                var replacement = '<div class="img-thumbnail image-clickable" data-src="' + holder +'"><a href="#" title="Открыть картинку">';
                replacement += '<span class="glyphicon glyphicon-picture" style="font-size: 16px"></span></a>';
                replacement += '<img src="" style="max-width:100%; height: auto; display: none"></div>';
                return replacement;
            }


            var replaceWithImgLinks = function (text) {
                var exp = '(https?:\/\/[-A-ZА-Я0-9+&@#\/%?=~_|!:,.;()]*[-A-ZА-Я0-9+&@#\/%=~_|()]\.(?:jpg|jpeg|gif|png)(?:[^ ]*))';
                return text.replace(new RegExp(exp, 'ig'), getImgReplacementString('$1'));
            }

            var replaceURL = function (text) {
                if (isHTML(text)) {
                    return text;
                }
                var exp = /(\b(https?|ftp|file):\/\/[-A-ZА-Я0-9+&@#\/%?=~_|!:,.;]*[-A-ZА-Я0-9+&@#\/%=~_|()])/ig;
                var url = exp.exec(text);

                if (!url) {
                    return text;
                }
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

            var replaceWithYoutube = function (text) {
                var exp = /\b(https?:\/\/(?:www\.)?youtube\.com\/watch\?v=(.*)&?(?:.*))\b/ig;
                var replacement = '<a href="$1" class="video" target="_blank"><img src="https://img.youtube.com/vi/$2/hqdefault.jpg"></a>';

                return text.replace(exp, replacement);
            }

            var replaceOwnName = function (text) {
                var exp = new RegExp('(?:\\s||,||\\.)(' + $this.user.name + ')(?:\\s||,||\\.)', 'ig');
                return text.replace(exp, "<code class=\"private\">$1</code>");
            }

            incomingMessage = replaceOwnName(incomingMessage);
            incomingMessage = replaceWithYoutube(incomingMessage);
            incomingMessage = replaceURL(incomingMessage);

            return incomingMessage;
        },
        bindClicks: function () {
            var $this = this.app;
            var newLine = $this.domElems.chat.find('div:last-child');
            var newNameOnLine = newLine.find('.nickname');

            newLine.find('.image-clickable').click(function () {
                var $img = $(this).find('img');

                if ($img.attr('src') == '') {
                    $img.attr('src', $(this).data('src'));
                }

                $img.toggle();
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
                var e = this;
                require(['userdetails_handler'], function (userDetails) {
                    userDetails.process($this, $(e).data('id'));
                });
            });

            newLine.find('.accept-private').click(function () {
                var userId = $(this).data('id');
                if (userId) {
                    $this.togglePrivate(userId);
                }
            });

            newLine.find('a[bind-play-click=1]').click(function (e) {
                var musicElId = $(this).attr('id');
                require(['audio'], function (audio) {
                    audio.playMusic($this.domElems.audioPlayer, e, musicElId);
                });
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
        }
    }
});
