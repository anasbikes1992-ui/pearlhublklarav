<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $todayRevenue = Booking::query()
            ->whereDate('created_at', today())
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->sum('total_amount');

        $weekRevenue = Booking::query()
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->sum('total_amount');

        $monthRevenue = Booking::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->sum('total_amount');

        $totalRevenue = Booking::query()
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->sum('total_amount');

        $pendingPayouts = Transaction::query()
            ->where('status', 'pending')
            ->sum('amount');

        return [
            Stat::make('Today\'s Revenue', 'LKR ' . number_format($todayRevenue, 2))
                ->description('Bookings today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('This Week', 'LKR ' . number_format($weekRevenue, 2))
                ->description('Week to date')
                ->color('info'),

            Stat::make('This Month', 'LKR ' . number_format($monthRevenue, 2))
                ->description('Month to date')
                ->color('warning'),

            Stat::make('Total Revenue', 'LKR ' . number_format($totalRevenue, 2))
                ->description('All time')
                ->color('primary'),

            Stat::make('Pending Payouts', 'LKR ' . number_format($pendingPayouts, 2))
                ->description('Awaiting processing')
                ->color('danger'),
        ];
    }
}
