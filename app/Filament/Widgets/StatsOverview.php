<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\RfidCard;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected ?string $heading = 'Analytics';
    protected function getStats(): array
    {
        return [
            Stat::make('Total Checkin Today', Attendance::whereDate('created_at', Carbon::today())->count())
                ->description('Real-time attendance checked in count')
                ->Icon('tabler-door-enter')
                ->color('success'),

            Stat::make('Total Checkout Today', Attendance::whereDate('check_out', Carbon::today())->count())
                ->description('Real-time attendance checked out count')
                ->Icon('tabler-door-exit')
                ->color('danger'),

            Stat::make('Total Student', Student::count())
                ->icon('heroicon-s-user-group')
                ->color('primary'),

            Stat::make('Total Card Registered', RfidCard::count())
                ->icon('heroicon-s-credit-card')
                ->color('primary'),

            Stat::make('Total Masuk', Attendance::whereDate('created_at', Carbon::today())->where('status', 'masuk')->count())
                ->description('Real-time attendance in count')
                ->Icon('tabler-door-enter')
                ->color('success'),

            Stat::make('Total Telat', Attendance::whereDate('created_at', Carbon::today())->where('status', 'telat')->count())
                ->description('Real-time attendance late count')
                ->Icon('tabler-door-enter')
                ->color('warning'),

            Stat::make('Total Alpa', Attendance::whereDate('created_at', Carbon::today())->where('status', 'alpa')->count())
                ->description('Real-time attendance alpa count')
                ->Icon('tabler-door-enter')
                ->color('danger'),

            Stat::make('Total Attendance Today', Attendance::whereDate('created_at', Carbon::today())->count())
                ->Icon('heroicon-o-check-badge'),
        ];
    }
    protected function getColumns(): int
    {
        return 4; // Menampilkan 4 kolom dalam satu baris
    }
}
