# Changelog

## 6.0.0-beta.1

Retour 6 is the first version for Kirby 6. If you're on Kirby 4 or 5, keep using Retour 5.7.

### Breaking changes

- Requires Kirby 6 and PHP 8.3 or newer
- The `kirby retour:indices` command has been removed. Logs of earlier versions are updated automatically the first time you open Retour in the Panel.
- Log entries older than 12 months are deleted automatically (`deleteAfter` defaults to `12`; set it to `false` to keep them)
- The PHP API has changed significantly. If your code uses Retour's classes directly, check it against the new version.

### Features

- Ignore failures from the Panel: the new "Ignore" option adds a failure's path, or a pattern based on it, to the ignored paths and removes its failures from the log. The new "Ignored" button manages these paths, lists the ones from the `ignore` option and adds common scanner paths (`wp-*`, `xmlrpc.php`, `.env`, `.git/…`) in one go.
- New `ignoreIps` option: requests from these IP addresses or ranges (e.g. `192.0.2.0/24`), such as your host's security scanner, aren't logged. Redirects still apply to them.
- New "Week" timespan, starting on the same weekday as the calendar in the Panel's date fields
- New `cache` option: the parsed redirects config is cached until the file changes. On by default; set it to `false` or pass Kirby cache options, e.g. `['type' => 'apcu']`.
- New translations: Czech, Danish, Italian, Japanese, Korean, Polish, Portuguese (Portugal) and Spanish (Latin America). All others have been completed.
- New and more fitting icons throughout the Panel
- Clicking the empty redirects table opens the drawer to add a redirect
- The buttons above the tables can be reordered or hidden with Kirby's `panel.viewButtons` option

### Fixes

#### Redirects

- A `retour.yml` that can't be parsed is no longer replaced with an empty list on the next save. Redirects are off until it's fixed, and the Panel shows the error.
- Saving rejects invalid path patterns, invalid status codes and paths another redirect already uses, instead of breaking routing or creating duplicates
- While redirects are saved, other requests no longer see an empty config file, and changes made by others in the meantime (e.g. to other redirects) are kept
- Redirects also apply to ignored paths and to HEAD requests (which aren't logged)
- Redirects no longer show or target draft pages, or pages inside drafts
- Targets that aren't pages (e.g. `downloads/file.pdf`) are resolved against the site URL instead of the requested path
- Missing targets (a deleted page, or a `200` redirect to a missing page or draft) show the error page and are logged as failure
- Redirects to `mailto:` links and to content representations of the home page (e.g. `home.rss`) work
- Query strings are passed on exactly as they are instead of being re-encoded, which broke parameters like Google's `_gl`
- The creator and last editor of a redirect are stored by their user UUID, so they're still shown after an email address changes

#### Logging

- One invalid pattern in the `ignore` option no longer stops the others from working
- Very long paths and referrers are cut off at 1024 characters
- Removing or resolving a failure works for paths with slashes

#### Stats

- Custom timespans over several months show every month, and the chart only counts records inside the timespan
- Previous and next on a custom timespan move by its own length, and clicking a month in the timeline zooms into the whole month
- Invalid timespan dates no longer break the view, and an end date before the start date is corrected
- The timeline handles daylight saving time, keeps highlighting the current hour, day or month past midnight and is no longer mirrored in right-to-left languages
- The timeline labels are dimmed again and sit under their columns; the y-axis shows exact ticks and whole numbers for small counts
- Numbers in the stats and tables are formatted in the Panel's language

#### Panel

- Filtering a table only searches the values shown (and a redirect's comment) and shows "No results" without matches. The filter button closes the field in one click and keeps the focus.
- Deleting the last rows of a page or switching timespans no longer leaves an empty table without pagination
- Table cells line up with their headers, dimmed dashes and icons are readable in light and dark mode, and only clickable cells show a pointer cursor
- Without logging, the redirects table no longer shows an empty hits column
- The tooltip of a hit count shows the date of the last hit in a readable format
- Fixed the links of some logged paths and referrers in the tables
- Table columns can be sorted with the keyboard; screen readers skip the timeline chart and announce the priority icon
- Unknown Retour tab URLs show the Panel's not-found view

### Performance

- The redirects config isn't parsed on every request anymore (see the new `cache` option)
- Panel, API and media requests no longer load the redirects (1.3 ms less per request with 1,000 redirects)
- Logging a request and ignoring a path each take a single database statement
- Stats count each hour, day or month with an indexed range query
- Clearing or purging the log only rebuilds the database file when at least a fifth of it is free, so large logs are locked for less time
- Smaller log database: the index for redirect hits no longer includes failures
- Filtering large tables in the Panel is much faster and lighter (40 ms instead of 530 ms at 100,000 failures)

### Housekeeping

- New `kirby retour:seed` command that fills the log with dummy data for testing
- Translations are no longer managed on Transifex. To contribute, open a pull request against the JSON files in `src/i18n` on the `dev` branch.
- Redirects return a Kirby response instead of ending the PHP script
- The Panel code uses Kirby 6's view, dialog and drawer controllers and Vue 3
- Upgraded tooling (kirbyup 4, ESLint 10, TypeScript, getkirby/php-cs, ParaTest) and a much larger test suite
