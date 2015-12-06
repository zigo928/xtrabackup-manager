# Table of Contents #



# Full Only Backup Strategy #

This is the simplest backup strategy supported -- XtraBackup Manager will only ever take full backups of the target host each time the backup task runs.
<p align='center'><img src='http://xtrabackup-manager.googlecode.com/svn/wiki/images/xbm-fullonly.png' /></p>

**IMPORTANT NOTE:** At the time of writing, this method is actually unsupported. You can emulate this with the `Rotating Sets of Incremental Backups` method. Simply use backup strategy code `ROTATING` and set the following parameters for the backup:

  * `rotate_method = AFTER_SNAPSHOT_COUNT`
  * `max_snapshot_groups = N` -- N is the number of full backups to keep before deleting the oldest.
  * `rotate_snapshot_no = 1`


## Restores ##

Restores for this backup strategy are not very complicated. The full back you wish to restore is simply copied to the target directory for the restore and the process is complete.


## Retention Policy ##

The retention policy functionality of the Full Only backup strategy is similarly simple.

There is one parameter, configurable separately against each scheduled backup task, that controls the maximum number of snapshots that may be kept in the backup system for that task.

This parameter is called `max_snapshots` and defaults to `7`. The default is based on the assumption that most commonly, people may wish to scheduled their backups once a day and plan to keep a week's worth of backup snapshots.

You may change this value at any time and it will be used at the next application of the retention policy for the scheduled backup task.

When the retention policy is applied, if the total number of snapshots exceeds `max_snapshots` then the oldest snapshot will be deleted. This process is repeated until the total number of snapshots no longer exceeds `max_snapshots`.


### Example ###

Assuming you are using the default configuration with `max_snapshots` of `7` and you started nightly backups of a particular system using this strategy just over a week ago, you would expect that upon successful completion of the eighth backup, the oldest backup snapshot would be deleted.

<p align='center'><img src='http://xtrabackup-manager.googlecode.com/svn/wiki/images/xbm-fullonly-retention.png' /></p>

## Parameters ##

All parameters are set individually against each scheduled backup task.

| **Parameter name** | **Valid Values** | **Default** | **Description** |
|:-------------------|:-----------------|:------------|:----------------|
| `max_snapshots`    | >= 1             | 7           | The maximum number of snapshots to maintain before the oldest is deleted. |