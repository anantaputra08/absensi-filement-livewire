<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\RfidCard;
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

        $rfidCard = RfidCard::where('rfid_card', $this->rfid_card)->first();
        $student = $rfidCard ? $rfidCard->student : null;

        if ($student) {
            Attendance::create([
                'rfid_card' => $this->rfid_card,
                'check_in' => now(),
                'status' => 'masuk',
            ]);

            $this->student = $student;
        }

        $this->reset('rfid_card');
    }
    public function render()
    {
        return view('livewire.attendance-form');
    }
}
