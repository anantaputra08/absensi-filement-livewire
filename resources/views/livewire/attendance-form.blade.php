<div class="max-w-full mx-auto p-6 bg-white rounded-lg shadow-md">
    <form wire:submit.prevent="submit" class="mb-4">
        <label for="rfid_card" class="block text-sm font-medium text-gray-700">RFID Card</label>
        <input 
            type="text" 
            id="rfid_card" 
            wire:model="rfid_card" 
            autofocus 
            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            placeholder="Scan your RFID card..."
        >
    </form>

    @if ($student)
        <div class="mt-4 p-6 bg-blue-50 rounded-lg shadow-sm border border-blue-200">
            <h3 class="text-xl font-semibold text-blue-800 mb-4">Student Information</h3>
            <div class="space-y-3 text-blue-900">
                <p><span class="font-medium">NIS:</span> <span class="text-blue-700">{{ $student->nis }}</span></p>
                <p><span class="font-medium">Name:</span> <span class="text-blue-700">{{ $student->name }}</span></p>
                <p><span class="font-medium">Class:</span> <span class="text-blue-700">{{ $student->class }}</span></p>
                <p><span class="font-medium">Check-in:</span> <span class="text-blue-700">{{ $student->attendances->last()->check_in }}</span></p>
                <p><span class="font-medium">Status:</span> 
                    <span class="px-3 py-1 rounded-md text-gray-200 font-semibold 
                        @if($student->attendances->last()->status === 'masuk') bg-green-500 
                        @elseif($student->attendances->last()->status === 'telat') bg-yellow-500 
                        @else bg-red-500 @endif">
                        {{ $student->attendances->last()->status }}
                    </span>
                </p>
            </div>
        </div>
    @endif
</div>