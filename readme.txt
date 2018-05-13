
Some scripts to collect player history data from a sa-mp server.
Basically plquery.php gets run every 5 minutes by a cron job,
and other cron jobs run the other .php scripts that generate svg graphs.

This uses the amazing SVGGraph by goat1000:
  http://www.goat1000.com/svggraph.php
  https://github.com/goat1000/SVGGraph

Demo: https://robin.thisisgaming.org/pl/stats/

Database structure:

table: p
> i (Primary)  int(11)                     AUTO_INCREMENT
> n            char(24)  latin1_swedish_ci

table: t
> i (Primary)  int(11)
> t (Primary)  int(10)  UNSIGNED
> s            int(11)

