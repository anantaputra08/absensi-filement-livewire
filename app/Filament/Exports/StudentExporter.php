<?php

namespace App\Filament\Exports;

use App\Models\Student;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class StudentExporter extends Exporter
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('nis'),
            ExportColumn::make('name'),
            ExportColumn::make('gender'),
            ExportColumn::make('birth_date'),
            ExportColumn::make('birth_place'),
            ExportColumn::make('address'),
            ExportColumn::make('phone'),
            ExportColumn::make('email'),
            ExportColumn::make('religion'),
            ExportColumn::make('blood_type'),
            ExportColumn::make('father_name'),
            ExportColumn::make('mother_name'),
            ExportColumn::make('guardian_name'),
            ExportColumn::make('father_occupation'),
            ExportColumn::make('mother_occupation'),
            ExportColumn::make('entry_year'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your student export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
