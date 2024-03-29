# Options

## Config

The following config options are available:

```php
// site/config/config.php

'distantnative.retour' => [
  // En-/disable all logging (true/false)
  'logs' => true,

  // Array of paths to ignore as failures (can include placeholder wildcards or regular expressions)
  'ignore' => [],

  // Number of months after which logs should be deleted automatically
  'deleteAfter' => false,

  // Absolut path for location of redirects config
  // Default: site/config/retour.yml
  // (allows for yml and json files/formats)
  'config' => ...,

  // Absolut path for location of database
  // Default: $kirby->root('logs) + /retour/log.sqlite
  'database' => ...,

  // set your own string as prefix in the Panel dialog
  // (disable completely by setting to `false`)
  'site' => 'my.short.domain'
]
```

## Permissions

Moreover, Retour obeys to the following user blueprint permissions:

```yaml
title: Editor

permissions:
  access:
    retour: false
```
