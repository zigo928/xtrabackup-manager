<p><img src='http://xtrabackup-manager.googlecode.com/svn/wiki/images/xbm-materialized-256.png' align='right' />

<h1>Materialized Backups</h1>

The materialized backups feature is used to make it faster to restore the most recently taken backup snapshot for any scheduled backup task.<br>
<br>
As outlined in the various BackupStrategies documentation, depending on timing, restoring the most recent backup could potentially involve copying a full backup snapshot to the target restore location and then applying many sets of deltas, one by one, until the most recent set of deltas available has been applied.<br>
<br>
This process can be very time consuming, and time is often not a luxury one can afford when a system failure has occurred and a restore is needed.<br>
<br>
When enabled, this feature simply ensures that there is always a fully materialized copy of the most recent backup available to restore. This essentially means that restoring is almost entirely a case of just copying the data to the desired location.<br>
<br>
The system works in an intelligent manner and will only use additional disk space when necessary.<br>
<br>
For example, if XtraBackup Manager had just completed taking a full backup, no additional snapshot copy would be made because if a restore was required, the full backup itself could just be copied without any extra work.<br>
<br>
On the other hand, if the system had just completed taking its first incremental backup after a full backup, it would copy the full backup to a location in the configured Storage Volume and then apply the deltas for the incremental that was just taken. In this case, if a restore was required, there is no time wasted applying deltas -- the materialized snapshot can simply be copied to the target restore location and used from there.<br>
<br>
The materialized backups feature is available on both the <a href='ContinuousIncrementalBackupStrategy.md'>Continuous Incremental</a> & <a href='RotatingBackupStrategy.md'>Rotating Sets of Incrementals</a> backup strategies. It is enabled by default and is controlled by the <code>maintain_materialized_copy</code> parameter.<br>
<br>
<p align='center'>
<font size='1'>The wand image is credited to <a href='http://www.psdgraphics.com'>psdGraphics.com</a>.</font>
</p>