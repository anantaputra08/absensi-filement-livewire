<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\RfidCard;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AttendanceForm extends Component
{
    public $rfid_card;
    public $student;
    public $message;

    protected $rules = [
        'rfid_card' => 'required',
    ];

    public function updatedRfidCard($value)
    {
        $this->submit();
    }

    public function submit()
    {
        $this->validate();

        // Cek apakah kartu RFID ada di database
        $rfidCard = RfidCard::where('rfid_card', $this->rfid_card)->first();

        // Jika kartu tidak ditemukan
        if (!$rfidCard) {
            Notification::make()
                ->title('RFID card not found!')
                ->body('Please contact Guidance and Counseling (BK) to register your RFID card.')
                ->danger()
                ->send();

            $this->reset('rfid_card');
            return;
        }

        // Ambil data student yang terkait dengan kartu RFID
        $student = $rfidCard->student;

        // Jika tidak ada student yang terkait dengan kartu RFID
        if (!$student) {
            Notification::make()
                ->title('RFID card not registered!')
                ->body('Please contact Guidance and Counseling (BK) to register your RFID card.')
                ->danger()
                ->send();

            $this->reset('rfid_card');
            return;
        }

        // Cek apakah sudah ada attendance hari ini
        $today = Carbon::today();
        $existingAttendance = Attendance::where('rfid_card', $this->rfid_card)
            ->whereDate('check_in', $today)
            ->first();

        // Jika sudah masuk waktu checkout
        if (AttendanceSetting::isCheckOutTime()) {
            if ($existingAttendance && !$existingAttendance->check_out) {
                $existingAttendance->update([
                    'check_out' => now()
                ]);

                $this->student = $student;
                $this->reset('rfid_card');

                Notification::make()
                    ->title('Checkout successful!')
                    ->success()
                    ->send();

                return;
            }
        }

        // Jika sudah check-in sebelumnya
        if ($existingAttendance && !AttendanceSetting::isCheckOutTime()) {
            $this->student = $student;
            $this->reset('rfid_card');

            Notification::make()
                ->title('You have already checked in today!')
                ->success()
                ->send();

            return;
        }

        // Jika sudah check-out sebelumnya
        if ($existingAttendance && AttendanceSetting::isCheckOutTime()) {
            $this->student = $student;
            $this->reset('rfid_card');

            Notification::make()
                ->title('You have already checked out today!')
                ->success()
                ->send();

            return;
        }

        // Logic untuk check in dan status
        if (!$existingAttendance) {
            if (AttendanceSetting::isCheckOutTime()) {
                // Jika sudah waktu checkout (terlalu telat), status alpa
                Attendance::create([
                    'rfid_card' => $this->rfid_card,
                    'check_in' => now(),
                    'status' => 'alpa',
                ]);

                Notification::make()
                    ->title('You have passed the check in deadline!')
                    ->body('If you forget to check in, please contact Guidance and Counseling (BK).')
                    ->danger()
                    ->send();
                // $this->message = 'Anda telah melewati batas waktu check in dan checkout!';
            } else if (AttendanceSetting::isLate()) {
                // Jika lewat batas check in tapi belum waktu checkout, status telat
                Attendance::create([
                    'rfid_card' => $this->rfid_card,
                    'check_in' => now(),
                    'status' => 'telat',
                ]);

                Notification::make()
                    ->title('You are late checking in!')
                    ->warning()
                    ->send();
                // $this->message = 'Anda terlambat check in!';
            } else {
                // Jika masih dalam waktu check in normal
                Attendance::create([
                    'rfid_card' => $this->rfid_card,
                    'check_in' => now(),
                    'status' => 'masuk',
                ]);

                Notification::make()
                    ->title('Successfully checked in!')
                    ->success()
                    ->send();
            }

            $this->student = $student;
            $this->reset('rfid_card');
        }

        $this->reset('rfid_card');
    }

    public function studentInfolist(Student $record): Infolist
    {
        return Infolist::make()
            ->record($record)
            ->schema([
                Section::make('Student Information')
                    ->heading('Student Information')
                    ->schema([
                        \Filament\Infolists\Components\Grid::make([
                            'default' => 3,
                            'sm' => 1,
                            'md' => 3,
                        ])
                            ->schema([
                                // First Column - NIS and Check-in
                                \Filament\Infolists\Components\Group::make([
                                    TextEntry::make('nis')
                                        ->label('NIS')
                                        ->weight('bold')
                                        ->color('primary'),

                                    TextEntry::make('check_in')
                                        ->label('Check-in')
                                        ->weight('bold')
                                        ->color('primary')
                                        ->state(function (Student $record): ?string {
                                            return $record->attendances->last()?->check_in;
                                        }),
                                ]),

                                // Second Column - Name and Check-out
                                \Filament\Infolists\Components\Group::make([
                                    TextEntry::make('name')
                                        ->label('Name')
                                        ->weight('bold')
                                        ->color('primary'),

                                    TextEntry::make('check_out')
                                        ->label('Check-out')
                                        ->weight('bold')
                                        ->color('primary')
                                        ->state(function (Student $record): ?string {
                                            return $record->attendances->last()?->check_out;
                                        })
                                        ->visible(function (Student $record): bool {
                                            return $record->attendances->last()?->check_out !== null;
                                        }),
                                ]),

                                // Third Column - Status
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->state(function (Student $record): ?string {
                                        return $record->attendances->last()?->status;
                                    })
                                    ->color(function (Student $record): string {
                                        $status = $record->attendances->last()?->status;
                                        return match ($status) {
                                            'masuk' => 'success',
                                            'telat' => 'warning',
                                            default => 'danger',
                                        };
                                    })
                                    ->visible(function (Student $record): bool {
                                        return $record->attendances->isNotEmpty();
                                    }),
                            ]),
                    ]),
            ]);
    }

    public function render()
    {
        return view('livewire.attendance-form');
    }
}