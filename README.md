Sociochat.ru
=========

This is source code of  <a href="https://sociochat.ru" target="_blank">SocioChat</a> in actual state.
The code is a bit rough in some places, lacking of tests, but pretty readable and managable.
I'll be happy with your feedback in form of bugreports or pull requests.

SocioChat is licensed under the Apache Licence, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0.html).

## Features

* Very fast and full async websocket handling
* Adaptive markup
* HTML5 Notifications
* The concept "chat first - register profile later"
* Private chatting and personal banning
* Seamless reconnection on interruption of connection
* Flood protection
* Socionic dual chat roulette

## Installation

* PHP 5.5
* MySQL or PostgreSQL. Use schema sql files in <em>db/</em> accordingly
* PECL extension 'libevent' is recommended to achieve the best speed
```bash
sudo apt-get install libevent libevent-dev
sudo pecl install libevent
```
Don't forget to enable this extension in php-cli
* Get composer and do in project root
```bash
$ php composer.phar update
```
to get all necessary libraries
* Configure web server (nginx/apache) for project host and edit conf/local.ini. This file overrides settings from default.ini
* Run from project root with command
```bash
php bin/chat-server.php
```
