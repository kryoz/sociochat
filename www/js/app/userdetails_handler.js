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

            actions.find('.note').click(function () {
                var textNote = $app.domElems.userDetails.find('.note-data');

                var editHtml = '';
                editHtml += '<div class="input-group btn-block" id="note-edit">';
                editHtml += '<input type="text" class="form-control">';
                editHtml += '<span class="input-group-btn">';
                editHtml += '<button class="btn btn-default" type="button">';
                editHtml += '<span class="glyphicon glyphicon-pencil"></span></button>';
                editHtml += '</span>';
                editHtml += '</div></div>';

                var noteForm = $(editHtml);
                textNote.html(noteForm);

                if (textNote.length) {
                    noteForm.find('input').val(textNote.find('div').text());
                }

                noteForm.find('button').click(function () {
                    var command = {
                        subject: 'Note',
                        action: 'save',
                        user_id: userId,
                        note: noteForm.find('input').val()
                    }
                    $app.send(command);
                    $this.updateInfo(userId);
                })
            });

            actions.find('.private').click(function () {
                $app.togglePrivate(userId);
            });
        },
    }
});
