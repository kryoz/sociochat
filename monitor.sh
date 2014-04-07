#!/bin/bash
PID=$(ps -A | grep 'php' | awk '{print $1}')
while [ -e /proc/$PID ] ; do
	sleep 60
done
cd /var/www/sociochat.ru
tail nohup.out | mail -s "Chat died!" webmaster@sociochat.ru
