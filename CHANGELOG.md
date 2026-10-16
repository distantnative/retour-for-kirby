# Changelog

## 6.0.0-beta.1

Retour 6 is the first version for Kirby 6. If you're on Kirby 4 or 5, keep using Retour 5.7.

### Breaking changes

- Requires Kirby 6 and PHP 8.3 or newer
- The `kirby retour:indices` command has been removed. Logs of earlier versions are now updated automatically the first time you open Retour in the Panel: unused indices are dropped and the index for redirect hits gets smaller.
- Log entries older than 12 months are now deleted automatically (the `deleteAfter` option defaults to `12`). Set it to `false` to keep them forever.
- The PHP API has changed significantly. If your code uses Retour's classes directly, check it against the new version.

### Features

- Ignore failures from the Panel: every failure has a new "Ignore" option that adds its path, or a pattern based on it, to the ignored paths and removes its failures from the log
- New "Ignored" button on the failures tab to manage the ignored paths. It also lists the ones from the `ignore` option and adds common scanner paths (`wp-*`, `xmlrpc.php`, `.env`, `.git/…`) in one go.
- New `ignoreIps` option: requests from these IP addresses or ranges (e.g. `192.0.2.0/24`) aren't logged at all, e.g. the ones of your hosting provider's security scanner. Redirects still apply to them.
- New "Week" timespan to view the stats, failures and hits of a single week. Weeks start on the same day as the calendar in the Panel's date fields.
- New `cache` option: the parsed redirects config is cached until the file changes. On by default; set it to `false` or pass Kirby cache options, e.g. `['type' => 'apcu']`.
- New translations: Czech, Danish, Italian, Japanese, Korean, Polish, Portuguese (Portugal) and Spanish (Latin America)
- New icons in the Panel: a redirect icon for Retour in the menu, icons for the redirects and failures tabs and more fitting icons in the stats, tables, drawers and dialogs

### Fixes

- A `retour.yml` that can't be parsed is no longer replaced with an empty list on the next save in the Panel. Redirects are turned off until it's fixed, and the Panel shows the error.
- Saving a redirect with an invalid path pattern or status code fails with an error message instead of breaking routing for every request
- Redirects now also apply to ignored paths; ignoring a path only stops its failures from being logged
- HEAD requests (e.g. from link checkers and uptime monitors) are now redirected as well; they aren't logged, so they don't inflate the stats
- Redirects no longer show draft pages, or pages inside drafts, to visitors and don't redirect to them
- Redirect targets that aren't pages, e.g. `downloads/file.pdf`, are now resolved against the site URL. Before, browsers resolved them relative to the requested path, which broke nested paths and sites in a subfolder.
- Redirects to a page that has been deleted (e.g. a `page://…` UUID target) now show the error page and are logged as failure, instead of sending visitors to an invalid URL
- Redirects with status `200` to a page that doesn't exist or is a draft now show the error page and are logged as failure, instead of answering with an empty page
- Redirects to a content representation of the home page (e.g. `home.rss`) work now
- Query strings are passed on exactly as they are, both a target's own query string and the one forwarded by `preserveQuery`. They used to be re-encoded, which broke parameters like Google's `_gl` linker.
- Redirects to `mailto:` links work now; they used to end in an error
- Changing the path of a redirect to one that another redirect already uses is rejected instead of creating a duplicate
- Removing or resolving a failure now also works for paths with slashes
- Stats: custom timespans over several months show every month (one starting on January 31 skipped February), the chart only counts records inside the selected timespan, and the hours of a single day are no longer skipped or doubled when daylight saving time begins or ends
- Invalid dates for the timespan no longer break the Retour view, and an end date before the start date is corrected
- Unknown Retour tab URLs (e.g. `/panel/retour/foo`) show the Panel's not-found view
- One invalid pattern in the `ignore` option no longer stops all other patterns from working
- Requests that come in while redirects are being saved no longer see an empty config file, and saving keeps concurrent changes to other settings in `retour.yml`
- Very long paths and referrers (e.g. from bots) are cut off at 1024 characters in the log
- Filtering a table without matches shows "No results" instead of saying that there are no redirects or failures at all
- The filter button closes the search field with one click, also with a search term entered, and keeps the focus
- Filtering a table only searches the values shown in it (and the comment of a redirect); terms like "path" or "null" no longer match every row
- Deleting the last row on a table page, or switching to a timespan with fewer rows, no longer leaves an empty table without pagination
- Table columns can be sorted with the keyboard
- Only clickable table cells show a pointer cursor
- Translations have been completed and cleaned up; shared strings now come from Kirby's own translations

### Performance

- The redirects config isn't parsed on every request anymore (see the new `cache` option)
- Logging a failure or redirect hit takes one database statement instead of first looking up the table schema
- Faster stats: each hour, day or month is counted with an indexed range query
- Ignoring a path removes its existing failures in a single statement
- Clearing the log or purging old entries only rebuilds the database file when at least a fifth of it is free space, so the log isn't locked as long on large sites
- Smaller log database: the index for redirect hits no longer includes failures

### Housekeeping
- New `kirby retour:seed` command that fills the log with dummy data for testing.
- Translations are no longer managed on Transifex. To contribute, open a pull request against the JSON files in `src/i18n` on the `dev` branch.
- Redirects return a Kirby response instead of ending the PHP script
- The Panel code uses Kirby 6's view, dialog and drawer controllers and Vue 3; its logic lives in tested TypeScript helpers
- Upgraded tooling: kirbyup 4, ESLint 9, TypeScript, getkirby/php-cs and ParaTest
- Much larger test suite