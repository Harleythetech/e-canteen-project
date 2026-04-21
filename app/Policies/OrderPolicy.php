<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id
            || $user->isStaff()
            || $user->isAdmin();
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isStaff() || $user->isAdmin();
    }

    public function cancel(User $user, Order $order): bool
    {
        // Students can cancel their own pending orders
        if ($user->id === $order->user_id && $order->status === 'pending') {
            return true;
        }

        return $user->isAdmin();
    }
}
