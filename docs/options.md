# Options

## Config

The following config options are available (shown with their default values):

```php
// site/config/config.php

'distantnative.retour' => [
  // En-/disable all logging (true/false)
  // (when disabled, the failures tab and all stats are hidden)
  'logs' => true,

  // Array of paths to ignore as failures (can include placeholder wildcards or regular expressions)
  // in addition to the ones ignored via the Panel
  'ignore' => [],

  // Number of months after which logs should be deleted automatically
  // (checked from time to time when the Retour view is opened in the Panel;
  // `false` keeps them forever)
  'deleteAfter' => false,

  // Absolute path for location of redirects config
  // (or a closure returning the path)
  // (allows for yml and json files/formats)
  'config' => fn () => kirby()->root('config') . '/retour.yml',

  // Absolute path for location of database
  // (or a closure returning the path)
  'database' => fn () => kirby()->root('logs') . '/retour/log.sqlite',

  // Prefix shown in front of the path field in the Panel:
  // `true` uses your site's domain, a string your own
  // prefix (e.g. 'my.short.domain') and `false` disables it
  'site' => true,

  // HTTP status code preselected when adding a new redirect (e.g. 308),
  // `null` means new redirects start as disabled
  'status' => null,

  // forward the query string of the original request
  // (e.g. to preserve ad/campaign tracking)
  'preserveQuery' => false,
]
```

## Query strings

By default, redirects send visitors to the exact target you entered and drop the query string of the original request. If you run ads or campaigns, this means tracking parameters like `gclid`, `fbclid` or `utm_*` get lost on the way.

With the `preserveQuery` option enabled, Retour forwards the query string unchanged to the redirect target:

```php
// site/config/config.php

'distantnative.retour' => [
  'preserveQuery' => true
]
```

```
/old-page?utm_source=newsletter  =>  /new-page?utm_source=newsletter
```

If the target already has a query string of its own, the forwarded parameters are added to it. The option applies to all redirects with a `3xx` status code. Other status codes don't send the visitor to a new URL, so their query string stays untouched anyway.

## Permissions

To hide Retour from users of a certain role, disable access to its Panel area in the user blueprint:

```yaml
title: Editor

permissions:
  access: // [!code focus]
    retour: false // [!code focus]
```
