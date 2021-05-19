---
title: Backups
description: How to work with backups on a LXD container
extends: _layouts.documentation
section: content
---

# Backups

From the instances home, click on the **instance** and then click on the **backups tab** in the left-hand side.

![alt text](/assets/img/nuber/instance-backups.png)

## Scheduling a Backup

Select the **frequency** of the backups, and enter the amount of backups that you want to retain in the `keep` input and then click on the **schedule** button.

To delete the schedule, click on the **x icon** next to the schedule that you want to delete.

## Restoring a Backup

> Rememember that when you restore a backup, any backups after that point in time will no longer exist.

To restore a backup, click on the **restore icon** next to the backup that you want to restore. A confirmation
dialog will then be shown before the instance is restored.

## Deleting a Backup

To delete a backup, click on the **x icon** next to the backup that you want to delete. A confirmation
dialog will then be shown before the backup is deleted.
