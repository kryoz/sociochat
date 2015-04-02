define(function () {
    return {
        app: {},
        process: function ($app, userId) {
            this.app = $app;
            this.updateInfo(userId);
        },

        updateInfo: function(userId) {
            var $this = this;
            var $app = this.app;
            $.ajax({
                url: '/user.php',
                type: 'GET',
                data: {id: userId},
                success: function (response) {
                    var profile = $app.domElems.userDetails;
                    var avatar = '<div class="user-avatar"><span class="glyphicon glyphicon-user" style="font-size: 148px"></span></div>';
                    if (response.avatar) {
                        avatar = '<img src="' + response.avatar + '" class="img-responsive img-rounded">';
                    }
                    profile.find('.photo').html(avatar);
                    profile.find('.name').text(response.name);
                    profile.find('.sex').text(response.sex);
                    profile.find('.tim').text(response.tim);
                    profile.find('.birth').text(response.birth);
                    profile.find('.note-data').text(response.note);
                    profile.find('.online-time').text(response.onlineTime);
                    profile.find('.date-register').text(response.dateRegister);
                    profile.find('.word-rating').text(response.wordRating);
                    profile.find('.rude-rating').text(response.rudeRating);
                    profile.find('.music-rating').text(response.musicRating);
                    profile.find('.karma').text(response.karma);
                    profile.find('.names').text(response.names);
                    $app.domElems.userDetails.find('.mail-note').remove();
                    $app.domElems.userDetails.find('.note-edit').remove();

                    var actions = profile.find('.actions');
                    actions.children().unbind();
                    actions.children().hide();

                    for (var action in response.allowed) {
                        actions.find('.' + response.allowed[action]).show();
                    }

                    $this.bindActionIcons(actions, response.id);

                    $('.tab-pane').removeClass('active');
                    $app.domElems.userDetails.toggleClass('active');
                },
                dataType: 'json'
            });
        },
        bindActionIcons: function (actions, userId) {
            var $app = this.app;
            var $this = this;

            actions.find('.ban').click(function () {
                var command = {
                    subject: 'Blacklist',
                    action: 'ban',
                    user_id: userId
                }
                $app.send(command);
                $this.updateInfo(userId);
            });

            actions.find('.unban').click(function () {
                var command = {
                    subject: 'Blacklist',
                    action: 'unban',
                    user_id: userId
                };
                $app.send(command);
                $this.updateInfo(userId);
            });

            actions.find('.karma-plus').click(function () {
                var command = {
                    subject: 'Properties',
                    action: 'addKarma',
                    user_id: userId
                };
                $app.send(command);
                $this.updateInfo(userId);
            });

            actions.find('.karma-minus').click(function () {
                var command = {
                    subject: 'Properties',
                    action: 'decreaseKarma',
                    user_id: userId
                };
                $app.send(command);
                $this.updateInfo(userId);
            });

            actions.find('.note').click(function () {
                var textNote = $app.domElems.userDetails.find('.note-data');
                var dupItem = $('#note-edit');

                if (dupItem.length) {
                    dupItem.remove();
                    return false;
                }
                var editHtml = '';
                editHtml += '<div class="input-group btn-block" id="note-edit">';
                editHtml += '<input type="text" class="form-control">';
                editHtml += '<span class="input-group-btn">';
                editHtml += '<button class="btn btn-default" type="button">';
                editHtml += '<span class="glyphicon glyphicon-pencil"></span></button>';
                editHtml += '</span>';
                editHtml += '</div></div>';

                var noteForm = $(editHtml);

                if (textNote.length) {
                    noteForm.find('input').val(textNote.text());
                }

                textNote.html(noteForm);
                noteForm.find('input').focus();

                noteForm.find('button').click(function () {
                    var command = {
                        subject: 'Note',
                        action: 'save',
                        user_id: userId,
                        note: noteForm.find('input').val()
                    };
                    $app.send(command);
                    $this.updateInfo(userId);
                });
            });

            actions.find('.private').click(function () {
                $app.togglePrivate(userId);
            });

            actions.find('.mail').click(function () {
                var textNote = $app.domElems.userDetails.find('.mail-note');
                var dupItem = $('.mail-note');

                if (dupItem.length) {
                    dupItem.remove();
                    return false;
                }
                if (!textNote.length) {
                    textNote = $('<br><div class="panel panel-default mail-note"></div>');
                    $app.domElems.userDetails.find('.photo').parent().append(textNote);
                }

                var editHtml = '<div class="panel-heading">Введите сообщение</div>';
                editHtml += '<div class="panel-body">';
                editHtml += '<div class="input-group btn-block" id="mail-edit">';
                editHtml += '<input type="text" class="form-control">';
                editHtml += '<span class="input-group-btn">';
                editHtml += '<button class="btn btn-default" type="button">';
                editHtml += '<span class="glyphicon glyphicon-pencil"></span></button>';
                editHtml += '</span>';
                editHtml += '</div></div></div>';

                editHtml = $(editHtml);
                textNote.html(editHtml);
                editHtml.find('input').focus();

                textNote.find('button').click(function () {
                    var command = {
                        subject: 'Message',
                        msg: '/mail '+$app.getUserInfoById(userId).name+' '+editHtml.find('input').val(),
                        to: ''
                    };
                    $app.send(command);
                    textNote.remove();
                });
            });
        }
    }
});
