#!/bin/bash

for i in {1..10}
do
	RESULT=`ps aux | egrep AsteriskSocket.php | wc -l`

	if [ $RESULT == 2 ]; then
	    echo "$SERVICE is running"
	else
	    echo "$SERVICE stopped"
	    pkill -f  AsteriskSocket
	    /etc/init.d/AsteriskSocket start 
	fi
	sleep 5
done