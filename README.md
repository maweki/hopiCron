#hopiCron

##WHAT IS IT?
hopiCron (hpc) is free software and comes without any warranty.
It is an implimentation of a cron deamon for cheap webspace that comes
without cronjobs.
It is licenced unter the GNU GPL



##HOW DOES IT WORK?
hpc calls itself by means of an http-query. Because of a smart combination
of a sleeper and a dispatcher job, hpc draws nearly no resources and
produces only two http queries a minute (in its default setting).

##HOW DO I WORK IT?
You write one or many crontab files and start the hpc.php. There you
select "start" and enter your password (from the configuration file).
From there on, the dispatcher and the sleeper start alternately
and the dispatcher will dispatch every job as an http query as soon
as its rules allow it to.

##IS THERE ANYTHING I SHOULD KNOW?
First of all, you should know how to write a crontab file.
Then, remember, jobs are called as an http query. You will generate
a pagehit for every time something is called. This is by default
at least 2880 (2*24*60) hits a day (not including dispatched jobs).
Two hits a minute are not much but it could throw of your statistics.
