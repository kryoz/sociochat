<?php
define('ROOT', 1);
$title = 'Частые вопросы';
require_once "pages/header.php";
?>
<body>
	<div class="container" id="wrapper">
		<div class="navbar navbar-default">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span><span class="glyphicon glyphicon-cog"></span>
					</button>
					<a href="#chat" class="navbar-brand navbar-left tab-panel" data-toggle="tab">СоциоЧат</a>
				</div>
				<div class="collapse navbar-collapse">
					<ul role="navigation" class="nav navbar-nav">
						<li>
							<a href="http://vk.com/topic-66015624_29370149" target="_blank" class="tip" title="Задать вопрос в группе ВК"><span class="glyphicon glyphicon-question-sign"></span> Задать вопрос в группе ВК</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				Частые вопросы
			</div>
			<div class="panel-body">
				<div id="accordion" class="panel-group">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#socionic">Что такое соционика?</a>
							</h4>
						</div>
						<div id="socionic" class="panel-collapse collapse">
							<div class="panel-body">
								<p>Соционика - это концепция из сферы психологии, имеющая в основе типологию Юнга. <br>
									Вы наверняка замечали, что с одними людьми нам бывает комфортно и легко находится общий язык, с другими возникают непримиримые противоречия в мировосприятии.
									Соционика позволяет вполне чётко обрисовать причины таких ситуаций.<br>
									Прежде всего, соционика постулирует информационную модель личности на уровне архетипа, то есть нечто фундаментальное, сохраняющее стабильность на протяжении всей жизни человека.<br>
									Опираясь на  эту модель можно спрогнозировать как сильные, так и слабые стороны индивида, рассмотреть взаимодействия с другими моделями - то есть с окружающими людьми.<br>
									Это также позволяет дать рекомендации по выбору профессиональной деятельности, выстроить стратегию общения с проблемными людьми и конечно же по поиску максимально совместимого партнёра.<br>
									В соционике существует только 16 видов моделей или как ещё называют их "типов информационного метаболизма", сокращённо ТИМ. <br>
									Вы можете здесь возмутиться: ведь люди представляют собой гораздо более разнообразную массу. Но противоречия здесь нет. Во-первых, вспомним, что мы говорим о моделях, а модели всего лишь абстрактное упрощение реального, во-вторых, разглядеть архетип за огромными пластами жизненного опыта человека задача непростая. Можете представить ТИМ как скелет, а жизненный опыт в виде мягких тканей, покрывающих его.<br>
									Тем не менее, с одной стороны, нельзя назвать соционику наукой в строгом смысле, но с другой - неослабевающий интерес к ней на протяжении многих лет доказывает, что это не просто теория о сферических личностях в вакууме, а нечто находящее подтверждение в реальном мире.
									Более подробно ознакомиться вы можете с помощью запроса в любимой поисковом сервисе.
								</p>
							</div>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#f1">У меня не работает чат или окно чата растягивается за пределы экрана вниз</a>
							</h4>
						</div>
						<div id="f1" class="panel-collapse collapse">
							<div class="panel-body">
								<p>Поддерживаются современные браузеры Chrome, Safari, Firefox и Opera старше 15-й версии. Для мобильных устройств аналогично, за исключением Android Browser, Opera Mobile или Mini.</p>
							</div>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#f2">Хочу чтобы можно было настроить цвет ника и текста, а также всякие смайлики!</a>
							</h4>
						</div>
						<div id="f2" class="panel-collapse collapse">
							<div class="panel-body">
								<p>Этого не будет сделано по соображениям минималистичности и удобства восприятия чата.</p>
							</div>
						</div>
					</div>

					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#f3">Я не могу зайти под свои именем снова.</a>
							</h4>
						</div>
						<div id="f3" class="panel-collapse collapse">
							<div class="panel-body">
								<p>Скорее всего, вы заходили под своим именем с другого браузера, компьютера или иного устройства. Или использовали анонимный режим браузера. Используйте другую вариацию имени. Если вы не заходите более 3-х дней со своего имени, то привязка удаляется. Используйте регистрацию для избежания подобных ситуаций.</p>
							</div>
						</div>
					</div>

					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#f4">Что делает кнопка "Искать дуала"?</a>
							</h4>
						</div>
						<div id="f4" class="panel-collapse collapse">
							<div class="panel-body">
								<p>Это специальный режим, при котором вы начинаете искать дуала с целью пообщаться с глазу на глаз. Поскольку в системе вы можете быть не единственным желающим найти такого же дуала, то возможно это займёт некоторое время. Также в этом режиме вы не можете изменять свои настройки по понятным причинам, для этого вам надо нажать "Прекратить поиск". </p>
							</div>
						</div>
					</div>

					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#f5">Если я закрою вкладку браузера или интернет отвалится в этом режиме, то мы потеряем друг друга?</a>
							</h4>
						</div>
						<div id="f5" class="panel-collapse collapse">
							<div class="panel-body">
								<p>Нет :) но при условии, что ваш партнер дождётся возвращения.</p>
							</div>
						</div>
					</div>

					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#f6">Могу ли я написать сообщение собеседнику в общем чате (паблике), чтобы никто другой не видел?
									<br>Можно ли писать приватно, не покидая общий чат?</a>
							</h4>
						</div>
						<div id="f6" class="panel-collapse collapse">
							<div class="panel-body">
								<p>Да, это возможно, если вы дважды кликните на имя этого человека. Чтобы снова написать в паблик нажмите X возле кнопки отправки сообщения. Можно также выбрать из выпадающего списка справа от неё.</p>
							</div>
						</div>
					</div>

					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#f7">На чём написан чат? Выложи пожалуйста исходники!</a>
							</h4>
						</div>
						<div id="f7" class="panel-collapse collapse">
							<div class="panel-body">
								<p>Все подробности изложены в статье <a href="http://habrahabr.ru/post/218751/" target="_blank">на Хабре</a></p>
								<p>Исходники здесь <a href="https://github.com/kryoz/sociochat" target="_blank">https://github.com/kryoz/sociochat</a></p>
							</div>
						</div>
					</div>

					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#f8">Есть ли контакт для связи с админом?</a>
							</h4>
						</div>
						<div id="f8" class="panel-collapse collapse">
							<div class="panel-body">
								<p><a href="mailto:webmaster@sociochat.me">webmaster@sociochat.me</a></p>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</body>
</html>