<?php

namespace Tests\Unit\Console;

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Event;
use Tests\TestCase;

class SchedulerTest extends TestCase
{
    public function test_database_queue_uses_one_bounded_non_overlapping_worker(): void
    {
        config()->set('queue.default', 'database');

        $events = $this->queueWorkerEvents();

        $this->assertCount(1, $events);

        $event = $events[0];
        foreach ([
            '--stop-when-empty',
            '--max-time=50',
            '--max-jobs=20',
            '--sleep=1',
            '--tries=3',
            '--timeout=40',
            '--backoff=5',
            '--memory=128',
            '--no-interaction',
        ] as $option) {
            $this->assertStringContainsString($option, $event->command);
        }

        $this->assertSame('* * * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
        $this->assertSame(5, $event->expiresAt);
    }

    public function test_sync_queue_does_not_schedule_a_worker(): void
    {
        config()->set('queue.default', 'sync');

        $this->assertSame([], $this->queueWorkerEvents());
    }

    /**
     * @return list<Event>
     */
    private function queueWorkerEvents(): array
    {
        $schedule = $this->app->make(ConsoleKernel::class)->resolveConsoleSchedule();

        return array_values(array_filter(
            $schedule->events(),
            fn (Event $event): bool => str_contains($event->command ?? '', 'queue:work')
        ));
    }
}
