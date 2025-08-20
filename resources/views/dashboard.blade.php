<x-app-layout>
    <div class="">
        <div>
            <x-notification/>
        </div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-purple-300 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-100 space-x-2">
                    <div class="py-10 space-y-5">
                        @php
                            $latestPomodoro = \App\Models\Pomodoro::latest()->first();
                        @endphp

                        @if($latestPomodoro)
                            @livewire('pomodoro-timer', ['pomodoro_id' => $latestPomodoro->id])
                        @else
                            <p>Henüz bir pomodoro yok</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
