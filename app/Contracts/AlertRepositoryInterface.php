<?php

namespace App\Contracts;

interface AlertRepositoryInterface
{
    public function recent(): array;
}
