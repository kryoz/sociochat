#!/bin/bash
kill $(ps -A | grep 'sociochat.me' | awk '{print $1}')
