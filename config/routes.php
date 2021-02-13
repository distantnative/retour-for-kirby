<?php

namespace distantnative\Retour;

return function ($kirby) {
    return retour()->routes()->toRules(true);
};
