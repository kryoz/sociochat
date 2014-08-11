SocioChat
=========

This is source code of  <a href="https://sociochat.me" target="_blank">SocioChat</a>.
The code is a bit rough in some places, lacking of tests, but pretty readable and managable.

This repository has stopped updating since July 2014. The sources moved to a private repository due to lack of volunteers and starting of commercial features.

SocioChat is licensed under the Apache Licence, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0.html).

## The story

In the beginning of 2014 I was reading an article about ReactPHP and Ratchet. It looked really attractive and promising stack. At the same time I stumbled upon a yet another chat roulette. It was utilizing socionic theory about 16 types of information metabolizm (very close to MBTI) but appeared very buggy. It had all signs of poor code quality even without source code exposed. So if I decided to join that project it would result a complete rewrite. That's why SocioChat appeared as an alternative.

At first it had closed source code but one day I realized that it's really stupid to hide this work. Because maybe one day I'll exit the project and it will die without any useful contribution to society. 
So now thanks to that decision and GitHub, of course, you have an opportunity to learn something new and even influence to make the project better.

## Features

* Very fast and full async websocket handling
* Adaptive markup via Twitter Bootstrap 3
* UI optimized to use with mobile devices
* HTML5 Notifications
* English/Russian language support (still in progress)
* The concept "chat first - register later"
* Private chatting and personal banning
* Seamless reconnection on interruption of connection
* Flood protection
* Socionic/MBTI dual chat roulette
* Avatars

## Installation

* PHP 5.5
* MySQL or PostgreSQL. Use `phinx` utility to apply migrations.
* Give write permissions to `www/uploads/avatars`
* PECL extension 'libevent' is recommended to achieve the best speed
```bash
sudo apt-get install libevent-dev
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

jsappfile = main.js
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
