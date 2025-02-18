<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\RfidCard;
use Carbon\Carbon;
use Livewire\Component;

class AttendanceForm extends Component
{
    public $rfid_card;
    public $student;
    public $message;

    protected $rules = [
        'rfid_card' => 'required|exists:rfid_cards,rfid_card',
    ];

    public function updatedRfidCard($value)
    {
        $this->submit();
    }

    public function submit()
    {
        $this->validate();
        $this->message = '';
        
        $rfidCard = RfidCard::where('rfid_card', $this->rfid_card)->firstOrFail();
        $student = $rfidCard ? $rfidCard->student : null;

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

        // Jika sudah checkout time, update checkout untuk attendance yang belum checkout
        if (AttendanceSetting::isCheckOutTime()) {
            if ($existingAttendance && !$existingAttendance->check_out) {
                $existingAttendance->update([
                    'check_out' => now()
                ]);
                $this->message = 'Berhasil checkout!';
                $this->student = $student;
                $this->reset('rfid_card');
                return;
            }
        }

        // Jika sudah ada attendance hari ini dan masih dalam waktu checkin
        if ($existingAttendance && !AttendanceSetting::isCheckOutTime()) {
            $this->message = 'Anda sudah melakukan check in hari ini!';
            $this->student = $student;
            $this->reset('rfid_card');
            return;
        }

        // Jika belum checkin dan sudah melewati batas waktu checkin
        if (!$existingAttendance && AttendanceSetting::isLate()) {
            Attendance::create([
                'rfid_card' => $this->rfid_card,
                'check_in' => now(),
                'status' => 'alpa',
            ]);
            $this->message = 'Anda telah melewati batas waktu check in!';
            $this->student = $student;
            $this->reset('rfid_card');
            return;
        }

        // Jika belum checkin dan masih dalam waktu checkin
        if (!$existingAttendance && !AttendanceSetting::isLate()) {
            $status = AttendanceSetting::isLate() ? 'telat' : 'masuk';
            Attendance::create([
                'rfid_card' => $this->rfid_card,
                'check_in' => now(),
                'status' => $status,
            ]);
            $this->message = 'Berhasil check in!';
            $this->student = $student;
        }

        $this->reset('rfid_card');
    }

    public function render()
    {
        return view('livewire.attendance-form', [
            'student' => $this->student,
            'message' => $this->message
        ]);
    }
}