#/bin/sh

STEP=5

echo Run with step $STEP


while sleep $STEP
do
	LAST=$(ps -eo pcpu,args | grep "steam server/server.pike" | grep -v grep | awk '{print $1}' | sed 's/,/\./g')
	USER=$(php active_users.php)
	echo N:$LAST:$USER
	rrdtool update server.rrd N:$LAST:$USER
done