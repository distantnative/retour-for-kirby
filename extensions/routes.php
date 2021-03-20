<?php

namespace distantnative\Retour;

return function (): array {
    return retour()->redirects()->toRoutes(true);
};
