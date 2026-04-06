<?php

namespace App\Filament\Exports;

use App\Models\Booking;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BookingExporter extends Exporter
{
    protected static ?string $model = Booking::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('Booking ID'),
            ExportColumn::make('listing.title')
                ->label('Listing'),
            ExportColumn::make('listing.vertical')
                ->label('Vertical'),
            ExportColumn::make('customer.full_name')
                ->label('Customer'),
            ExportColumn::make('customer.email')
                ->label('Customer Email'),
            ExportColumn::make('provider.full_name')
                ->label('Provider'),
            ExportColumn::make('start_at')
                ->label('Start Date'),
            ExportColumn::make('end_at')
                ->label('End Date'),
            ExportColumn::make('status'),
            ExportColumn::make('payment_status'),
            ExportColumn::make('total_amount')
                ->label('Total (LKR)'),
            ExportColumn::make('currency'),
            ExportColumn::make('created_at')
                ->label('Booked At'),
            ExportColumn::make('completed_at')
                ->label('Completed At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your booking export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
