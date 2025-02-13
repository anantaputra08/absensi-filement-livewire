<?php

namespace App\Filament\Imports;

use App\Models\Student;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class StudentImporter extends Importer
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nis')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('gender')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('birth_date')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('birth_place')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('address')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('phone')
                ->rules(['max:255']),
            ImportColumn::make('email')
                ->rules(['email', 'max:255']),
            ImportColumn::make('religion')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('blood_type')
                ->rules(['max:255']),
            ImportColumn::make('father_name')
                ->rules(['max:255']),
            ImportColumn::make('mother_name')
                ->rules(['max:255']),
            ImportColumn::make('guardian_name')
                ->rules(['max:255']),
            ImportColumn::make('father_occupation')
                ->rules(['max:255']),
            ImportColumn::make('mother_occupation')
                ->rules(['max:255']),
            ImportColumn::make('entry_year')
                ->requiredMapping()
                ->rules(['required', 'max:9']),
        ];
    }

    public function resolveRecord(): ?Student
    {
        return Student::firstOrNew() 
        ->where('nis', $this->data['nis'])
        ->first(); 
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your student import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
