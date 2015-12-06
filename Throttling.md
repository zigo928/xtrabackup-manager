<p><img src='http://xtrabackup-manager.googlecode.com/svn/wiki/images/xbm-throttle-256.png' align='right' />

<h1>Throttling</h1>

Taking backups of your database systems is an important task, but it should never take such a toll on the system that it affects its ability to deliver service.<br>
<br>
Many administrators address this issue by running their backups during periods where the database is not very busy, but unfortunately not everybody has this luxury. The periods of time either simply don't exist, or they are not long enough.<br>
<br>
This is where throttling can really help!<br>
<br>
Rather than relying on the OS itself to balance the demands of the ongoing database operations and the backup task, throttling allows you to limit the amount of IO resources available to your backup, ensuring the rest of the system has a predictable amount of resources available to it for the duration of the backup.<br>
<br>
This feature is disabled by default on all backup tasks. Instructions on how to enable it are included below.<br>
<br>
<br>
<h2>Using Throttling in XtraBackup Manager</h2>

<b>Note:</b> Before attempting to enable throttling you must ensure that the <code>pv</code> tool is available on your XtraBackup Manager host and is in the <code>PATH</code> for the user that XtraBackup Manager runs as. More on <code>pv</code> can be found <a href='http://www.ivarch.com/programs/pv.shtml'>here</a>.<br>
<br>
Enabling throttling in XtraBackup Manager is actually very easy. You can limit the IO throughput to N MB/sec (megabytes) by setting the <code>throttle</code> parameter for any scheduled backup task.<br>
<br>
Example:<br>
<br>
<blockquote><code>shell&gt; xbm backup edit db01.mydomain.com nightlyBackup throttle 10</code></blockquote>

The above will ensure that the backup named "nightlyBackup" for db01.mydomain.com will not use more than 10 megabytes per second of IO.<br>
<br>
Throttling can be disabled by setting the value to 0.<br>
<br>
<b>NOTE:</b> The minimum amount of IO usage that <code>xtrabackup</code> will use during incremental backups is 3MB/sec.  Setting <code>throttle</code> to a value of <code>1</code>, <code>2</code> or <code>3</code> in XtraBackup Manager will all equate to a 3MB/sec IO usage in these cases. See below for more details.<br>
<br>
<br>
<h2>How it works</h2>

XtraBackup Manager uses two means of limiting the resources consumed.<br>
<br>
When streaming full backups over the network, the <code>pv</code> command-line tool is placed in the pipeline on the XtraBackup Manager server side. This tool allows you to limit the rate at which data flows through it, which in turn provides a sort of resistance in the pipeline, preventing the throughput from exceeding the specified amount.<br>
<br>
If taking an incremental backup the <code>--throttle</code> parameter is given to <code>innobackupex</code> which in turn passes it through to the <code>xtrabackup</code> command. In this case, when the <code>xtrabackup</code> is scanning the InnoDB data files for changed pages, it will limit the IOPs it uses.<br>
<br>
While <code>pv</code> allows you to limit in megabytes per second, the <code>--throttle</code> parameter for <code>xtrabackup</code> is specified in IOPs.<br>
<br>
Through testing it was determined that <code>xtrabackup</code> will actually consume at least 2MB/sec for scanning the InnoDB log files, plus 1MB times the value given as the <code>--throttle</code> parameter. That is to say, <code>xtrabackup</code> for <code>--throttle=N</code>, it will actually use <code>N + 2MB/sec</code>.<br>
<br>
XtraBackup Manager attempts to simplify things for the user as much as possible and masks this quirk.<br>
<br>
When you configure XtraBackup Manager to throttle at 4MB/sec by setting the <code>throttle</code> parameter to <code>4</code>, it will take into account this 2MB/sec used for log file scanning and only give a <code>--throttle</code> value of <code>2</code> to <code>xtrabackup</code>, resulting in an actual IO throughput utilisation much closer to the 4MB/sec specified.<br>
<br>
Additionally, since the minimum amount of IO usage that <code>xtrabackup</code> will use during incremental backups is 3MB/sec, setting <code>throttle</code> to a value of <code>1</code>, <code>2</code> or <code>3</code> in XtraBackup Manager will all equate to a 3MB/sec IO usage.<br>
<br>
<br>
<p align='center'>
<font size='1'>The throttle image is credited to <a href='http://www.psdgraphics.com'>psdGraphics.com</a>.</font>
</p>