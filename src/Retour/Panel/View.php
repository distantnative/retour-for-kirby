<?php

namespace Kirby\Retour\Panel;

use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Panel;
use Kirby\Panel\Ui\View as ViewComponent;
use Kirby\Retour\Retour;
use Kirby\Retour\Timespan;

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

	/**
	 * Returns the view for the tab
	 */
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
			title: $this->i18n('view.retour'),
			breadcrumb: [
				[
					'label' => $this->i18n('retour.' . $this->tab),
					'link'  => 'retour/' . $this->tab,
				]
			]
		);
	}

	/**
	 * Returns all props for the Panel view
	 */
	public function props(): array
	{
		$retour   = Retour::instance();
		$timespan = Timespan::props();
		['from' => $from, 'to' => $to, 'unit' => $unit] = $timespan;

		// props for minimal Panel view
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

		// if log feature is supported...
		if ($retour->hasLog() === true) {
			// run garbage collection with a chance of 10%;
			if (mt_rand(1, 10000) <= 0.1 * 10000) {
				// purge log entries that should be automatically deleted
				$retour->log()->purge();
			}

			// add additional tabs
			$props['tabs'][] = [
				'name'  => 'failures',
				'label' => $this->i18n('retour.failures'),
				'icon'  => 'url-broken',
				'link'  => 'retour/failures'
			];

			// get statistics data for current timeframe
			$props['stats'] = $retour->log()->stats($unit, $from, $to);
		}

		// add tab-specific data, e.g. for table rows
		$props['data'] = match ($this->tab) {
			'redirects' => $retour->redirects()->toData($from, $to),
			'failures'  => $retour->log()->fails($from, $to) ?? [],
			default     => []
		};

		return $props;
	}
}
