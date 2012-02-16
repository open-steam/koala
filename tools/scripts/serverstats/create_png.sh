#!/bin/bash

STEP=60
echo Run with step $STEP

while sleep $STEP
do
	echo updating images
	# 15 min - steam
	nice -n 19 rrdtool graph steam15m.png --start -900 -a PNG --vertical-label "sTeam" -w 600 -h 100 \
	DEF:auswertung=server.rrd:processes:AVERAGE LINE1:auswertung#ff0000:"sTeam Last"
	
	# 1 Stunden - steam
	nice -n 19 rrdtool graph steam1h.png --start -3600 -a PNG --vertical-label "sTeam" -w 600 -h 100 \
	DEF:auswertung=server.rrd:processes:AVERAGE LINE1:auswertung#ff0000:"sTeam Last"

	# 36 Stunden - steam
	nice -n 19 rrdtool graph steam36h.png --start -129600 -a PNG --vertical-label "sTeam" -w 600 -h 100 \
	DEF:auswertung=server.rrd:processes:AVERAGE LINE1:auswertung#ff0000:"sTeam Last"

	# 7 Tage - steam
	nice -n 19 rrdtool graph steam1w.png --start -604800 -a PNG --vertical-label "sTeam" -w 600 -h 100 \
	DEF:auswertung=server.rrd:processes:AVERAGE LINE1:auswertung#ff0000:"sTeam Last"
	
	# 15 min - user
	nice -n 19 rrdtool graph user15m.png --start -900 -a PNG --vertical-label "Nutzer" -w 600 -h 100 \
	DEF:auswertung=server.rrd:users:AVERAGE LINE1:auswertung#00ff00:"# Nutzer"
	
	# 1 Stunden - user
	nice -n 19 rrdtool graph user1h.png --start -3600 -a PNG --vertical-label "Nutzer" -w 600 -h 100 \
	DEF:auswertung=server.rrd:users:AVERAGE LINE1:auswertung#00ff00:"# Nutzer"

	# 36 Stunden - user
	nice -n 19 rrdtool graph user36h.png --start -129600 -a PNG --vertical-label "Nutzer" -w 600 -h 100 \
	DEF:auswertung=server.rrd:users:AVERAGE LINE1:auswertung#00ff00:"# Nutzer"

	# 7 Tage - user
	nice -n 19 rrdtool graph user1w.png --start -604800 -a PNG --vertical-label "Nutzer" -w 600 -h 100 \
	DEF:auswertung=server.rrd:users:AVERAGE LINE1:auswertung#00ff00:"# Nutzer"
done