<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSlotRequest;
use App\Services\InventoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class InventoryController extends Controller
{
    public function index(InventoryService $service): View
    {
        return view('pages.inventaris-stok', ['inventory' => $service->inventory()]);
    }

    public function update(UpdateSlotRequest $request, string $slot, InventoryService $service): RedirectResponse
    {
        $service->updateSlot($slot, $request->validated());

        return redirect()->route('inventory');
    }
}
