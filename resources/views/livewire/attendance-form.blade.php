<div class="max-w-full p-6 bg-white dark:bg-black rounded-lg shadow-md">
    <div class="mb-4">
        <label for="rfid_card" class="block text-sm font-medium text-gray-700">RFID Card</label>
        <input type="text" id="rfid_card" wire:model.live="rfid_card" wire:keydown.enter="submit" autofocus
            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            placeholder="Scan your RFID card...">
    </div>

    @if ($student)
        <div class="mt-4">
            {{ $this->studentInfolist($student) }}
        </div>
    @endif
</div>
