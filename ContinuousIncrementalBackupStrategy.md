# Table of Contents #



# Continuous Incremental Backup Strategy #

The Continuous Incremental backup strategy is a more resource conscious way of maintaining snapshots of your databases at different points in time.

Rather than taking a full backup of your database every time your scheduled backup task runs, the Continuous Incremental method will take a full backup, or _seed_, first and after that it will only take incremental backups storing only the changes, or _deltas_, since the last backup was taken.

The seed and deltas are all stored separately on the storage volume that the scheduled backup task is configured to use.

<p align='center'><img src='http://xtrabackup-manager.googlecode.com/svn/wiki/images/xbm-continc.png' /></p>


## Restores ##

Restores for the Continuous Incremental backup strategy work by first copying the seed backup snapshot to the target directory and then applying each set of deltas from oldest to newest in order to reach the point in time of the backup snapshot you wish to restore to.

This means that restoring to the point in time of the seed will always be the fastest restore to perform, and restoring the most recent backup will always take the longest amount of time. This is because restoring the latest backup will require applying each set of deltas, one by one, from oldest to newest.

This can be a problem when you consider that most commonly you will want to restore from the latest backup in the event of a system failure. To handle this, this backup strategy supports a feature called MaterializedBackups, which ensures that a fully materialized copy of the latest backup snapshot is always maintained in the backup system, allowing for faster restore times at the expense of disk space.

The feature is controlled by the parameter `maintain_materialized_copy` and is enabled by default.


## Retention Policy ##

The retention policy for the Continuous Incremental backup strategy is controlled by a parameter called `max_snapshots`, which defaults to `7`. The default is based on the assumption that most commonly, people may wish to scheduled their backups once a day and plan to keep a week's worth of backup snapshots.

This parameter may be configured separately for each individual scheduled backup task.

The `max_snapshots` controls the maximum number of backup snapshots that may be kept in the backup system for the given task. This maximum is inclusive of _both_ the seed and all _incrementals_.

When the retention policy is applied and the total number of snapshots exceeds the value of `max_snapshots`, the oldest set of deltas is applied to the seed. The seed then represents a full backup as at the time of the set of deltas applied.

Once the deltas are successfully applied to the seed, they are deleted -- the process is repeated until the total number of snapshots no longer exceeds `max_snapshots`.

This creates an effect of that can be referred to as _rolling forward_ the seed. It effectively updates the seed, meaning that it can no longer be used to restore to the point in time that it was representing before the merge took place.

<p align='center'><img src='http://xtrabackup-manager.googlecode.com/svn/wiki/images/xbm-continc-retention.png' /></p>

## Parameters ##

All parameters are set individually against each scheduled backup task.

| **Parameter name** | **Valid Values** | **Default** | **Description** |
|:-------------------|:-----------------|:------------|:----------------|
| `max_snapshots`    | >= 1             | 7           | Controls the maximum number of backup snapshots retained. |
| `maintain_materialized_copy` | 0 or 1           | 1           | Whether or not the MaterializedBackups feature is enabled. |