<form method="POST" action="audio.php">
	<input type="text" name="song" placeholder="Введите имя артиста или песни" maxlength="255" value="<?=htmlspecialchars($song)?>" style="width:80%">
	<input type="submit" value="Искать">
</form>