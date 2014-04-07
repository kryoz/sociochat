#!/bin/bash
kill $(ps -A | grep 'php' | awk '{print $1}')
