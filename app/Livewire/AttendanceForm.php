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
        $this->message = '';

        // Cek apakah kartu RFID ada di database
        $rfidCard = RfidCard::where('rfid_card', $this->rfid_card)->first();

        // Jika kartu tidak ditemukan
        if (!$rfidCard) {
            $this->message = 'Kartu RFID tidak ditemukan';
            $this->reset('rfid_card');
            return;
        }

        // Ambil data student yang terkait dengan kartu RFID
        $student = $rfidCard->student;

        // Jika tidak ada student yang terkait dengan kartu RFID
        if (!$student) {
            $this->message = 'Kartu RFID tidak terdaftar';
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
                // dd($existingAttendance);  
                $this->message = 'Berhasil checkout!';
                $this->student = $student;
                $this->reset('rfid_card');
                return;
            }
        }

        // Jika sudah check-in sebelumnya
        if ($existingAttendance && !AttendanceSetting::isCheckOutTime()) {
            $this->message = 'Anda sudah melakukan check in hari ini!';
            $this->student = $student;
            $this->reset('rfid_card');
            return;
        }

        // Jika sudah check-out sebelumnya
        if ($existingAttendance && AttendanceSetting::isCheckOutTime()) {
            $this->message = 'Anda sudah melakukan check out hari ini!';
            $this->student = $student;
            $this->reset('rfid_card');
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
                // $this->message = 'Berhasil check in!';
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
                        TextEntry::make('nis')
                            ->label('NIS')
                            ->weight('bold')
                            ->color('primary'),

                        TextEntry::make('name')
                            ->label('Name')
                            ->weight('bold')
                            ->color('primary'),

                        TextEntry::make('class')
                            ->label('Class')
                            ->weight('bold')
                            ->color('primary'),

                        TextEntry::make('check_in')
                            ->label('Check-in')
                            ->weight('bold')
                            ->color('primary')
                            ->state(function (Student $record): ?string {
                                return $record->attendances->last()?->check_in;
                            }),

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
            ]);
    }

    public function render()
    {
        return view('livewire.attendance-form');
    }
}