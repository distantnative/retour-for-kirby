<?php

namespace Kirby\Retour;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	protected $kirby;
	protected $tmp = __DIR__ . '/tmp';

	protected function app(array $options = []): App
	{
		return $this->kirby->clone([
			'options' => $options
		]);
	}

	public function setUp(): void
	{
		$this->kirby = new App([
			'roots' => [
				'index' => $this->tmp
			]
		]);
	}

	public function tearDown(): void
	{
		Dir::remove($this->tmp);
		Retour::$instance = null;
	}
}
