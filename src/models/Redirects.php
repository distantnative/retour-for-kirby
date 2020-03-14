<?php

namespace distantnative\Retour;

use Kirby\Cms\Response;
use Kirby\Data\Data;
use Kirby\Http\Header;
use Kirby\Http\Url;

class Redirects
{

    protected $retour = null;
    protected $redirects = null;

    public function __construct(Retour $retour)
    {
        $this->retour = $retour;
        $this->redirects = $retour->config()['redirects'] ?? [];
    }

    /**
     * Read all redirects and combine them with records data
     *
     * @param string $from
     * @param string $to
     *
     * @return array
     */
    public function get(string $from, string $to): array
    {
        // If logging is disabled, return without data for redirects
        if (option('distantnative.retour.logs') !== true) {
           return $this->redirects;
        }

        $logs = $this->retour->logs();
        return array_map(function ($redirect) use ($logs, $from, $to) {
            return $logs->redirect($redirect, $from, $to);
        }, $this->redirects);
    }

    /**
     * Get routes config for all redirects
     *
     * @return array
     */
    public static function toRoutes(): array
    {
        $data = $this->redirects;

        // Filter: no routes for disabled redirects
        $data = array_filter($data, function ($redirect) {
            return $redirect['status'] !== 'disabled';
        });

        // create route array for each redirect
        $logs = $this->retour->logs();
        $data = array_map(function ($redirect) use ($logs) {
            return [
                'pattern' => $redirect['from'],
                'action'  => function (...$parameters) use ($redirect, $logs) {
                    $code = (int) $redirect['status'];
                    $to   = $redirect['to'];

                    // Create log record
                    if (option('distantnative.retour.logs') === true) {
                        $logs->create([
                            'path' => Url::path(),
                            'redirect' => $redirect['from']
                        ]);
                    }

                    // Set the right response code
                    kirby()->response()->code($code);

                    // Map placeholders/parameters
                    foreach ($parameters as $i => $parameter) {
                        $to = str_replace('$' . ($i + 1), $parameter, $to);
                    }

                    // Replace alias for home
                    if ($to === '/') {
                        $to = 'home';
                    }

                    // Redirects
                    if ($code >= 300 && $code < 400) {
                        return Response::redirect($to ?? '/', $code);
                    }

                    // Return page for other codes
                    if ($page = page($to)) {
                        return page($page);
                    }

                    // Deliver HTTP status code and die
                    Header::status($code);
                    die();
                }
            ];
        }, $data);

        return $data;
    }

    /**
     * Update redirects in file
     *
     * @param array $data
     */
    public static function update(array $data = [])
    {
        $config = $this->retour->config();
        $config['redirects'] = $data;
        $this->retour->config($config);
    }
}
