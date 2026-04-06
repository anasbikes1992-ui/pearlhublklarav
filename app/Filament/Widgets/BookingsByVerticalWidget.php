<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class BookingsByVerticalWidget extends ChartWidget
{
    protected static ?string $heading = 'Bookings by Vertical';

    protected function getData(): array
    {
        $data = Booking::query()
            ->join('listings', 'bookings.listing_id', '=', 'listings.id')
            ->whereNotIn('bookings.status', ['cancelled', 'refunded'])
            ->where('bookings.created_at', '>=', now()->subDays(30))
            ->selectRaw('listings.vertical, COUNT(*) as count, SUM(bookings.total_amount) as revenue')
            ->groupBy('listings.vertical')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Bookings',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.8)',  // property - emerald
                        'rgba(59, 130, 246, 0.8)',  // stay - blue
                        'rgba(245, 158, 11, 0.8)',  // vehicle - amber
                        'rgba(239, 68, 68, 0.8)',   // event - red
                        'rgba(139, 92, 246, 0.8)',  // sme - purple
                    ],
                ],
            ],
            'labels' => $data->pluck('vertical')->map(fn ($v) => ucfirst($v))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
