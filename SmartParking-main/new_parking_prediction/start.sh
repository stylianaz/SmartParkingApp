#!/usr/bin/env bash

# sleep time helps to prevent wake before database is ready
sleep 30s

nohup python3 ml_scheduler.py &