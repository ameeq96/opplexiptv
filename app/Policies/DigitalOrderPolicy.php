<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Digital\DigitalOrder;

class DigitalOrderPolicy
{
    public function viewAdmin(Admin $admin, DigitalOrder $order): bool
    {
        return !empty($admin->id);
    }

    public function updateAdmin(Admin $admin, DigitalOrder $order): bool
    {
        return !empty($admin->id);
    }

    public function assignDelivery(Admin $admin, DigitalOrder $order): bool
    {
        return !empty($admin->id);
    }

    public function markPaid(Admin $admin, DigitalOrder $order): bool
    {
        return !empty($admin->id);
    }
}
