<?php

return [
    'view.retour' => 'Redirects',

    'retour.table.filter'        => 'Filter...',
    'retour.table.perPage.all'   => 'All',
    'retour.table.perPage.after' => 'per page',

    'retour.hits'      => 'Hits',
    'retour.hits.last' => 'Last',

    'retour.routes'       => 'Routes',
    'retour.routes.add'   => 'Add redirect',
    'retour.routes.empty' => 'No redirect routes configured',

    'retour.routes.fields.from' => 'Path',
    'retour.routes.fields.from.help' => 'Add <a href="{docs}" target="_blank">placeholders</a> for dynamic redirects.',
    'retour.routes.fields.to' => 'Redirect to',
    'retour.routes.fields.to.help' => 'Relative path or absolute URL. Leave empty to let the request die with status code.',
    'retour.routes.fields.status' => 'Status',
    'retour.routes.fields.status.help' => 'Learn about <a href="{docs}" target="_blank">HTTP status codes</a> and when to use which one.',
    'retour.routes.fields.priority' => 'Has priority?',
    'retour.routes.fields.priority.abbr' => 'Prio',
    'retour.routes.fields.priority.help' => 'If you activate this option, the redirect will be enforced even if an actual page exists.',
    'retour.routes.fields.comment' => 'Comment',
    'retour.routes.fields.comment.help' => 'If you need to add some info for yourself or others…',

    'retour.failures'          => 'Failures',
    'retour.failures.empty'    => 'No failures yet',
    'retour.failures.path'     => 'Path',
    'retour.failures.referrer' => 'Referrer',
    'retour.failures.resolve'  => 'Add as redirect',

    'retour.failures.clear'          => 'Clear log',
    'retour.failures.clear.confirm'  => 'Do you really want to permanently delete the log?',

    'retour.stats.redirected' => 'redirected',
    'retour.stats.resolved'   => 'resolved',
    'retour.stats.failed'     => 'failed',
    'retour.stats.mode.all'   => 'All',
    'retour.stats.mode.year'  => 'Year',
    'retour.stats.mode.month' => 'Month',
    'retour.stats.mode.week'  => 'Week',
    'retour.stats.mode.day'   => 'Day',

    'retour.system'                    => 'System',
    'retour.system.version'            => 'Installed version',
    'retour.system.release'            => 'Current release',
    'retour.system.support'            => 'Support development',
    'retour.system.support.donate'     => 'Donate',
    'retour.system.failures'           => 'Logged failures',
    'retour.system.redirects'          => 'Successfully redirected',
    'retour.system.deleteAfter'        => 'Delete logs after',
    'retour.system.deleteAfter.months' => '{count} months',
    'retour.system.docs'               => 'Learn more about options <a href="{docs}">in the docs</a>.'
];
