define(function () {
    return {
        process: function (app) {
            var hashName = app.domElems.hashInput.val();
            var hashList = app.domElems.hashPanel.find(".result");
            var pagination = app.domElems.hashPanel.find(".pagination");
            var sourceUrl = '/hash.php';
            var $this = this;

            var renderHashSearchResponse = function (response) {
                var totalCount = response.totalCount;
                var page = response.page;
                var pageCount = response.pageCount;
                var hashes = response.hashes;

                var html = '';

                for (var id in hashes) {
                    var hashItem = hashes[id];

                    html += '<div class="panel panel-default">';
                    html += '<div class="panel-heading"><b>'+hashItem.name+'</b> '+app.timeUTCConvert(hashItem.date, 1)+'</div>';
                    html += '<div class="panel-body">'+hashItem.message+'</div>';
                    html += "</div>";
                }

                hashList.html(html);

                html = '';
                for (var i = 1; i < Math.floor(totalCount / pageCount); i++) {
                    var currentClass = '';
                    var src = '?song=' + hashName + '&page=' + i;

                    if (i == page) {
                        currentClass = 'active';
                        src = '';
                    }

                    html += '<li class="' + currentClass + '">';
                    html += '<a data-src="' + src + '" href="#">' + i + '</a></li>';
                }

                pagination.html(html);

                pagination.find('a').click(function (e) {
                    var src = $(this).data('src');
                    if (src) {
                        $.ajax({
                            type: "GET",
                            url: sourceUrl + src,
                            success: function (r) {
                                renderHashSearchResponse(r);
                            },
                            dataType: 'json'
                        });
                    }
                });
            };

            if (hashName) {
                var l = Ladda.create(app.domElems.doMusicSearch.get(0));
                l.start();

                $.ajax({
                    type: "GET",
                    url: sourceUrl,
                    data: {
                        'hash': hashName
                    },
                    success: function (response) {
                        renderHashSearchResponse(response);
                        l.stop();
                    },
                    error: function (response) {
                        hashList.html('<td>' + response.status + ' ' + response.statusText + ' ' + response.responseText + '</td>');
                        l.stop();
                    },
                    dataType: 'json'
                });
            }
        },
        playMusic: function ($audio, e, musicElId) {
            var audioElRaw = $audio.get(0);
            var $realTrackEl = $('#' + musicElId);
            var $this = $(e.currentTarget);

            audioElRaw.addEventListener('ended', function () {
                $realTrackEl.find('.glyphicon-pause').removeClass('glyphicon-pause').addClass('glyphicon-play-circle');
            });

            if (audioElRaw.paused || audioElRaw.ended || $audio.data('current-track-id') != $this.attr('id')) {
                $('#' + $audio.data('current-track-id')).find('.glyphicon-pause').removeClass('glyphicon-pause').addClass('glyphicon-play-circle');

                if ($audio.data('current-track-id') != $this.attr('id')) {
                    $audio.attr('src', $this.data('src'));
                }

                $audio.data('current-track-id', $this.attr('id'));

                $this.find('.glyphicon-play-circle').removeClass('glyphicon-play-circle').addClass('glyphicon-pause');
                audioElRaw.play();
            } else {
                $this.find('.glyphicon-pause').removeClass('glyphicon-pause').addClass('glyphicon-play-circle');
                audioElRaw.pause();
            }
        }
    }
});