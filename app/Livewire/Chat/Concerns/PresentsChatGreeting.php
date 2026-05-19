<?php

namespace App\Livewire\Chat\Concerns;

use App\Domains\Calendar\Enums\AgendaKind;
use App\Domains\Calendar\Services\ListTodayAgendaService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

trait PresentsChatGreeting
{
    #[Computed]
    public function greeting(): string
    {
        $hour = (int) now()->format('G');
        $part = match (true) {
            $hour < 12 => __('Good morning'),
            $hour < 18 => __('Good afternoon'),
            default => __('Good evening'),
        };

        $user = Auth::user();
        $first = $user->first_name
            ?: (string) (explode(' ', (string) ($user->name ?: $user->email))[0] ?? '');

        return $first !== '' ? "{$part}, {$first}" : $part;
    }

    /**
     * @return array{date: string, time: string}
     */
    #[Computed]
    public function dateTimePill(): array
    {
        $now = CarbonImmutable::now();

        return [
            'date' => mb_strtoupper($now->format('l, F j')),
            'time' => $now->format('g:i A'),
        ];
    }

    /**
     * @return array{meetings: int, tasks: int}
     */
    #[Computed]
    public function tomorrowSummary(): array
    {
        $rows = app(ListTodayAgendaService::class)->executeFor(
            Auth::user(),
            CarbonImmutable::tomorrow(),
        );

        $meetings = 0;
        $tasks = 0;
        foreach ($rows as $row) {
            $row->kind === AgendaKind::Event ? $meetings++ : $tasks++;
        }

        return ['meetings' => $meetings, 'tasks' => $tasks];
    }
}
