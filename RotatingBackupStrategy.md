# Table of Contents #



# Rotating Sets of Incremental Backups Backup Strategy #

The Rotating Sets of Incremental Backups backup strategy, or _Rotating Backups_ for short, combines the space saving advantages offered by the Continuous Incremental strategy with some additional levels of safety.

As with the Continuous Incremental strategy, rather than taking a full backup of your database every time your scheduled backup task runs, this strategy will take a full backup, or _seed_, first and after that it will  take incremental backups storing only the changes, or _deltas_, since the last backup was taken.

This seed and it's corresponding deltas are referred to as a _Snapshot Group_. A snapshot group will continue to have incremental snapshots taken until a _rotation_ is triggered.

When a rotation occurs, a new Snapshot Group is created and a new full backup is taken to be the seed for the new group. Incremental backups will be taken from then until another rotation is triggered and the cycle repeats.

How rotation is triggered is configurable, however, by default the rotation will be triggered based upon the day of the week being Sunday.

<p align='center'><img src='http://xtrabackup-manager.googlecode.com/svn/wiki/images/xbm-rotating.png' /></p>

## Restores ##

Restores for this backup strategy work by first identifying the snapshot group of the snapshot to restore and then copying the group's relevant seed to the target restore directory. After this the software continues by applying each set of deltas from oldest to newest, in order to reach the point in time of the  snapshot selected for restore.

Similarly to the Continuous Incremental backup strategy, this means that restoring to the point in time of any seed will always be the fastest type of restore to perform, and restoring the most recent backup of a snapshot group will always take the longest amount of time. This is because restoring the latest backup will require applying each set of deltas, one by one, from oldest to newest.

This can be a problem when you consider that most commonly you will want to restore from the latest backup in the event of a system failure. To handle this, this backup strategy supports a feature called MaterializedBackups, which ensures that a fully materialized copy of the latest backup snapshot is always maintained in the backup system, allowing for faster restore times at the expense of disk space.

The feature is controlled by the parameter `maintain_materialized_copy` and is enabled by default.


## Retention Policy ##

The retention policy for the Rotating backup strategy is controlled by a parameter called `max_snapshot_groups`, which defaults to `2`.

When the retention policy is applied and the total number of snapshot groups for the given scheduled backup task exceeds the value of `max_snapshots`, the oldest snapshot group is deleted _in its entirety_.

The default `max_snapshot_groups` value of `2` combined with the default rotating trigger of the day of week being Sunday is equivalent to saying "keep the last 2 weeks of backups".

### Example ###

Assuming you are using the default rotating configuration and `max_snapshot_groups` of `2` and you started nightly backups of a particular system using this strategy just over two weeks ago, you would expect that upon successful completion of the first backup for week #3, the oldest backup snapshot group would be completely deleted.

As you can see in the diagram below, a new backup snapshot group for week #3 is created by taking a new full backup. After that successfully completes, the number of snapshot groups exceeds 2 and so the oldest snapshot group is removed.

<p align='center'><img src='http://xtrabackup-manager.googlecode.com/svn/wiki/images/xbm-rotating-retention.png' /></p>

This is just one example of how this backup strategy can be employed, however, the behaviour can be changed to suit business requirements by tweaking the parameters for the scheduled backup task.

## Parameters ##

All parameters are set individually against each scheduled backup task.

| **Parameter name** | **Valid Values** | **Default** | **Description** |
|:-------------------|:-----------------|:------------|:----------------|
| `rotate_method`    | `DAY_OF_WEEK` or `AFTER_SNAPSHOT_COUNT` | `DAY_OF_WEEK` | Whether to trigger group rotation based on the day of the week or after a certain number of snapshots are taken. |
| `rotate_day_of_week` | 0-6 - 0=Sunday 6=Saturday | 0 (Sunday)  | Which day(s) of week to trigger group rotation on. For multiple days, comma-separated values are accepted. Ignored unless `rotate_method` is `DAY_OF_WEEK`. |
| `max_snapshots_per_group` | >= 1             | 7           | The maximum number of snapshots that can exist within a snapshot group. In cases where `rotate_method` is `DAY_OF_WEEK`, it is possible for group rotation to be missed if no backups end up being run on the correct day of the week. If there are already `max_snapshots_per_group` snapshots in the group, no backup will be taken. See: `backup_skip_fatal`. |
| `backup_skip_fatal` | 0 or 1           | 1           | Whether or not a backup being skipped as a result of `max_snapshots_per_group` threshold being hit is considered fatal. |
| `rotate_snapshot_no` | >= 1             | 7           | How many snapshots a snapshot group can maintain before a group rotation is triggered. This only applied when using a `rotate_method` of `AFTER_SNAPSHOT_COUNT`. |
| `max_snapshot_groups` | >= 1             | 2           | How many snapshot groups may exist before the oldest is deleted. |
| `maintain_materialized_copy` | 0 or 1           | 1           | Whether or not the MaterializedBackups feature is enabled. |