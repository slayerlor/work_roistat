<?php

namespace App\AmoCRM\Abstracts;

use App\AmoCRM\Interfaces\BuilderInterface;

abstract class AbstractBuilder implements BuilderInterface
{
    protected array $data = [];

    public function build(): array
    {
        return $this->data;
    }
}