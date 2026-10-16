# Options

## Config

The following config options are available:

```php
// site/config/config.php

'distantnative.retour' => [
  // En-/disable all logging (true/false)
  'logs' => true,

  // Array of paths to ignore as failures (can include placeholder wildcards or regular expressions)
  // in addition to the ones ignored via the Panel
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

  // set default HTTP status code the redirect will respond with
  // Default: `null` (which disables the redirect)
  'status' => 308,

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

Moreover, Retour obeys to the following user blueprint permissions:

```yaml
title: Editor

permissions:
  access: // [!code focus]
    retour: false // [!code focus]
```
