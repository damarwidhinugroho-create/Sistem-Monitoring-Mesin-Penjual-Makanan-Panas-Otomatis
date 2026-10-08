<?php

namespace App\Contracts;

interface TemperatureRepositoryInterface
{
    public function readings(): array;
}
