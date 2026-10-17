# Changelog

## 6.0.0-beta.1

Retour 6 is rebuilt for Kirby 6: less noise from bots and scanners, a faster and leaner log, a Panel that's easier to navigate and more robust redirects. If you're on Kirby 4 or 5, keep using [Retour 5.7](https://github.com/distantnative/retour-for-kirby/releases/tag/5.7.0).

### Breaking changes

Updating is mostly automatic, but a few things work differently now. Check them first, especially if you want to keep log entries older than a year:

- Requires Kirby 6 and PHP 8.3 or newer
- `kirby retour:indices` command has been removed. Logs of earlier versions are updated automatically the first time you open Retour in the Panel.
- Log entries older than 12 months are deleted automatically (`deleteAfter` defaults to `12`; set it to `false` to keep them)
- The PHP API has changed significantly. If your code uses Retour's classes directly, check it against the new version.

### Features

On most sites, the majority of 404s don't come from people but from bots probing for WordPress logins or leaked `.env` files. Retour 6 lets you silence them right from the Panel and makes everything else easier to explore:

- **Ignore failures from the Panel** by their path/pattern. The "Ignored" button manages these paths and adds common scanner paths (`wp-*`, `xmlrpc.php`, `.env`, …) in one go.
- New **`ignoreIps` option** keeps IP addresses or ranges out of the log.
- Stats get a "Week" timespan, arrow keys to step through timespans and an updated design for the pie chart.
- Tables keep their page, sorting and filter in the URL and show 50 rows per page.
- New `cache` option: the parsed redirects config is cached until the file changes (on by default)
- Multi-language sites: **redirects to pages can target a specific language**
- New Czech, Danish, Italian, Japanese, Korean, Polish, Portuguese (Portugal) and Spanish (Latin America) translations
- More fitting icons

### Performance

Retour looks at every request your site answers, and its log grows with every bot that passes by. Both had to get a lot leaner:

- Panel, API and media requests no longer load the redirects at all
- With 1M log records, the log takes 88 MB instead of 133 MB, and removing, resolving or ignoring failures takes 0.1 ms instead of 47 ms thanks to a new index
- Tables are paginated, sorted and filtered on the server: at 1M log records, two years of failures load 4 KB instead of 30 MB of data and no longer exceed PHP's memory limit

### Fixes

Rebuilding Retour meant looking into every corner, and quite a few long-standing bugs turned up along the way:

- `retour.yml` that can't be parsed is no longer replaced with an empty list.
- Saving rejects invalid path patterns, status codes and duplicate paths.
- Redirects answer `HEAD` requests (without logging them) and pass query strings on unchanged.
- Missing targets show the error page and are logged as failure.
- Multi-language sites: redirects with priority keep visitors in the language of the path.
- Logging: one invalid `ignore` pattern no longer breaks the others and removing a failure keeps the redirect hits of its path
- Stats: custom timespans show every month, count only their own records and move by their own length.
- Timeline marks the current time by the server's clock and handles daylight saving time.
- Fixes for right-to-left languages and narrow screens.
- Tables: failures/redirects with slashes, `~`, `!`, `*` or `'` in the path can be managed.
- Filtering, sorting and pagination are more reliable.
- Numbers and dates follow the Panel's language.

### Housekeeping

Behind the scenes, Retour got easier to test, translate and contribute to:

- New `kirby retour:seed` command that fills the log with dummy data for testing
- Translations moved from Transifex to pull requests against `src/i18n` on the `dev` branch
- Upgraded tooling and a much larger test suite
