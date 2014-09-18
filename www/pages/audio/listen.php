<table>
	<td><b><?=$dao->getArtist()?></b> - <?=$dao->getSong()?></td>
	<td><?=$dao->getQuality()?> kbit/sec</td>
</table>
<br>
<audio controls>
	<source src="<?=$url?>" type='audio/mpeg; codecs="mp3"' >
	Ваш браузер не поддерживает тег audio!
</audio>
<br>
<input type="text" id="url-container" value="https://sociochat.me/audio.php?track_id=<?=$dao->getTrackId()?>"/>
<button id="copy-button" data-clipboard-target="url-container" data-clipboard-text="Default clipboard text from attribute" data-copied-hint="Скопировано!" title="Скопировать ссылку в буфер обмена">Скопировать ссылку в буфер обмена</button>
<br>
<h3><a href="<?=$url?>">Скачать</a></h3>
<p><small>СоциоЧат не хранит звуковые файлы. Источником данных служит сервис pleer.com</small></p>
<script src="js/zeroclipboard.min.js"></script>
<script>
	var client = new ZeroClipboard( document.getElementById("copy-button") );
</script>