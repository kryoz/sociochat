Sociochat
=========

This is source code of  <a href="https://sociochat.ru" target="_blank">SocioChat</a> in actual state.
The code is a bit rough in some places, lacking of tests, but pretty readable and managable.
I'll be happy with your feedback in form of <a href="https://github.com/kryoz/sociochat/issues">bugreports</a> or pull requests. 
See also official groups in <a href="http://vk.com/sociochat" target="_blank">VK</a> and <a href="https://www.facebook.com/groups/sociochat/" target="_blank">Facebook</a> 

SocioChat is licensed under the Apache Licence, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0.html).

## Features

* Very fast and full async websocket handling
* Adaptive markup via Twitter Bootstrap 3
* UI optimized to use with mobile devices
* HTML5 Notifications
* The concept "chat first - register later"
* Private chatting and personal banning
* Seamless reconnection on interruption of connection
* Flood protection
* Socionic dual chat roulette

## Installation

* PHP 5.5
* MySQL or PostgreSQL. Use corresponding schema sql files in <em>db</em> folder
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
* Configure web server (nginx/apache) for project host and edit <em>conf/local.ini</em>. This file overrides settings from default.ini. For example
```ini
db.name = chat
db.pass = 111
db.user = chat
db.scheme = pgsql
db.host = localhost

session.lifetime = 3600
session.timeout = 10
floodTimeout = 1
inviteTimeout = 10

logger =

domain.web = chat
domain.ws = ws://chat:8080

jsappfile = app.dev.js
metrika = 0
```
* Run from project root with command for development purpose
```bash
php bin/chat-server.php
```
To start in daemon mode use
```bash
nohup php bin/chat-server.php &
```
