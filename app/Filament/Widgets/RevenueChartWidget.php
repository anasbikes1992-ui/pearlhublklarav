<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Revenue Trend';

    protected static ?string $pollingInterval = '300s';

    protected function getData(): array
    {
        $days = collect(range(0, 29))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->startOfDay();
            
            $revenue = Booking::query()
                ->whereDate('created_at', $date)
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->sum('total_amount');

            return [
                'date' => $date->format('M d'),
                'revenue' => $revenue / 1000, // Convert to thousands
            ];
        })->reverse()->values();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (LKR Thousands)',
                    'data' => $days->pluck('revenue')->toArray(),
                    'borderColor' => '#00d4ff',
                    'backgroundColor' => 'rgba(0, 212, 255, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $days->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
