<?php

namespace Kirby\Retour\Commands;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Retour\Log;
use Kirby\Retour\Redirect;
use Kirby\Retour\Retour;
use Kirby\Toolkit\Str;
use Random\Randomizer;

/**
 * Replaces the log with dummy data for testing
 */
class Seed extends Command
{
	public static function args(): array
	{
		return [
			'records' => [
				'description'  => 'Number of log records to generate',
				'longPrefix'   => 'records',
				'castTo'       => 'int',
				'defaultValue' => 50000
			],
			'months' => [
				'description'  => 'Number of months the records are spread across',
				'longPrefix'   => 'months',
				'castTo'       => 'int',
				'defaultValue' => 25
			],
			'force' => [
				'description' => 'Skip the confirmation before deleting the existing log',
				'prefix'      => 'f',
				'longPrefix'  => 'force',
				'noValue'     => true
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$retour = Retour::instance($cli->kirby());
		$log    = $retour->log();

		if ($log === null) {
			$cli->out('Logging is disabled, no database to seed.');
			return;
		}

		if ($cli->arg('force') !== true) {
			$cli->confirmToContinue('All existing log records will be deleted. Continue?');
		}

		$random    = new Randomizer();
		$total     = $cli->arg('records');
		$dates     = static::dates($random, $total, $cli->arg('months'));
		$failures  = static::failures();
		$redirects = static::redirects($retour);
		$referrers = static::referrers();

		// failure paths that will be marked as resolved
		$resolved = array_flip(array_map(
			fn (int $key): string => $failures[$key],
			$random->pickArrayKeys($failures, intdiv(count($failures), 5))
		));

		$log->flush('all');

		$db     = $log->database()->connection();
		$insert = $db->prepare('INSERT INTO records (date, path, referrer, redirect, wasResolved) VALUES (?, ?, ?, ?, ?)');
		$db->beginTransaction();

		$progress = $cli->progress($total);

		foreach ($dates as $i => $time) {
			// redirect hits only store their redirect
			if ($redirects !== [] && $random->nextFloat() < 0.4) {
				$redirect = static::pick($random, $redirects);
				$path     = null;
				$referrer = null;
			} else {
				$redirect = null;
				$path     = static::pick($random, $failures);
				$referrer = $random->nextFloat() < 0.3 ? static::pick($random, $referrers) : null;
			}

			$insert->execute([
				Log::timestamp(date('Y-m-d H:i:s', $time)),
				$path,
				$referrer,
				$redirect,
				$redirect === null && isset($resolved[$path]) ? 1 : null
			]);

			if ($i % 1000 === 0) {
				$progress->current($i);
			}
		}

		$db->commit();
		$progress->current($total);

		if ($redirects === []) {
			$cli->out('No active redirects configured, only failures were generated.');
		}

		$cli->success('Generated ' . number_format($total) . ' log records.');
	}

	/**
	 * Returns sorted timestamps; recent ones are
	 * more likely, simulating growing traffic
	 */
	protected static function dates(
		Randomizer $random,
		int $total,
		int $months
	): array {
		$start = strtotime('-' . $months . ' months');
		$end   = time();
		$dates = [];

		for ($i = 0; $i < $total; $i++) {
			$dates[] = $start + (int)(($end - $start) * sqrt($random->nextFloat()));
		}

		sort($dates);

		return $dates;
	}

	public static function description(): string|null
	{
		return 'Replace the Retour log with dummy data for testing';
	}

	/**
	 * Returns paths that don't exist and will be logged as failures
	 */
	protected static function failures(): array
	{
		$failures = [
			'wp-login.php',
			'wp-admin',
			'.env',
			'xmlrpc.php',
			'apple-touch-icon.png',
			'blog/hello-world',
			'about-us',
			'contact.html',
			'shop/cart',
			'downloads/brochure-2019.pdf',
			'team/former-colleague',
			'events/summer-party',
		];

		// long tail of paths that only get hit rarely
		for ($i = 0; $i < 1000; $i++) {
			$failures[] = 'blog/' . Str::random(10, 'alphaLower');
		}

		return $failures;
	}

	/**
	 * Picks an item, favoring the first ones so that
	 * some paths get a lot more hits than others
	 */
	protected static function pick(Randomizer $random, array $items): mixed
	{
		return $items[(int)(count($items) * $random->nextFloat() ** 3)];
	}

	/**
	 * Returns the patterns of all active redirects,
	 * as only those can get hits
	 */
	protected static function redirects(Retour $retour): array
	{
		$redirects = $retour->redirects()->filter(
			fn (Redirect $redirect): bool => $redirect->isActive()
		);

		return $redirects->toArray(
			fn (Redirect $redirect): string => $redirect->from()
		);
	}

	protected static function referrers(): array
	{
		return [
			'https://www.google.com/',
			'https://duckduckgo.com/',
			'https://www.bing.com/',
			'https://t.co/',
			'https://news.ycombinator.com/',
			'https://example.com/links',
		];
	}
}
