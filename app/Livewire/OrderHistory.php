<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::student')]
#[Title('My Orders')]
class OrderHistory extends Component
{
    // Controls which tab is active: 'active', 'completed', or 'cancelled'
    // Defaults to 'active' so students see their live orders first
    public string $filter = 'active';

    /**
     * Loads the student's orders filtered by the selected tab.
     * - active: orders that are still in progress (pending, paid, preparing, ready)
     * - completed: orders that have been picked up
     * - cancelled: orders that were cancelled
     * - default (fallback): all orders regardless of status
     *
     * Orders are sorted newest first and include their items for the summary display.
     */
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
