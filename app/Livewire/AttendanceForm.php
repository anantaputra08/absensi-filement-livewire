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

        $rfidCard = RfidCard::where('rfid_card', $this->rfid_card)->firstOrFail();
        $student = $rfidCard ? $rfidCard->student : null;

        if ($student) {
            if (AttendanceSetting::isAttendanceClosed()) {
                $status = 'alpa';
            } elseif (AttendanceSetting::isLate()) {
                $status = 'telat';
            } else {
                $status = 'masuk';
            }

            Attendance::create([
                'rfid_card' => $this->rfid_card,
                'check_in' => now(),
                'status' => $status,
            ]);

            $this->student = $student;
        }

        $this->reset('rfid_card');
    }

    public function render()
    {
        return view('livewire.attendance-form', [
            'student' => $this->student,
        ]);
    }
}
