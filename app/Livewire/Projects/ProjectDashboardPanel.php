<?php

namespace App\Livewire\Projects;

use App\Domains\TimeTracking\Services\ExportMonthTimeEntriesService;
use App\Domains\TimeTracking\Services\ProjectTimeStatsService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectDashboardPanel extends Component
{
    #[Locked]
    public int $projectId = 0;

    public string $scope = 'me';

    public string $monthAnchor = '';

    public function mount(int $projectId): void
    {
        $this->projectId = $projectId;
        $this->monthAnchor = Carbon::now()->startOfMonth()->toDateString();
    }

    public function setScope(string $scope): void
    {
        if (! in_array($scope, ['me', 'team'], true)) {
            return;
        }

        $this->scope = $scope;
        $this->refreshChart();
    }

    public function previousMonth(): void
    {
        $this->monthAnchor = CarbonImmutable::parse($this->monthAnchor)
            ->subMonth()
            ->startOfMonth()
            ->toDateString();
        $this->refreshChart();
    }

    public function nextMonth(): void
    {
        $this->monthAnchor = CarbonImmutable::parse($this->monthAnchor)
            ->addMonth()
            ->startOfMonth()
            ->toDateString();
        $this->refreshChart();
    }

    public function goToCurrentMonth(): void
    {
        $this->monthAnchor = Carbon::now()->startOfMonth()->toDateString();
        $this->refreshChart();
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function dailyHours(): array
    {
        return app(ProjectTimeStatsService::class)->dailyHoursForMonth(
            Auth::user(),
            $this->projectId,
            CarbonImmutable::parse($this->monthAnchor),
            $this->resolvedUserId(),
        );
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function dailyEntryCounts(): array
    {
        return app(ProjectTimeStatsService::class)->dailyEntryCountsForMonth(
            Auth::user(),
            $this->projectId,
            CarbonImmutable::parse($this->monthAnchor),
            $this->resolvedUserId(),
        );
    }

    #[Computed]
    public function totalAllTimeSeconds(): int
    {
        return app(ProjectTimeStatsService::class)->totalSecondsForProject(
            Auth::user(),
            $this->projectId,
            $this->resolvedUserId(),
        );
    }

    #[Computed]
    public function totalMonthSeconds(): int
    {
        return (int) array_sum($this->dailyHours);
    }

    /**
     * @return array{rate: float, currency: string}|null
     */
    #[Computed]
    public function billingRate(): ?array
    {
        return app(ProjectTimeStatsService::class)->billingRateForProject(
            Auth::user(),
            $this->projectId,
        );
    }

    #[Computed]
    public function totalMonthMoney(): ?string
    {
        $rate = $this->billingRate;
        if ($rate === null) {
            return null;
        }

        $amount = ($this->totalMonthSeconds / 3600) * $rate['rate'];

        return number_format($amount, 2, '.', "'").' '.$rate['currency'];
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function chartLabels(): array
    {
        return array_map(
            fn (string $day) => (string) (int) substr($day, -2),
            array_keys($this->dailyHours),
        );
    }

    /**
     * @return array<int, float>
     */
    #[Computed]
    public function chartData(): array
    {
        return array_map(
            fn (int $seconds) => round($seconds / 3600, 2),
            array_values($this->dailyHours),
        );
    }

    /**
     * Entry counts per day, parallel-indexed with $chartLabels and $chartData.
     *
     * @return array<int, int>
     */
    #[Computed]
    public function chartCounts(): array
    {
        return array_values($this->dailyEntryCounts);
    }

    #[Computed]
    public function monthLabel(): string
    {
        return Carbon::parse($this->monthAnchor)->translatedFormat('F Y');
    }

    #[Computed]
    public function isCurrentMonth(): bool
    {
        return $this->monthAnchor === Carbon::now()->startOfMonth()->toDateString();
    }

    #[On('time-entries-updated')]
    public function refresh(): void
    {
        $this->refreshChart();
    }

    public function exportMonth(ExportMonthTimeEntriesService $service): StreamedResponse
    {
        $export = $service->execute(
            Auth::user(),
            $this->projectId,
            CarbonImmutable::parse($this->monthAnchor),
            $this->resolvedUserId(),
        );

        return response()->streamDownload(
            fn () => print $export->contents,
            $export->filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    public function render()
    {
        return view('livewire.projects.project-dashboard-panel');
    }

    private function resolvedUserId(): ?int
    {
        return $this->scope === 'me' ? (int) Auth::id() : null;
    }

    private function refreshChart(): void
    {
        unset(
            $this->dailyHours,
            $this->dailyEntryCounts,
            $this->totalAllTimeSeconds,
            $this->totalMonthSeconds,
            $this->totalMonthMoney,
            $this->chartLabels,
            $this->chartData,
            $this->chartCounts,
            $this->monthLabel,
            $this->isCurrentMonth,
        );

        $this->dispatch(
            'chart-data-updated',
            labels: $this->chartLabels,
            values: $this->chartData,
            counts: $this->chartCounts,
        );
    }
}
