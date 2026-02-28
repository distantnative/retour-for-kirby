<?php

namespace Kirby\Retour\Panel;

use Kirby\Retour\Retour;
use Kirby\Retour\Timespan;
use Kirby\Toolkit\I18n;

/**
 * Handles (Fiber) Panel view requests
 */
class View
{
	/**
	 * Returns all props for the Panel view
	 */
	public static function props(string $tab): array
	{
		$retour   = Retour::instance();
		$timespan = Timespan::props();
		['from' => $from, 'to' => $to, 'unit' => $unit] = $timespan;

		// get all data for redirects
		// and fallback for failures
		$redirects = $retour->redirects()->toData($from, $to);
		$failures  = [];

		// props for minimal Panel view
		$props = [
			'timespan' => $timespan,
			'tab'      => $tab,
			'tabs'     => [
				[
					'name'  => 'redirects',
					'label' => I18n::translate('retour.redirects'),
					'badge' => count($redirects),
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

			// get all data for 404 failures
			/** @var array */
			$failures = $retour->log()->fails($from, $to);

			// add additional tabs
			$props['tabs'][] = [
				'name'  => 'failures',
				'label' => I18n::translate('retour.failures'),
				'badge' => count($failures),
				'link'  => 'retour/failures'
			];
			$props['tabs'][] = [
				'name'  => 'system',
				'label' => I18n::translate('retour.system'),
				'badge' => false,
				'link'  => 'retour/system'
			];

			// get statistics data for current timeframe
			$props['stats'] = $retour->log()->stats($unit, $from, $to);
		}

		// add tab=specific data, e.g. for table rows
		$props['data'] = match ($tab) {
			'redirects' => $redirects,
			'failures'  => $failures,
			'system'    => [
				'redirects' => array_reduce(
					$redirects,
					fn ($c, $i) => $c + $i['hits'],
					0
				),
				'failures' => array_reduce(
					$failures,
					fn ($c, $i) => $c + $i['hits'],
					0
				),
				'deleteAfter' => $retour->option('deleteAfter', '-')
			],
			default => []
		};

		return $props;
	}

	/**
	 * Returns the Fiber view definition for a tab
	 */
	public static function tab(string $tab): array
	{
		return [
			'component'  => 'k-retour-' . $tab . '-view',
			'title'      => I18n::translate('view.retour'),
			'breadcrumb' => [
				[
					'label' => I18n::translate('retour.' . $tab),
					'link'  => 'retour/' . $tab,
				]
			],
			'props' => static::props($tab)
		];
	}
}
