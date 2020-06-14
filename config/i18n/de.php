<?php

return [
    'view.retour' => 'Weiterleitungen',

    'retour.table.filter'        => 'Filtern...',
    'retour.table.perPage.all'   => 'Alle',
    'retour.table.perPage.after' => 'pro Seite',

    'retour.hits'      => 'Treffer',
    'retour.hits.last' => 'Zuletzt',

    'retour.routes'       => 'Routen',
    'retour.routes.add'   => 'Weiterleitung hinzufügen',
    'retour.routes.empty' => 'Keine Weiterleitungen bisher',

    'retour.routes.fields.from' => 'Pfad',
    'retour.routes.fields.from.help' => 'Verwende <a href="{docs}" target="_blank">Platzhalter</a> für dynamische Weiterleitungen.',
    'retour.routes.fields.to' => 'Weiterleiten nach',
    'retour.routes.fields.to.help' => 'Relativer Pfad oder absolute URL. Frei lassen, um Anfrage mit Statuscode abzubrechen.',
    'retour.routes.fields.status' => 'Status',
    'retour.routes.fields.status.help' => 'Lerne die <a href="{docs}" target="_blank">HTTP Statuscodes</a> und wann welchen zu verwenden',
    'retour.routes.fields.priority' => 'Hat Priorität?',
    'retour.routes.fields.priority.abbr' => 'Prio',
    'retour.routes.fields.priority.help' => 'Wenn aktiviert, wird die Weiterleitung auch vor tatsächlich bestehenden Seiten angewendet.',
    'retour.routes.fields.comment' => 'Kommentar',
    'retour.routes.fields.comment.help' => 'Für sich selbst oder andere…',

    'retour.failures'          => 'Fehler',
    'retour.failures.empty'    => 'Keine Fehler bisher',
    'retour.failures.path'     => 'Pfad',
    'retour.failures.referrer' => 'Ursprung',
    'retour.failures.resolve'  => 'Als Weiterleitung hinzufügen',

    'retour.failures.clear'          => 'Log leeren',
    'retour.failures.clear.confirm'  => 'Willst du wirklich alle Logs endgültig löschen?',

    'retour.stats.redirected' => 'weitergeleitet',
    'retour.stats.resolved'   => 'behoben',
    'retour.stats.failed'     => 'gescheitert',
    'retour.stats.mode.all'   => 'Gesamt',
    'retour.stats.mode.year'  => 'Jahr',
    'retour.stats.mode.month' => 'Monat',
    'retour.stats.mode.week'  => 'Woche',
    'retour.stats.mode.day'   => 'Tag',

    'retour.system'                    => 'System',
    'retour.system.update'             => 'Update checken',
    'retour.system.version'            => 'Installierte Version',
    'retour.system.release'            => 'Aktuelles Release',
    'retour.system.support'            => 'Unterstütze die Entwicklung',
    'retour.system.support.donate'     => 'Spende',
    'retour.system.failures'           => 'Aufgezeichnete Fehler',
    'retour.system.redirects'          => 'Erfolgreich weitergeleitet',
    'retour.system.deleteAfter'        => 'Leere Logs nach',
    'retour.system.deleteAfter.months' => '{count} Monaten',
    'retour.system.docs'               => 'Erfahre mehr <a href="{docs}">zu den Einstellungen</a>.'
];
