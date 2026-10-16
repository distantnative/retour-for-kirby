# Getting started

## Requirements

| Kirby | Retour |
| ----- | ------ |
| 6     | 6 (requires PHP 8.3+) |
| 4, 5  | [<Badge type="info" text="v5.7.0" />](https://github.com/distantnative/retour-for-kirby/releases/tag/5.7.0)\* |
| 3     | [<Badge type="info" text="v4.5.0" />](https://github.com/distantnative/retour-for-kirby/releases/tag/4.5.0)\* |

\* without further support or development

The 404 tracking stores its log in an SQLite database, so PHP needs the `pdo_sqlite` extension. Without it, you can still use the redirects by disabling the [`logs` option](/options#config).

## Install

1. [<Badge type="tip" text="Download" />](https://api.github.com/repos/distantnative/retour-for-kirby/zipball) & unzip
2. Rename the folder to `retour` and move it to `/site/plugins/retour`

### With `composer`

Alternatively, you can install it with composer:

```bash
composer require distantnative/retour-for-kirby
```

Once installed, you'll find Retour in the Panel menu or at `https://yourwebsite.com/panel/retour`.

## Panel menu

Retour adds a `Redirects` entry to the Panel menu, below Kirby's own entries. To move it somewhere else, use its id `retour` in the [`panel.menu` option](https://getkirby.com/docs/reference/system/options/panel). The list replaces the default menu, so include all entries you want to keep:

```php
// site/config/config.php

'panel' => [
  'menu' => [
    'site',
    'retour',
    '-',
    'users',
    'system'
  ]
]
```

## Where data is stored

Your redirects and ignored paths are stored in `/site/config/retour.yml`. You can commit this file to version control. But keep in mind that editors can change it in the Panel of your live site, so deploying an older copy would overwrite their changes.

The log behind the stats and failures is an SQLite database at `/site/logs/retour/log.sqlite`. It's runtime data, so don't commit it or overwrite it when deploying.

You can change both locations with the [`config` and `database` options](/options#config).

## Updates

Make sure to read the [release notes](https://github.com/distantnative/retour-for-kirby/releases) for breaking changes before you start the update.

::: warning Create a backup
When updating, always make sure to first create backups of the redirects config (`/site/config/retour.yml`) and database (`/site/logs/retour/log.sqlite`).
:::

1. [<Badge type="tip" text="Download" />](https://api.github.com/repos/distantnative/retour-for-kirby/zipball) & unzip the new version
2. Replace the `/site/plugins/retour` folder

Or, if you installed the plugin via composer, run:

```bash
composer update distantnative/retour-for-kirby
```

Retour updates log databases of earlier versions automatically the first time you open its Panel view afterwards. With a large log, this can take a few seconds once.
