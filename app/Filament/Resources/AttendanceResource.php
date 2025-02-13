<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\Select::make('rfid_card')
                //     ->label('Student Name')
                //     ->options(function () {
                //         return \App\Models\RfidCard::with('student')
                //             ->get()
                //             ->pluck('student.name', 'rfid_card');
                //     })
                //     ->searchable()
                //     ->required(),
                Forms\Components\Select::make('student_id')
                    ->label('Student Name')
                    ->options(function () {
                        return \App\Models\Student::pluck('name', 'id');
                    })
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search) {
                        return \App\Models\Student::query()
                            ->where('nis', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->reactive() // Supaya RFID Card otomatis berubah saat siswa dipilih
                    ->required()
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        $studentId = $get('student_id'); // Ambil student_id yang dipilih
                        if ($studentId) {
                            $rfidCard = \App\Models\RfidCard::where('student_id', $studentId)->value('rfid_card');
                            $set('rfid_card', $rfidCard); // Set nilai rfid_card secara otomatis
                        } else {
                            $set('rfid_card', null); // Reset nilai jika tidak ada siswa yang dipilih
                        }
                    }),

                Forms\Components\TextInput::make('rfid_card')
                    ->label('Card Number')
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\DateTimePicker::make('check_in')
                    ->required(),
                Forms\Components\DateTimePicker::make('check_out'),
                Forms\Components\TextInput::make('status')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rfid_card')
                    ->label('Card Number')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rfidCard.student.nis')
                    ->label('NIS')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('rfidCard.student.name')
                    ->label('Student Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('check_in')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->formatStateUsing(function ($state) {
                        $updatedAt = Carbon::parse($state);
                        $now = Carbon::now();
                        $diff = $updatedAt->diff($now);

                        $days = $diff->d;
                        $hours = $diff->h;
                        $minutes = $diff->i;

                        $timeString = '';

                        if ($days > 0) {
                            $timeString .= $days . ' day' . ($days > 1 ? 's' : '') . ' ';
                        }
                        if ($hours > 0) {
                            $timeString .= $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ';
                        }
                        if ($minutes > 0) {
                            $timeString .= $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ';
                        }

                        return trim($timeString) . ' ago';
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
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
