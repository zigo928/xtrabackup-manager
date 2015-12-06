# Backup Strategies Explained #

XtraBackup Manager provides some different backup strategies that are used to control things like:

  * When should a FULL backup snapshot be taken?
  * When should an INCREMENTAL backup snapshot be taken?
  * How long should backups be kept for?

There are currently three backup strategies in XtraBackup Manager and each has a code that is really just a shorthand way of referring to the strategy within the software.

They are:

  * [Full Only](FullOnlyBackupStrategy.md) - Code: `FULLONLY`
  * [Continuous Incremental](ContinuousIncrementalBackupStrategy.md) - Code: `CONTINC`
  * [Rotating Sets of Incremental Backups](RotatingBackupStrategy.md) - Code: `ROTATING`

Each backup strategy controls when or _if_ to take FULL or INCREMENTAL backups and in addition has a different means of controlling when old backups should be deleted. The rules for when backups should be deleted are referred to in XtraBackup Manager as the _retention policy_.

The retention policy is _only_ applied upon successful completion of any backup.

Each of the backup strategies is explained in more detail at the linked sections above.