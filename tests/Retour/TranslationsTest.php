<?php

namespace Kirby\Retour;

use Kirby\Data\Json;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversNothing]
class TranslationsTest extends TestCase
{
	protected const string ROOT = __DIR__ . '/../../src/i18n';

	protected static function placeholders(string $string): array
	{
		preg_match_all('/\{\w+\}/', $string, $matches);
		sort($matches[0]);
		return $matches[0];
	}

	protected static function read(string $locale): array
	{
		return Json::read(static::ROOT . '/' . $locale . '.json');
	}

	public static function locales(): array
	{
		$locales = [];

		foreach (Dir::files(static::ROOT) as $file) {
			$locale = basename($file, '.json');

			if ($locale !== 'en') {
				$locales[$locale] = [$locale];
			}
		}

		return $locales;
	}

	#[DataProvider('locales')]
	public function testKeys(string $locale): void
	{
		$expected = array_keys(static::read('en'));
		$actual   = array_keys(static::read($locale));

		$this->assertSame([], array_values(array_diff($expected, $actual)), 'Missing keys in ' . $locale);
		$this->assertSame([], array_values(array_diff($actual, $expected)), 'Unknown keys in ' . $locale);
	}

	#[DataProvider('locales')]
	public function testPlaceholders(string $locale): void
	{
		$en = static::read('en');

		foreach (static::read($locale) as $key => $value) {
			$this->assertSame(
				static::placeholders($en[$key] ?? ''),
				static::placeholders($value),
				'Placeholders differ for ' . $key . ' in ' . $locale
			);
		}
	}
}
