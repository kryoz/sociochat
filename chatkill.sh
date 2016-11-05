#!/bin/bash
kill $(ps -x | grep 'sociochat.daemon' | awk '{print $1}')
