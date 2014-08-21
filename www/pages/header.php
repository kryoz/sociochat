<?php
if (!defined('ROOT')) {
	die('not allowed');
}
$title = isset($title) ? $title : 'соционический чат с дуалами';
$js = isset($js) ? $js : '';
$domain = isset($domain) ? $domain : 'https://sociochat.me';
$meta = isset($meta) ? $meta : '';
?><!doctype html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta charset="utf-8" />
	<title>SocioChat - <?=$title?></title>
	<meta property="og:image" content="<?=$domain?>/img/sociochat.jpg">
	<meta property="og:title" content="SocioChat" />
	<meta property="og:description" content="Удобный современный и быстрый соционический чат. Здесь находят дуалов и новых друзей! Оптимизирован под мобильные устройства." />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<meta name="description" content="Удобный современный и быстрый соционический чат. Здесь находят дуалов и новых друзей! Оптимизирован под мобильные устройства.">
	<meta name="keywords" content="соционика, мобильные знакомства, чат, дуалчат">
	<?=$meta?>
	<link rel="icon" href="<?=$domain?>/favicon.ico" type="image/x-icon">
	<link rel="apple-touch-icon" href="<?=$domain?>/img/sociochat.jpg">
	<link rel="stylesheet" href="<?=$domain?>/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?=$domain?>/css/styles.css" >
	<script type="text/javascript" src="<?=$domain?>/js/jquery.min.js"></script>
	<script type="text/javascript" src="<?=$domain?>/js/bootstrap.min.js"></script>
<?=$js?>
</head>
