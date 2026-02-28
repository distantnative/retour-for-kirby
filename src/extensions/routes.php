<?php

namespace Kirby\Retour;

return fn (): array => Retour::instance()->redirects()->toRoutes(true);
