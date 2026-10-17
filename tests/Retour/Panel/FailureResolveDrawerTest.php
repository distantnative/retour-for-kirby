<?php

namespace Kirby\Retour\Panel;

use Kirby\Data\Data;
use Kirby\Retour\Retour;
use Kirby\Retour\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FailureResolveDrawer::class)]
#[CoversClass(RedirectCreateDrawer::class)]
class FailureResolveDrawerTest extends TestCase
{
	protected function drawer(string $path, array $body = []): FailureResolveDrawer
	{
		$app = $this->app([
			'distantnative.retour.config'   => $this->tmp . '/retour.yml',
			'distantnative.retour.database' => $this->tmp . '/log.sqlite'
		])->clone([
			'request' => [
				'body' => $body
			]
		]);

		$log = Retour::instance($app)->log();
		$log->add(['path' => 'foo/bar']);
		$log->add(['path' => 'baz']);

		return FailureResolveDrawer::factory(urlencode(str_replace('/', "\x1D", $path)));
	}

	public function testLoad(): void
	{
		$drawer = $this->drawer('foo/bar')->load();

		$this->assertSame('foo/bar', $drawer->value['from']);
		$this->assertArrayNotHasKey('modifier', $drawer->fields);
	}

	public function testSubmit(): void
	{
		$drawer = $this->drawer('foo/bar', [
			'from'   => 'foo/bar',
			'to'     => 'baz',
			'status' => '301'
		]);

		$this->assertSame(['redirect' => 'retour/redirects'], $drawer->submit());

		$redirects = Data::read($this->tmp . '/retour.yml')['redirects'];
		$this->assertSame('foo/bar', $redirects[0]['from']);
		$this->assertSame(301, $redirects[0]['status']);

		$failures = Retour::instance()->log()->fails('2000-01-01', '2099-12-31')->values();
		$this->assertSame(['baz'], array_column($failures, 'path'));
	}
}
