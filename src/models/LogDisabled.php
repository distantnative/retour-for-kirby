<?php

namespace distantnative\Retour;

final class LogDisabled
{
    public function __call(string $property, array $arguments)
    {
        return false;
    }
}
