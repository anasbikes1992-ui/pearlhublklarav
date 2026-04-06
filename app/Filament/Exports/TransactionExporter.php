<?php

namespace App\Filament\Exports;

use App\Models\Transaction;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TransactionExporter extends Exporter
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('wallet.user.full_name')
                ->label('User'),
            ExportColumn::make('wallet.user.email')
                ->label('User Email'),
            ExportColumn::make('provider')
                ->label('Payment Provider'),
            ExportColumn::make('external_reference')
                ->label('Reference'),
            ExportColumn::make('amount')
                ->label('Amount (LKR)'),
            ExportColumn::make('currency'),
            ExportColumn::make('status'),
            ExportColumn::make('created_at')
                ->label('Transaction Date'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your transaction export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
