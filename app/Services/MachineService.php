<?php

namespace App\Services;

use App\Contracts\MachineRepositoryInterface;
use App\Enums\MachineStatus;
use App\ViewModels\MachineViewModel;

class MachineService
{
    public function __construct(private readonly MachineRepositoryInterface $repository) {}

    public function details(): MachineViewModel
    {
        return new MachineViewModel($this->repository->details(), MachineStatus::Online);
    }
}
