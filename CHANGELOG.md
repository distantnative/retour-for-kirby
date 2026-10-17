# Changelog

## 6.0.0-beta.1

Retour 6 is rebuilt for Kirby 6: less noise from bots and scanners, a faster and leaner log, a Panel that's easier to navigate and more robust redirects. If you're on Kirby 4 or 5, keep using Retour 5.7.

### Breaking changes

- Requires Kirby 6 and PHP 8.3 or newer
- The `kirby retour:indices` command has been removed. Logs of earlier versions are updated automatically the first time you open Retour in the Panel.
- Log entries older than 12 months are deleted automatically, even if nobody opens Retour in the Panel (`deleteAfter` defaults to `12`; set it to `false` to keep them)
- The PHP API has changed significantly. If your code uses Retour's classes directly, check it against the new version.

### Features

- Ignore failures from the Panel by their path or a pattern, which also removes them from the log. The "Ignored" button manages these paths and adds common scanner paths (`wp-*`, `xmlrpc.php`, `.env`, …) in one go. The new `ignoreIps` option keeps IP addresses or ranges out of the log. Redirects still apply to both.
- The stats get a "Week" timespan, links in the timeline and the timespan navigation, arrow keys to step through timespans and a half ring chart with the total number of requests
- The tables keep their page, sorting and filter in the URL, show 50 rows per page, and their buttons can be reordered or hidden with Kirby's `panel.viewButtons` option
- New `cache` option: the parsed redirects config is cached until the file changes (on by default)
- New Czech, Danish, Italian, Japanese, Korean, Polish, Portuguese (Portugal) and Spanish (Latin America) translations, all others completed, and more fitting icons

### Fixes

- A `retour.yml` that can't be parsed is no longer replaced with an empty list on the next save (redirects are off and the Panel shows the error until it's fixed). Saving also rejects invalid path patterns, status codes and duplicate paths, and no longer interferes with requests or changes made at the same time.
- Redirects answer HEAD requests (without logging them), no longer show or target drafts, resolve targets that aren't pages against the site URL and pass query strings on unchanged. Missing targets show the error page and are logged as failure.
- Logging: one invalid `ignore` pattern no longer breaks the others, very long paths and referrers are cut off at 1024 characters, and removing a failure keeps the redirect hits of its path
- Stats: custom timespans show every month, count only their own records and move by their own length, and the timeline marks the current time by the server's clock and handles daylight saving time, right-to-left languages and narrow screens
- Tables: failures and redirects with slashes, `~`, `!`, `*` or `'` in their path can be opened, edited, resolved and removed. Filtering, sorting and pagination are more reliable, and numbers and dates follow the Panel's language. Fixed the links of some logged paths and referrers.

### Performance

- Panel, API and media requests no longer load the redirects at all
- With 1M log records, the log takes 88 MB instead of 133 MB, and removing, resolving or ignoring failures takes 0.1 ms instead of 47 ms thanks to a new index
- The tables are paginated, sorted and filtered on the server: at 1M log records, two years of failures load 4 KB instead of 30 MB of data and no longer exceed PHP's memory limit

### Housekeeping

- New `kirby retour:seed` command that fills the log with dummy data for testing
- Translations moved from Transifex to pull requests against `src/i18n` on the `dev` branch
- Upgraded tooling and a much larger test suite
