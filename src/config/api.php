<?php

namespace distantnative\Retour;

use Kirby\Form\Field;

/**
 * Sets up API routes for Panel area
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 *
 * @todo Remove once page picker is available via Fiber
 */

return [
    'routes' => function ($kirby) {
        return [
            [
                'pattern' => 'retour/pagepicker',
                'method'  => 'GET',
                'action'  => function (): array {
                    $field = new Field('pages', [
                        'model' => kirby()->site()
                    ]);

                    /**
                     * @psalm-suppress InvalidScope
                     */
                    return $field->pagepicker([
                        'image'    => $field->image(),
                        'info'     => $field->info(),
                        'limit'    => $field->limit(),
                        'page'     => $this->requestQuery('page'),
                        'parent'   => $this->requestQuery('parent'),
                        'query'    => $field->query(),
                        'search'   => $this->requestQuery('search'),
                        'subpages' => $field->subpages(),
                        'text'     => $field->text()
                    ]);
                }
            ]
        ];
    }
];
