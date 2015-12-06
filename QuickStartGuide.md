# Table of Contents #



# Introduction #

This guide should be all you need to get up and running with XtraBackup Manager quickly. The project is currently in Beta status, so **please use with caution in production environments** -- if you would like to play with the software and provide feedback, please do so!


# Requirements #

  * A Linux or Solaris-based system with cron, ssh, pv and netcat (nc)
  * MySQL server and command-line client 5.0.x or later - It is possible older versions will work.
  * Root access on the backup box and the database box(es) you plan to backup.
  * PHP 5 - Tested on 5.1.6 - Must also have php CLI - project expects it at `/usr/bin/php` and mysqli. (Eg, on debian based systems you need php5-cli and php5-mysql.)
  * xtrabackup 1.6.x series, 1.6.4 or later


# Installing and Configuring #

## Installing XtraBackup Manager ##

**Note: In the steps below, "xbm" is used as the mysql password for the user xbm. It is recommended you exchange that for your own password instead.**


  1. Install a barebones MySQL 5.0 or later somewhere - I suggest using the same host that you plan to run as your backup host. The DB load is very small.
  1. Create a system user account for the backup manager to run under - I use the username `xbm` but it doesn't matter. You **must** create a new user because XtraBackup Manager is going to completely take over the crontab of this user.
  1. Change user to your new user:
> > `shell> su - xbm`
  1. Edit $HOME/.profile and add a line: ulimit -n 65535
  1. Download the latest package from the [Downloads](http://code.google.com/p/xtrabackup-manager/downloads/list) section.
  1. Extract the tgz file - it will create a directory called "xtrabackup-manager".
> > `shell> tar xvzf xtrabackup-manager-noarch-0.81-r229.tgz`
  1. Connect to MySQL using the command-line client:
> > `shell> mysql -u root -p`
  1. Create a database schema for XtraBackup Manager to use in MySQL:
> > `mysql> CREATE DATABASE xbm CHARACTER SET utf8;`
  1. Setup a user for XtraBackup Manager to use:
> > `mysql> CREATE USER 'xbm'@'localhost' IDENTIFIED BY 'xbm';`<br />
> > `mysql> GRANT ALL PRIVILEGES ON xbm.* TO 'xbm'@'localhost';`<br />
> > Note: Access to the MySQL DB by this user will only originate from the XtraBackup Manager server itself, so unless the MySQL Databse you are using is remote, 'localhost' should suffice.
  1. Change directory to the xtrabackup-manager directory:
> > `shell> cd $HOME/xtrabackup-manager`
  1. Initialise the schema by running `sql/schema_init.sql` within the newly created database.
> > `shell> mysql -u xbm -pxbm xbm < sql/schema_init.sql`
  1. Open `includes/config.php` in your favourite editor and run through the various options. The comments in the file should make it fairly easy to understand what does what.
  1. Add the `xtrabackup-manager` directory to the `$PATH` of the XtraBackup Manager user and the root user.
  1. Test to make sure you can use the xbm command-line tool:
> > `shell> xbm host list`

You should see output like:

```
XtraBackup Manager v0.6 - Copyright 2011 Marin Software

-- Listing all Hosts --

        No hosts configured.

```

**Congratulations! You are ready to setup your backups**


## Preparing The Database Systems You Plan To Backup ##

### Setting up SSH Trust ###

The first thing you need to do is setup SSH trust between your backup host and any database hosts you plan to backup. There may be better ways of achieving passwordless SSH login to remote hosts, but this is the simplest way I know. Please feel free to provide feedback via Issues in Google Code.


  1. On the XtraBackup Manager Server host, change user to the XtraBackup Manager user:
> > `shell> su - xbm`
  1. Create yourself a SSH key -- just press ENTER when prompted to enter a passphrase - we need this to be passwordless so that XtraBackup Manager can use this key to connect to your remote hosts:
> > `shell> ssh-keygen -t dsa`
  1. Decide which user you are going to use to connect to the databases you wish to backup. XtraBackup Manager uses the **`mysql`** user by default, because this is the same user that `mysqld` runs under and therefore it will have the right permissions to the MySQL datadir for `xtrabackup` to be able to back up the database.
    * **NOTE:** You can change the Unix username that is used for any scheduled backup task with a command like:
> > > `shell> xbm backup edit <hostname> <backupName> backup_user <username>`
  1. Add the contents of the newly generated public key of your XtraBackup Manager User (`$HOME/.ssh/id_dsa.pub`) to the file called `$HOME/.ssh/authorized_keys` under the user you selected in the previous step on the remote database system you wish to backup. Create the .ssh directory in the home directory of that user if necessary.
  1. Ensure the permissions of the .ssh directory on the remote database host are set correctly:

> > `shell> chmod 700 .ssh`
  1. Ensure the permissions of the authorized\_keys file on the remote database host are set correctly:
> > `shell> chmod 600 .ssh/authorized_keys`
  1. Test connecting from your XtraBackup Manager server to your remote database host with the user you selected to confirm passwordless (key-based) login works:
> > `shell> ssh mysql@backuphost`
  1. Finally, verify that the hostname of your XtraBackup Manager server resolves correctly from your remote database host. This is required so that the backup data can be streamed over the network back to XtraBackup Manager.
> > `shell> host backup01`


Congratulations! You've now perform the necessary steps backup this remote database host with XtraBackup Manager.


### Install XtraBackup ###

XtraBackup needs to be installed and in the `$PATH` in two key places.

# The user that XtraBackup Manager will run under on your backup server
# The user you will use to connect to your remote database hosts - in our example above, this was the `mysql` user.

You should be able to find the information you need to download and install from the [Percona XtraBackup Download Page](http://www.percona.com/downloads/XtraBackup/).

After you have installed XtraBackup, test to see that you can run `xtrabackup` and `innobackupex` by just typing the command under the XtraBackup Manager user on the backup server host and under the remote user on your remote database host(s).

If not, you may need to setup some symlinks or add the directory with the XtraBackup binaries in it to your `$PATH`.


## Configuring Your Backups ##

### Setting up a Storage Volume ###

XtraBackup Manager needs to know where to store all of the backup data.

Make a directory somewhere with plenty of storage. Ensure the directory is owned/grouped to your XtraBackup Manager user and add it to XBM as a storage volume as follows:

  1. `shell> mkdir /backup1`
  1. `shell> chown xbm:xbm /backup1`
  1. `shell> xbm volume add "backup1" /backup1`

The above will add a storage volume with the name "backup1" at /backup1 to XtraBackup Manager. Easy!


### Adding a Remote Database Host To Backup ###

To backup a MySQL instance, you'll need to add the host to XtraBackup Manager and give it a description.

You can use the xbm command-line tool as follows:


> `shell> xbm host add "db01.mydomain.com" "Production DB #1"`

> Note: You will mostly refer to the remote database host by its hostname - the description is predominantly to make it easier for you keep track of your various hosts.


### Adding a Backup Task ###

Once you have setup a storage volume and added your remote database host, the next step is to schedule a backup task for that host.

Before we proceed, you should know that XtraBackup Manager supports a few different strategies for managing your backups. Since this is a quick start guide and it should remain simple, we're just going to just select a strategy without too much discussion.

It is important for you to keep in mind that you can only define which backup strategy to use at the time you add each scheduled backup task, so it will be important for you to read more about BackupStrategies before you start using this tool seriously.

For the purposes of this example, we're just going to go with ROTATING, but if you have the time, I highly recommend reading about BackupStrategies now.

Aside from selecting a backup strategy, you'll need to have the following information in order to add your scheduled backup task.

  * Hostname of the host you're backing up - you should have added it above.
  * Backup name or description you'd like to give the scheduled backup task.
  * CronExpression that defines when this backup task should be kicked off.
  * Name of the storage volume you want to use for storage for the backup - you should have added it above.
  * Path for the MySQL datadir on the host you wish to backup.
  * MySQL username and password that XtraBackup will use to connect to the MySQL instance that you wish to backup. It needs this to collect things like binary log and slave position details.

Once you have all of the above at hand, you can add the scheduled backup task with syntax as follows:

> `shell> xbm backup add <hostname> <backup_name> <strategy_code> <cron_expression> <backup_volume> <datadir_path> <mysql_user> <mysql_password>`

An example would be:

> `shell> xbm backup add "db01.mydomain.com" "nightlyBackup" ROTATING "30 20 * * *" "Storage Array 1" /usr/local/mysql/data backup "p4ssw0rd"`

**NOTE:** XtraBackup manager will default to using the Unix username 'mysql' to SSH into the database you wish to backup and run the backup. If you would like to change this you can use a command like the following:

> `shell> xbm backup edit "db01.mydomain.com" "nightlyBackup" backup_user myNewUser`