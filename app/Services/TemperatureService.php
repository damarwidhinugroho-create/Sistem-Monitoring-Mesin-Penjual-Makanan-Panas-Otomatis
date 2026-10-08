<?php

namespace App\Services;

use App\Contracts\TemperatureRepositoryInterface;
use App\Enums\MachineStatus;
use App\ViewModels\TemperatureViewModel;

class TemperatureService
{
    public function __construct(private readonly TemperatureRepositoryInterface $repository) {}

    public function monitor(): TemperatureViewModel
    {
        $data = $this->repository->readings();
        $data['safe_label'] = $data['temperature'] > 60
            ? 'Suhu aman >60C'
            : 'Suhu kurang dari 60C';

        return new TemperatureViewModel(
            $data,
            MachineStatus::Online,
        );
    }
}
