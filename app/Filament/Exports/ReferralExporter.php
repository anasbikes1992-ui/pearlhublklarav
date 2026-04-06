<?php

namespace App\Filament\Exports;

use App\Models\Referral;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ReferralExporter extends Exporter
{
    protected static ?string $model = Referral::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('referrer.full_name')
                ->label('Referrer'),
            ExportColumn::make('referrer.email')
                ->label('Referrer Email'),
            ExportColumn::make('referred.full_name')
                ->label('Referred User'),
            ExportColumn::make('code'),
            ExportColumn::make('status'),
            ExportColumn::make('referral_type')
                ->label('Type'),
            ExportColumn::make('points_awarded')
                ->label('Points'),
            ExportColumn::make('revenue_bonus_amount')
                ->label('Bonus (LKR)'),
            ExportColumn::make('bonus_paid_at')
                ->label('Paid At'),
            ExportColumn::make('created_at')
                ->label('Date'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your referral export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
