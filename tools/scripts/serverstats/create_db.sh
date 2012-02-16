#/bin/sh

STEP=5

echo Create dbs with step $STEP

rrdtool create server.rrd --step $STEP \
		DS:processes:GAUGE:120:U:U \
		DS:users:GAUGE:120:U:U \
		RRA:AVERAGE:0.5:1:2160 \
		RRA:AVERAGE:0.5:5:2016 \
		RRA:AVERAGE:0.5:15:2880 \
		RRA:AVERAGE:0.5:60:8760 \
		RRA:MAX:0.5:1:2160 \
		RRA:MAX:0.5:5:2016 \
		RRA:MAX:0.5:15:2880 \
		RRA:MAX:0.5:60:8760
		