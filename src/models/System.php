<?php

namespace distantnative\Retour;

class System {

    protected function license(): array
    {
        return [];
    }

    protected function options(): array
    {
        return [
            'view' => option('distantnative.retour.view', 'dashboard'),
        ];
    }

    public function toArray(): array
    {
        return [
            'options' => $this->options(),
            'license' => $this->license(),
        ];
    }

}
