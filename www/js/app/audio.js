define(function() {
	return {
		process: function(app) {
			var songName = app.domElems.musicInput.val();
			var trackList = $("#music .table");
			var pagination = $("#music .pagination");
			var sourceUrl = '/audio2.php';
			var $this = this;

			var renderSongSearchResponse = function(response) {
				var trackCount = response.count;
				var page = response.page;
				var pageCount = response.pageCount;
				response = response.tracks;

				var html = '<thead><th>Песня</th><th>Качество (кбит/сек)</th></thead>';

				for (var id in response) {
					var trackInfo = response[id];

					var musicElId = 'music-'+trackInfo.id+'-'+Math.floor(Math.random()*100000);

					html += "<tr>";
					html += '<td>';
					html += '<a href="#" data-id="'+trackInfo.id+'" id="'+musicElId+'" class="music"><span class="glyphicon glyphicon-play-circle"></span></a>';
					html += '&nbsp;&nbsp;<a href="#" data-src="https://sociochat.me/audio.php?track_id='+trackInfo.id+'" class="share"><span class="glyphicon glyphicon-bullhorn"></span></a> ';
					html += '&nbsp;&nbsp;'+trackInfo.artist+' - '+trackInfo.track+'</td>';
					html += '<td><a href="https://sociochat.me/audio.php?track_id='+trackInfo.id+'" target="_blank"><span class="glyphicon glyphicon-floppy-save"></span></a> '+trackInfo.bitrate+'</td>';
					html += "</tr>";
				}

				trackList.html(html);

				html = '';
				for (var i=1;i < Math.floor(trackCount/pageCount); i++) {
					var currentClass = '';
					var src = '?song='+songName+'&page='+i;

					if (i == page) {
						currentClass = 'active';
						src = '';
					}

					html += '<li class="'+currentClass+'">';
					html += '<a data-src="'+src+'" href="#">'+i+'</a></li>';
				}

				pagination.html(html);

				pagination.find('a').click(function(e) {
					var src = $(this).data('src');
					if (src) {
						$.ajax({
							type: "POST",
							url: sourceUrl+src,
							success: function(r) {
								renderSongSearchResponse(r);
							},
							dataType: 'json'
						});
					}
				});

				trackList.find('.music').click(function(e) {
					var $realTrackEl = $(this);

					if (!$realTrackEl.data('src')) {
						$.ajax({
							type: "GET",
							url: '/audio_player.php',
							data: {
								'track_id' : $realTrackEl.data('id')
							},
							success: function(response) {
								$realTrackEl.html($realTrackEl.html().replace(/\.\.\./ig, ' '+response.artist+' - '+response.track));
								$realTrackEl.data('src', response.url);
								$this.playMusic(app.domElems.audioPlayer, e, $realTrackEl.attr('id'));
							},
							dataType: 'json'
						});
					} else {
						$this.playMusic(app.domElems.audioPlayer, e, $realTrackEl.attr('id'));
					}
					return false;
				});

				trackList.find('.share').click(function() {
					var $val = app.domElems.inputMessage.val();
					app.domElems.inputMessage.val($val + $(this).data('src'));
					app.returnToChat();
				});
			}

			if (songName) {
				var l = Ladda.create(app.domElems.doMusicSearch.get(0));
				l.start();

				$.ajax({
					type: "POST",
					url: sourceUrl,
					data: {
						'song' : songName
					},
					success: function(response) {
						renderSongSearchResponse(response);
						l.stop();
					},
					error: function(response) {
						trackList.html('<td>'+response.status+' '+response.statusText+' '+response.responseText+'</td>');
						l.stop();
					},
					dataType: 'json'
				});
			}
		},
		playMusic: function($audio, e, musicElId) {
			var audioElRaw = $audio.get(0);
			var $realTrackEl = $('#'+musicElId);
			var $this = $(e.currentTarget);

			audioElRaw.addEventListener('ended', function() {
				$realTrackEl.find('.glyphicon-pause').removeClass('glyphicon-pause').addClass('glyphicon-play-circle');
			});

			if (audioElRaw.paused || audioElRaw.ended || $audio.data('current-track-id') != $this.attr('id')) {
				$('#'+$audio.data('current-track-id')).find('.glyphicon-pause').removeClass('glyphicon-pause').addClass('glyphicon-play-circle');

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