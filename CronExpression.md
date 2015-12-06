# Definition #

Cron is a time-based scheduler for Unix-like operating systems. A Cron Expression is an expression that defines at what times a specific job or command should be triggered.

XtraBackup Manager uses Cron Expressions to define when backup tasks should run, as they are well understood by most Unix/Linux system administrators.

In most cases the general form of Cron Expressions for use with XtraBackup manager are of the five field variety.

The fields are separated by spaces or tabs and are integer based patterns that specify the following.

  * minute (0-59),
  * hour (0-23),
  * day of the month (1-31),
  * month of the year (1-12),
  * day of the week (0-6 with 0=Sunday).

For more information on cron expressions I'd suggest reading `man crontab` on your system.