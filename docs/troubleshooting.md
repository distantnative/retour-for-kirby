# Troubleshooting

This plugin is provided "as is" with no guarantees. Use it at your own risk and always test it yourself before using it in a production environment. If you encounter any problem, please [create an issue](https://github.com/distantnative/retour-for-kirby/issues).

## PDOException: could not find driver

If you encounter this error message, it most likely means that SQLite is not installed, as mentioned in [this issue](https://github.com/distantnative/retour-for-kirby/issues/160) (with example how to fix). If you can't install it on your server, disable the [`logs` option](/options#config) to use Retour without the 404 tracking.

## The `Failures` tab is missing

The tab and all stats are only shown when the [`logs` option](/options#config) is enabled. Check that it isn't set to `false` in your config.

## A redirect doesn't work

- If a page exists at the path, the redirect only applies with the [`Has priority`](/redirects#has-priority) option enabled.
- Make sure the redirect doesn't have the `disabled` status.
- Browsers cache permanent redirects (`301`, `308`). Test changes in a private window.

## The Panel view is slow

With a large log, loading the stats can take a while. After updating from an earlier version, the first visit also updates the log database once. To keep the log small in the future, [clear it](/failures#clearing-logs) from time to time or set the [`deleteAfter` option](/options#config).
