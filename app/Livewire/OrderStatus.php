<?php

namespace App\Livewire;

use App\Models\Order;
use App\Services\QrCodeService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::student')]
#[Title('Order Status')]
class OrderStatus extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->authorize('view', $order);
        $this->order = $order->load('items');
    }

    public function cancelOrder(): void
    {
        $this->authorize('cancel', $this->order);

        if ($this->order->canTransitionTo('cancelled')) {
            $this->order->transitionTo('cancelled');
        }
    }

    public function render(QrCodeService $qrCodeService)
    {
        $this->order->refresh();

        $qrSvg = in_array($this->order->status, ['paid', 'preparing', 'ready'])
            ? $qrCodeService->generateSvg($this->order->order_number)
            : null;

        return view('livewire.order-status', ['qrSvg' => $qrSvg]);
    }
}
