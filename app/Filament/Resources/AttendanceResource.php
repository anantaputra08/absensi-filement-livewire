<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->label('Student Name')
                    ->options(function () {
                        return \App\Models\Student::query()
                            ->with('rfidCard')  // Eager load rfidCard
                            ->get()
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search) {
                        return \App\Models\Student::query()
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%")
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        $studentId = $get('student_id');
                        if ($studentId) {
                            // Ambil RFID card dari student
                            $rfidCard = \App\Models\RfidCard::where('student_id', $studentId)
                                ->latest()  // Ambil yang terbaru jika ada multiple cards
                                ->first();

                            if ($rfidCard) {
                                $set('rfid_card', $rfidCard->rfid_card);
                            } else {
                                $set('rfid_card', null);
                            }
                        } else {
                            $set('rfid_card', null);
                        }
                    }),

                Forms\Components\TextInput::make('rfid_card')
                    ->label('RFID Card')
                    ->required()
                    ->disabled()
                    ->reactive()
                    ->dehydrated()
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        $rfidCard = $get('rfid_card');
                        if ($rfidCard) {
                            $card = \App\Models\RfidCard::where('rfid_card', $rfidCard)->first();
                            if ($card) {
                                $set('student_id', $card->student_id);
                            }
                        }
                    }),

                Forms\Components\DateTimePicker::make('check_in')
                    ->required(),

                Forms\Components\DateTimePicker::make('check_out'),

                Forms\Components\Select::make('status')
                    ->options([
                        'telat' => 'Telat',
                        'masuk' => 'Masuk',
                        'izin' => 'Izin',
                    ])
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
                    ->sortable()
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('status')
                    ->sortable()
                    ->color(fn($record) => match ($record->status) {
                        'telat' => 'danger',
                        'masuk' => 'primary',
                        'izin' => 'warning',
                    })
                    ->badge(),
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
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                SelectFilter::make('status')
                    ->options([
                        'telat' => 'Telat',
                        'masuk' => 'Masuk',
                        'izin' => 'Izin',
                    ])
                    ->label('Status'),

                Filter::make('month')
                    ->form([
                        Select::make('month')
                            ->label('Choose Month')
                            ->options([
                                '01' => 'January',
                                '02' => 'February',
                                '03' => 'March',
                                '04' => 'April',
                                '05' => 'May',
                                '06' => 'June',
                                '07' => 'July',
                                '08' => 'August',
                                '09' => 'September',
                                '10' => 'October',
                                '11' => 'November',
                                '12' => 'December',
                            ])
                            ->native(false),
                        Select::make('year')
                            ->label('Choose Year')
                            ->options(
                                Attendance::query()
                                    ->selectRaw('DISTINCT YEAR(created_at) as year')
                                    ->pluck('year', 'year')
                                    ->toArray()
                            )
                            ->native(false)
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['month'] ?? null,
                            fn(Builder $query, $month): Builder => $query->whereMonth('created_at', $month)
                        )->when(
                                $data['year'] ?? null,
                                fn(Builder $query, $year): Builder => $query->whereYear('created_at', $year)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['month'] ?? null) {
                            $month = Carbon::createFromFormat('m', $data['month'])->translatedFormat('F');
                            $indicators[] = Indicator::make('Bulan: ' . $month)
                                ->removeField('month');
                        }

                        if ($data['year'] ?? null) {
                            $indicators[] = Indicator::make('Tahun: ' . $data['year'])
                                ->removeField('year');
                        }

                        return $indicators;
                    }),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')
                            ->native(false),
                        DatePicker::make('until')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('Created from ' . Carbon::parse($data['from'])->toFormattedDateString())
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Created until ' . Carbon::parse($data['until'])->toFormattedDateString())
                                ->removeField('until');
                        }

                        return $indicators;
                    })
                    ->columnSpan(2)->columns(2)
            ], layout: FiltersLayout::AboveContent)
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
