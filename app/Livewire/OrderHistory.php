<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::student')]
#[Title('My Orders')]
class OrderHistory extends Component
{
    public string $filter = 'active';

    public function render()
    {
        $query = auth()->user()->orders()->with('items')->latest();

        $orders = match ($this->filter) {
            'active' => $query->whereIn('status', ['pending', 'paid', 'preparing', 'ready'])->get(),
            'completed' => $query->where('status', 'completed')->get(),
            'cancelled' => $query->where('status', 'cancelled')->get(),
            default => $query->get(),
        };

        return view('livewire.order-history', [
            'orders' => $orders,
        ]);
    }
}
