<?php

namespace distantnative\Retour;

use Kirby\Toolkit\F;

final class ConfigTest extends TestCase
{
    public function testFileNotExists(): void
    {
        $file = __DIR__ . '/fixtures/nowhere.yml';
        $this->assertFalse(F::exists($file));

        $config = new Config($file);
        $this->assertTrue(F::exists($file));
        $this->assertSame([], $config->data());

        F::remove($file);
    }

    public function testData(): void
    {
        $file = __DIR__ . '/fixtures/config.yml';
        $this->assertTrue(F::exists($file));

        $config = new Config($file);
        $this->assertSame([
            'routes' => [
                ['foo' => 'bar'],
                ['homer' => 'simpson']
            ],
            'schema' => '3.0.1'
        ], $config->data());

        $this->assertSame('3.0.1', $config->data('schema'));
        $this->assertSame('fallback', $config->data('empty', 'fallback'));
        $this->assertSame(null, $config->data('empty'));

    }

    public function testSet(): void
    {
        $file   = __DIR__ . '/fixtures/temp.yml';
        $config = new Config($file);
        $this->assertSame([], $config->data());

        $config->set('routes', ['foo' => 'bar']);
        $this->assertSame(['routes' => ['foo' => 'bar']], $config->data());
        $this->assertSame('routes:
  foo: bar
', F::read($file));

        $config->set('schema', '3.0.0');
        $this->assertSame([
            'routes' => ['foo' => 'bar'],
            'schema' => '3.0.0'
        ], $config->data());
        $this->assertSame('routes:
  foo: bar
schema: 3.0.0
', F::read($file));

        F::remove($file);
    }

    public function testOverwrite(): void
    {
        $file   = __DIR__ . '/fixtures/temp.yml';
        $config = new Config($file);
        $this->assertSame([], $config->data());

        $config->set('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $config->data());

        $config->overwrite(['homer' => 'simpson']);
        $this->assertSame(['homer' => 'simpson'], $config->data());
        $this->assertSame('homer: simpson
', F::read($file));

        F::remove($file);
    }
}
