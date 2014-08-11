<table>
	<td><b><?=$dao->getArtist()?></b> - <?=$dao->getSong()?></td>
	<td><?=$dao->getQuality()?> kbit/sec</td>
</table>
<br>
<audio controls autoplay>
	<source src="<?=$url?>" type="audio/mp3" >
	Ваш браузер не поддерживает тег audio!
</audio>
<br>
<input type="text" id="url-container" value="https://sociochat.me/audio.php?track_id=<?=$dao->getTrackId()?>"/>
<button id="copy-button" data-clipboard-target="url-container" data-clipboard-text="Default clipboard text from attribute" data-copied-hint="Скопировано!" title="Скопировать ссылку в буфер обмена">Скопировать ссылку в буфер обмена</button>
<br>
<a href="<?=$url?>">Скачать</a>
<a href="https://sociochat.me/audio.php">Искать другую композицию</a>
<p><small>СоциоЧат не хранит звуковые файлы. Источником данных служит сервис pleer.com</small></p>
<script src="js/zeroclipboard.min.js"></script>
<script>
	var client = new ZeroClipboard( document.getElementById("copy-button") );
</script>