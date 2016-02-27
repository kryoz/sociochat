#!/bin/bash
kill $(ps -x | grep 'sociochat.me' | awk '{print $1}')
