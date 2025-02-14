<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Exports\StudentExporter;
use App\Filament\Imports\StudentImporter;
use App\Models\Student;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(StudentExporter::class)
                ->color('danger')
                ->icon('heroicon-c-arrow-up-tray'),
            Actions\ImportAction::make()
                ->importer(StudentImporter::class)
                ->color('success')
                ->icon('heroicon-c-arrow-down-tray'),
            Actions\CreateAction::make()
                ->color('primary')
                ->icon('heroicon-c-plus'),
        ];
    }
    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->withTrashed())
                ->badge(Student::withTrashed()->count())
                ->badgeColor('primary'),

            'active' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutTrashed())
                ->badge(Student::withoutTrashed()->count())
                ->badgeColor('success'),

            'deleted' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->onlyTrashed())
                ->badge(Student::onlyTrashed()->count())
                ->badgeColor('danger'),
        ];
    }
    public function getDefaultActiveTab(): string|int|null
    {
        return 'active';
    }
}
