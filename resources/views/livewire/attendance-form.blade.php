<div>
    <form wire:submit.prevent="submit">
        <div>
            <label for="rfid_card">RFID Card</label>
            <input type="text" id="rfid_card" wire:model="rfid_card" autofocus>
        </div>
    </form>

    @if ($student)
        <div>
            <h3>Student Information</h3>
            <p>NIS: {{ $student->nis }}</p>
            <p>Name: {{ $student->name }}</p>
            <p>Class: {{ $student->class }}</p>
            <p>Check-in: {{ $student->attendances->last()->check_in }}</p>
            <p>Status: {{ $student->attendances->last()->status }}</p>
        </div>
    @endif
</div>