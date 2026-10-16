# Contributing

First of all: 🙏 thank you that you want to help! 👏

Here are some ways how you can contribute:

::: tip Report bugs
Please report any bug via [GitHub issues](https://github.com/distantnative/retour-for-kirby/issues).
:::

::: tip Translate Retour
Help translating the Retour UI to more languages. Check out the [translations docs](/i18n).
:::

## Develop locally

Clone the repository into `/site/plugins/retour` of a Kirby 6 test site and check out the `dev` branch, which is also where pull requests should go. Then install the dependencies:

```bash
composer install
npm install
```

To build the Panel code while you work on it:

```bash
npm run dev
```

Run the tests and linter before opening a pull request:

```bash
composer test
npm test
npm run lint
```

To fill the log with dummy data for testing the stats and failures, use this [Kirby CLI](https://github.com/getkirby/cli) command. It replaces all existing log records:

```bash
kirby retour:seed --records 50000 --months 25
```

The docs live in the `docs` folder and can be previewed with:

```bash
cd docs
npm install
npm run dev
```
