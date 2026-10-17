# Redirects

You manage all your redirects in the `Routes` tab of Retour's Panel view.

## Add/edit a redirect

To create a new redirect, click on the `Add` button above the table. A drawer opens in which you can enter all the required details. By clicking on any table row or selecting the dropdown option, you can edit an existing route - or delete it:

![Edit a redirect](/edit.webp)

### Path

The URL segment after your site's domain, which you would like to catch and redirect.
It can be a static path, e.g. `team`, or you can use placeholders to define dynamic redirects, e.g. `blog/(:all)`.
Dynamic redirects use [Kirby's route patterns](https://getkirby.com/docs/guide/routing#patterns) as placeholders:

| Placeholder   | Matches                                          |
| ------------- | ------------------------------------------------ |
| `(:any)`      | A single path segment (no `/`)                   |
| `(:all)`      | Everything, including further segments (`a/b/c`) |
| `(:num)`      | Numbers, e.g. `42`                               |
| `(:alpha)`    | Letters only                                     |
| `(:alphanum)` | Letters and digits                               |

### Redirect to

Choose the target of your redirect. There are several options what to enter in this field:

| Select | Value                                                                                      |
| ------ | ------------------------------------------------------------------------------------------ |
| Url    | URL to an external website (e.g. `https://getkirby.com`)                                   |
| Page   | Choose one of your pages via the link picker                                               |
| Custom | Relative path inside your site (e.g. `blog/2018/a-nice-story`)                             |
| Custom | `error` to return the site's error page                                                    |
| Custom | Leave empty to let the browser request fail (for HTTP status codes not in the `3xx` range) |

If you are using placeholders in your path, you can use the matched value here as well via `$1`, `$2`, ...

```
project/(:any)/photos  =>  project/$1/gallery
blog/(:any)/(:all)     =>  notes/$1/entries/$2
```

### Status

The [HTTP status code](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status) the redirect will respond with.

- Only status codes in the `3xx` range will actually redirect the request to the new location (URL in browser actually changes). The query string of the original request, e.g. `?utm_source=newsletter`, is dropped unless you enable the [`preserveQuery` option](/options#query-strings).
- All other status code options either return the target page at the specified path (URL stays the same); or the browser request fails with the selected HTTP status code (empty `Redirect to` field). With status `200` and no page to show, Kirby's error page is shown instead, as an empty page would look like a successful request.
- If you select the `disabled` option, the redirect is ignored/inactive.

### Has priority

If the priority flag is activated, the redirect route will overrule any existing pages as well. With this option deactivated, only non-existing paths can be redirected.

### Comment

A simple text field to leave notes for yourself and others, e.g. why this redirect is necessary.

### Created by / Last modified by

Retour automatically adds the current user as context information, e.g. when working in a team.

## Sort and filter

Click a table header to sort the redirects by that column, click it again to reverse the order and a third time to get back to their order of precedence. The filter button above the table, next to `Add`, opens a search field that finds redirects by their path, target, status code or comment.

The page, sorting and filter are part of the URL, so you can bookmark them or share a link with others.
