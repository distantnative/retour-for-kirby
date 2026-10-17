<?php

namespace Kirby\Retour\Panel;

use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Panel;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\View as ViewComponent;
use Kirby\Retour\Retour;
use Kirby\Retour\Timespan;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Str;

/**
 * Handles Panel view requests for the Retour tabs
 */
class View extends ViewController
{
	public function __construct(
		protected string $tab
	) {
		parent::__construct();
	}

	public function buttons(): ViewButtons
	{
		$defaults = match ($this->tab) {
			'redirects' => ['create'],
			'failures'  => ['ignores', 'flush']
		};

		return ViewButtons::view('retour.' . $this->tab)->defaults(...$defaults);
	}

	public function load(): ViewComponent
	{
		if ($error = Retour::instance()->config()->error()) {
			throw $error;
		}

		// failures are only available with logging enabled
		if (
			$this->tab === 'failures' &&
			Retour::instance()->hasLog() === false
		) {
			Panel::go('retour/redirects');
		}

		return new ViewComponent(
			...$this->props(),
			component: 'k-retour-' . $this->tab . '-view',
			buttons: $this->buttons(),
			title: $this->i18n('view.retour'),
			breadcrumb: [
				[
					'label' => $this->i18n('retour.' . $this->tab),
					'link'  => 'retour/' . $this->tab,
				]
			]
		);
	}

	public function props(): array
	{
		$retour = Retour::instance();
		$log    = $retour->log();

		// update logs created by earlier versions
		$log?->migrate();

		$timespan = Timespan::props();
		['from' => $from, 'to' => $to, 'unit' => $unit] = $timespan;

		$props = [
			'timespan' => $timespan,
			'tab'      => $this->tab,
			'tabs'     => [
				[
					'name'  => 'redirects',
					'label' => $this->i18n('retour.redirects'),
					'icon'  => 'redirect',
					'link'  => 'retour/redirects'
				]
			]
		];

		if ($log !== null) {
			// purge outdated entries on every 10th view on average
			if (random_int(1, 10) === 1) {
				$log->purge();
			}

			$props['tabs'][] = [
				'name'  => 'failures',
				'label' => $this->i18n('retour.failures'),
				'icon'  => 'url-broken',
				'link'  => 'retour/failures'
			];

			$props['stats'] = $log->stats($unit, $from, $to);
		}

		$sortBy     = $this->query('sortBy');
		$searchterm = $this->query('searchterm');
		$page       = (int)$this->request->get('page', 1);

		$rows = match ($this->tab) {
			'redirects' => $this->redirects($from, $to, $sortBy, $searchterm, $page),
			'failures'  => $log->fails($from, $to, $sortBy, $searchterm, $page)
		};

		return [
			...$props,
			'data'       => $rows->values(),
			'pagination' => $rows->pagination()->toArray(),
			'searchterm' => $searchterm,
			'sortBy'     => $sortBy
		];
	}

	/**
	 * Returns a query parameter without surrounding
	 * whitespace, `null` if it's missing or empty
	 */
	protected function query(string $key): string|null
	{
		$value = trim($this->request->get($key, ''));
		return $value !== '' ? $value : null;
	}

	/**
	 * Returns a page of the redirects with their hits;
	 * unsorted, they keep their order of precedence
	 */
	protected function redirects(
		string $from,
		string $to,
		string|null $sortBy,
		string|null $searchterm,
		int $page
	): Collection {
		$redirects = new Collection(Retour::instance()->redirects()->toData($from, $to));

		if ($searchterm !== null) {
			$redirects = $redirects->filter(
				fn (array $redirect): bool => Str::contains(
					implode("\n", [$redirect['from'], $redirect['to'], $redirect['status'], $redirect['comment']]),
					$searchterm,
					true
				)
			);
		}

		if ($sortBy !== null) {
			$redirects = $redirects->sort(...Str::split($sortBy, ' '));
		}

		return $redirects->paginate(20, $page);
	}
}
