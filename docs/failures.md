# 404 tracking

Retour allows you to track requests to your site that Kirby didn't serve, e.g. as the requested page does not exist. You find them in the `Failures` tab of Retour's Panel view. This is valuable information for you to know where something might not be working correctly. You can see where your users fail.

![Failures tab](/failures.webp)

## Stats and timespan

Above the tables in both tabs, Retour shows stats for all logged requests. The pie chart shows how they split up, the timeline how they are spread over time:

- **Redirected:** requests handled by one of your redirects
- **Resolved:** failures that you later turned into a redirect via [`Add as redirect`](#add-as-redirect)
- **Failed:** failures that are still open

Depending on the timespan, the timeline shows hours, days or months. Click on a day or month to zoom in on it.

When you open up Retour, you will be shown the data of the current month. The selected timespan not only applies to the stats graphs, but also the data displayed in the tables. This is particularly relevant for the failures as only those that occurred during the selected timespan are shown. But it also applies to the hits column in the `Routes` tab.

You can change the selected timespan by using the navigation bar: moving between previous and next month or even changing the span to a full year, a day or any custom interval:

![Change timespan](/timespan.webp)

## Add as redirect

Every table row offers the option (behind the three dots to the right) to create a new redirect based on the failed request, which will be pre-filled with the path to prevent any more failures in the future.

![Options of a failure](/failure-options.webp)

Once you save that new route, all failure entries for that path will also be marked as resolved in the stats.

## Ignore failures

Bots and scanners request lots of paths that will never exist on your site, like `wp-login.php` or `.env`. To keep them out of your log, choose `Ignore` in the options of the table row. Before confirming, you can turn the path into a pattern with [placeholders](/redirects#path), e.g. `wp-admin(:all)`, to ignore a whole group of paths at once. Requests to ignored paths won't be logged anymore and all existing failures matching the pattern get removed from the log.

![Ignore a failure](/ignore.webp)

The `Ignored` button above the failures table lists all ignored paths, where you can also remove them again or add new ones. With one click, you can add a preset of paths that bots commonly probe for vulnerabilities, e.g. `wp-login.php`, `wp-admin`, `xmlrpc.php` or `.env`. All ignored paths are stored alongside your redirects in `site/config/retour.yml`.

![Ignored paths](/ignores.webp)

Additionally, you can ignore paths with the [`ignore` option](/options#config) in your `config.php`.

### Block at the server level

Ignoring a path only keeps it out of the failures log. The request still reaches Kirby, which renders your error page for it. For paths that bots hit a lot, it's better to block them in your web server so they never reach PHP at all.

**Apache:** add this to your `.htaccess`, right after `RewriteEngine on`:

```apache
RewriteRule ^(wp-admin|wp-login\.php|xmlrpc\.php|\.env) - [F]
```

**nginx:** add this to your `server` block, before any other `location` blocks:

```nginx
location ~ ^/(wp-admin|wp-login\.php|xmlrpc\.php|\.env) {
  return 403;
}
```

## Clearing logs

Depending on your use case, you might want to clear the logs from time to time. This can be either done manually by clicking the button above the failures table. You can clear the complete log or only the open failures, which keeps the redirected and resolved stats:

![Clear the log](/clear.webp)

Or automatically via the [`deleteAfter` option](/options#config), which removes all entries older than the given number of months (by default 12). Retour checks for outdated entries from time to time when someone opens it in the Panel.

To remove a single failure from the log, choose `Remove` in the options of its table row.
