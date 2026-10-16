<?php

namespace Kirby\Retour\Panel;

use Kirby\Data\Data;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\NotFoundException;
use Kirby\Retour\Retour;
use Kirby\Retour\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RedirectEditDrawer::class)]
class RedirectEditDrawerTest extends TestCase
{
	protected function drawer(string $id, array $body = []): RedirectEditDrawer
	{
		Data::write($this->tmp . '/retour.yml', [
			'redirects' => [
				['from' => 'foo', 'to' => 'bar', 'status' => 301, 'creator' => 'homer@simpson.com'],
				['from' => 'homer/simpson', 'to' => 'marge', 'status' => 302]
			]
		]);

		$app = $this->app([
			'distantnative.retour.config' => $this->tmp . '/retour.yml',
			'distantnative.retour.logs'   => false
		])->clone([
			'request' => [
				'body' => $body
			]
		]);

		Retour::instance($app);

		return new RedirectEditDrawer(urlencode(str_replace('/', "\x1D", $id)));
	}

	protected function redirects(): array
	{
		return Data::read($this->tmp . '/retour.yml')['redirects'];
	}

	public function testSubmit(): void
	{
		$drawer = $this->drawer('homer/simpson', [
			'from'   => 'homer/jay/simpson',
			'to'     => 'marge',
			'status' => '307'
		]);

		$this->assertTrue($drawer->submit());

		$redirects = Retour::instance()->redirects();
		$this->assertSame(['foo', "homer\x1Djay\x1Dsimpson"], $redirects->keys());

		$redirects = $this->redirects();
		$this->assertCount(2, $redirects);
		$this->assertSame('homer/jay/simpson', $redirects[1]['from']);
		$this->assertSame(307, $redirects[1]['status']);
	}

	public function testSubmitDuplicate(): void
	{
		$drawer = $this->drawer('foo', [
			'from' => 'homer/simpson',
			'to'   => 'bar'
		]);

		try {
			$drawer->submit();
			$this->fail('Saving a duplicate path must fail');
		} catch (DuplicateException) {
			$this->assertSame(['foo', 'homer/simpson'], array_column($this->redirects(), 'from'));
		}
	}

	public function testSubmitKeepsCreator(): void
	{
		$drawer = $this->drawer('foo', [
			'from' => 'foo',
			'to'   => 'baz'
		]);

		$drawer->submit();

		$redirect = $this->redirects()[0];
		$this->assertSame('baz', $redirect['to']);
		$this->assertSame('homer@simpson.com', $redirect['creator']);
	}

	public function testSubmitNotFound(): void
	{
		$drawer = $this->drawer('missing', [
			'from' => 'missing'
		]);

		$this->expectException(NotFoundException::class);
		$drawer->submit();
	}
}
