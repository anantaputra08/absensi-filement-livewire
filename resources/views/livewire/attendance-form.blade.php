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

    {{-- @if ($student)
        <div class="mt-4 p-6 bg-blue-50 rounded-lg shadow-sm border-blue-200">
            <div class="bg-blue-600 px-4 py-3">
                <h2 class="text-lg font-semibold text-black">Student Information</h2>
            </div>
                <!-- Content -->
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Student ID -->
                        <div class="flex pb-2">
                            <div class="w-1/3">
                                <span class="font-medium text-gray-600">NIS : </span>
                            </div>
                            <div class="w-2/3">
                                <span class="text-blue-700 font-medium"> {{ $student->nis }}</span>
                            </div>
                        </div>

                        <!-- Student Name -->
                        <div class="flex pb-2">
                            <div class="w-1/3">
                                <span class="font-medium text-gray-600">Name:</span>
                            </div>
                            <div class="w-2/3">
                                <span class="text-blue-700 font-medium">{{ $student->name }}</span>
                            </div>
                        </div>

                        <!-- Student Class -->
                        <div class="flex pb-2">
                            <div class="w-1/3">
                                <span class="font-medium text-gray-600">Class:</span>
                            </div>
                            <div class="w-2/3">
                                <span class="text-blue-700 font-medium">{{ $student->class }}</span>
                            </div>
                        </div>

                        <!-- Student Major -->
                        <div class="flex pb-2">
                            <div class="w-1/3">
                                <span class="font-medium text-gray-600">Check-in :</span>
                            </div>
                            <div class="w-2/3">
                                <span class="text-blue-700 font-medium">{{ $student->attendances->last()->check_in }}</span>
                            </div>
                        </div>

                        <!-- Student Major -->
                        @if ($student->attendances->last()->check_out)
                        <div class="flex pb-2">
                            <div class="w-1/3">
                                <span class="font-medium text-gray-600">Check-out :</span>
                            </div>
                            <div class="w-2/3">
                                <span class="text-blue-700 font-medium">{{ $student->attendances->last()->check_out }}</span>
                            </div>
                        </div>
                        @endif
                    </div>

                @if ($student->attendances->last())
                    <p><span class="font-medium">Status:</span>
                        <span
                            class="px-3 py-1 rounded-md text-gray-200 font-semibold
                            @if ($student->attendances->last()->status === 'masuk') bg-green-500
                            @elseif($student->attendances->last()->status === 'telat') bg-yellow-500
                            @else bg-red-500 @endif">
                            {{ $student->attendances->last()->status }}
                        </span>
                    </p>
                @endif
            </div>
        </div>
    @endif --}}
</div>
