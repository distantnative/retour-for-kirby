<?php

namespace distantnative\Retour;

return [
    '3.0.0' => function (Retour $retour) {
        $data = ['routes' => $retour->config];
        $retour->update($data);
    }
];
