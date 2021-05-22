---
title: Updates
description: Updates the Nuber app
extends: _layouts.documentation
section: content
---

# Software updates

Enter the `nuber` container and go into the app directory

On your LXD host run the following commands:

```bash
$ lxc exec nuber-app bash
$ cd /var/www
```

Run updater

```bash
$ vendor/bin/updater update --all
     __  __          __      __
    / / / /___  ____/ /___ _/ /____  _____
   / / / / __ \/ __  / __ `/ __/ _ \/ ___/
  / /_/ / /_/ / /_/ / /_/ / /_/  __/ /
  \____/ .___/\__,_/\__,_/\__/\___/_/
      /_/

version 0.1.0

- Checking for updates nuber-io/nuber (0.1.0)
- Downloading nuber-io/nuber (0.2.0)
- Running before scripts
 > bin/console maintenance:start
- Extracting nuber-io/nuber (0.1.0)
- Running after scripts
 > composer update --no-interaction
 > bin/console db:migrate
 > bin/console maintenance:end
- Updating lock file
```
