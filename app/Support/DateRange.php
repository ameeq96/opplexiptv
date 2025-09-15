<?php

namespace App\Support;


use Carbon\CarbonImmutable;


class DateRange
{
    public function __construct(
        public readonly ?CarbonImmutable $start,
        public readonly ?CarbonImmutable $end,
    ) {}


    public static function fromFilter(string $filter, ?string $start = null, ?string $end = null): self
    {
        $now = CarbonImmutable::now();
        return match ($filter) {
            'today' => new self($now->startOfDay(), $now->endOfDay()),
            '7days' => new self($now->subDays(6)->startOfDay(), $now->endOfDay()),
            '30days' => new self($now->subDays(29)->startOfDay(), $now->endOfDay()),
            'custom' => new self(
                $start ? CarbonImmutable::parse($start)->startOfDay() : null,
                $end ? CarbonImmutable::parse($end)->endOfDay() : null,
            ),
            default => new self(null, null),
        };
    }


    public function isBounded(): bool
    {
        return $this->start !== null && $this->end !== null;
    }
}
