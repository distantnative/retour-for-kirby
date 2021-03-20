<?php

namespace distantnative\Retour;

return function ($kirby) {
    return retour()->redirects()->toRoutes(true);
};
