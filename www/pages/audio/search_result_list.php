Найдено записей: <?=$response['count']?>
<br>
<?php
for ($i=1; $i <= ($response['count'] / 30); $i++) {
	echo '<a href="?song='.$song.'&page='.$i.'&token='.$token.'">'.$i.'</a> | ';
}
?>
<table>
	<thead>
	<th>Песня</th>
	<th>Качество (кбит/сек)</th>
	</thead>
	<?php foreach ($response['tracks'] as $id => $trackInfo) { ?>
		<tr>
			<td><a href="?token=<?=urlencode($token)?>&track_id=<?=urlencode($trackInfo['id'])?>"><?=$trackInfo['artist'].' - '.$trackInfo['track']?></td>
			<td><?=$trackInfo['bitrate']?></td>
		</tr>
	<?php } ?>
</table>