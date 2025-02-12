<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nis')
                    ->label('NIS')
                    ->disabled()
                    ->dehydrated()
                    ->afterStateHydrated(function (Forms\Components\TextInput $component, $record) {
                        if (!$record || !$record->nis) {
                            // Generate NIS baru hanya jika $record tidak ada atau $record->nis null
                            $year = date('y'); // Ambil tahun dua digit
                            $month = date('m'); // Ambil bulan dua digit
                            $lastStudent = Student::whereYear('created_at', date('Y'))->orderBy('id', 'desc')->first();
                            $lastNumber = $lastStudent ? (int) substr($lastStudent->nis, -3) : 0; // Ambil 3 digit terakhir dari NIS
                            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT); // Generate nomor urut
                            $component->state("{$year}{$month}{$newNumber}"); // Format NIS
                        }
                    }), 
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('gender')
                    ->required()
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),
                Forms\Components\DatePicker::make('birth_date')
                    ->required(),
                Forms\Components\TextInput::make('birth_place')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('religion')
                    ->required()
                    ->options([
                        'Kristen' => 'Kristen',
                        'Islam' => 'Islam',
                        'Hindu' => 'Hindu',
                        'Buddha' => 'Buddha',
                        'Lainnya' => 'Lainnya',
                    ]),
                Forms\Components\Select::make('blood_type')
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                        'AB' => 'AB',
                        'O' => 'O',
                    ]),
                Forms\Components\TextInput::make('father_name')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('father_occupation')
                    ->options([
                        'Wiraswasta' => 'Wiraswasta',
                        'PNS' => 'PNS',
                        'Wirausaha' => 'Wirausaha',
                    ]),
                Forms\Components\TextInput::make('mother_name')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('mother_occupation')
                    ->options([
                        'Wiraswasta' => 'Wiraswasta',
                        'PNS' => 'PNS',
                        'Wirausaha' => 'Wirausaha',
                    ]),
                Forms\Components\TextInput::make('guardian_name')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('entry_year')
                    ->label('Entry Year')
                    ->required()
                    ->default(date('Y') . '/' . (date('Y') + 1))
                    ->maxLength(9)
                    ->regex('/^\d{4}\/\d{4}$/'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender'),
                Tables\Columns\TextColumn::make('birth_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('birth_place')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('religion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('blood_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('father_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mother_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('guardian_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('father_occupation')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mother_occupation')
                    ->searchable(),
                Tables\Columns\TextColumn::make('entry_year'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            // 'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}