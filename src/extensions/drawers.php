<?php

use Kirby\Retour\Panel\FailureResolveDrawer;
use Kirby\Retour\Panel\RedirectCreateDrawer;
use Kirby\Retour\Panel\RedirectEditDrawer;

/**
 * Fiber drawers for all Panel tabs
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */

return [
	'retour.redirect.create' => [
		'pattern' => 'retour/redirects/create',
		'load'    => fn () => (new RedirectCreateDrawer())->load(),
		'submit'  => fn () => (new RedirectCreateDrawer())->submit(),
	],

	'retour.redirect.edit' => [
		'pattern' => 'retour/redirects/(:any)/edit',
		'load'    => fn (string $id) => (new RedirectEditDrawer($id))->load(),
		'submit'  => fn (string $id) => (new RedirectEditDrawer($id))->submit(),
	],

	'retour.failure.resolve' => [
		'pattern' => 'retour/failures/(:any)/resolve',
		'load'    => fn (string $path) => (new FailureResolveDrawer($path))->load(),
		'submit'  => fn (string $path) => (new FailureResolveDrawer($path))->submit()
	]
];
