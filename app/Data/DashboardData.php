<?php

namespace App\Data;


class DashboardData
{
    public function __construct(
        public readonly int $users,
        public readonly int $activeOrders,
        public readonly int $expiredOrders,
        public readonly int $totalOrders,
        /** @var array<string,float> */
        public readonly array $earningsByCurrency,
    ) {}
}
