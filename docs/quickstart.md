# Getting started

## Requirements

| Kirby | Retour |
| ----- | ------ |
| 6     | 6 (requires PHP 8.3+) |
| 4, 5  | [<Badge type="info" text="v5.7.0" />](https://github.com/distantnative/retour-for-kirby/releases/tag/5.7.0)\* |
| 3     | [<Badge type="info" text="v4.5.0" />](https://github.com/distantnative/retour-for-kirby/releases/tag/4.5.0)\* |

\* without further support or development

## Install

There are two main ways to install this plugin:

1. [<Badge type="tip" text="Download" />](https://api.github.com/repos/distantnative/retour-for-kirby/zipball) & unzip
2. Copy this repository to `/site/plugins/retour`.
3. Visit the Panel view: `https://yourwebsite.com/panel/retour`

### With `composer`

Alternatively, you can install it with composer:

```bash
composer require distantnative/retour-for-kirby
```

## Panel menu

coming soon...

## Updates

Make sure to read the [release notes](https://github.com/distantnative/retour-for-kirby/releases) for breaking changes before you start the update.

1. [<Badge type="tip" text="Download" />](https://api.github.com/repos/distantnative/retour-for-kirby/zipball) & unzip the new version
2. Replace the `/site/plugins/retour` folder

Or, if you installed the plugin via composer, run:

```bash
composer update distantnative/retour-for-kirby
```

::: warning Create a backup
When updating, always make sure to first create backups of the redirects config (`/site/config/retour.yml`) and database (`/site/logs/retour/log.sqlite`).
:::
