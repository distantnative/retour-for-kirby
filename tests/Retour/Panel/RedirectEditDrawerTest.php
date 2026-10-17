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
				['from' => 'homer/simpson', 'to' => 'marge', 'status' => 302, 'creator' => 'user://marge']
			]
		]);

		$app = $this->app([
			'distantnative.retour.config' => $this->tmp . '/retour.yml',
			'distantnative.retour.logs'   => false
		])->clone([
			'request' => [
				'body' => $body
			],
			'users' => [
				['id' => 'homer', 'email' => 'homer@simpson.com'],
				['id' => 'marge', 'email' => 'marge@simpson.com']
			]
		]);

		$app->impersonate('marge@simpson.com');
		Retour::instance($app);

		return RedirectEditDrawer::factory(Id::encode($id));
	}

	protected function redirects(): array
	{
		return Data::read($this->tmp . '/retour.yml')['redirects'];
	}

	public function testFactoryNotFound(): void
	{
		$this->expectException(NotFoundException::class);
		$this->drawer('missing', [
			'from' => 'missing'
		]);
	}

	public function testLoad(): void
	{
		$drawer = $this->drawer('homer/simpson')->load();

		$this->assertSame('homer/simpson', $drawer->title);
		$this->assertArrayHasKey('modifier', $drawer->fields);
		$this->assertSame('retour/redirects/' . Id::encode('homer/simpson') . '/delete', $drawer->options[0]['dialog']);
		$this->assertSame('marge', $drawer->value['to']);
		$this->assertSame('marge@simpson.com', (string)$drawer->value['creator'][0]['text']);
	}

	public function testLoadCreatorByEmail(): void
	{
		$drawer = $this->drawer('foo')->load();
		$this->assertSame('homer@simpson.com', (string)$drawer->value['creator'][0]['text']);
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
		$this->assertSame(['foo', 'homer/jay/simpson'], $redirects->keys());

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
		$this->assertSame('user://marge', $redirect['modifier']);
	}
}
