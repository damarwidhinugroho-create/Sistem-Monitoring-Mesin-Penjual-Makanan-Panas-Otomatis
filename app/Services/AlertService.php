<?php

namespace App\Services;

use App\Contracts\AlertRepositoryInterface;

class AlertService
{
    public function __construct(private readonly AlertRepositoryInterface $repository) {}

    public function history(): array
    {
        return $this->repository->recent();
    }
}
