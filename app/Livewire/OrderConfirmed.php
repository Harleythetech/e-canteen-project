<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::student')]
#[Title('Order Confirmed')]
class OrderConfirmed extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        abort_unless(auth()->id() === $order->user_id, 403);
    }

    public function render()
    {
        return view('livewire.order-confirmed');
    }
}
