<div class="bg-white w-full h-[430px] text-black rounded-xl px-4 py-8 space-y-9">
    <div class="flex justify-center space-x-4">
        <button>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                 stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
            </svg>
        </button>

        <p>{{ now()->format('d.m.Y l') }}</p>
        <button>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                 stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
            </svg>
        </button>
    </div>

    <div x-data="{ modelOpen: false }" class="">
        <div class="flex items-end justify-center">
            @if($activePomodoro && $activePomodoro->status === 'in_progress')
                <p class="font-bold text-4xl text-red-500">{{ $activePomodoro->formatted_remaining_time }}</p>
            @elseif($activePomodoro && $activePomodoro->status === 'paused')
                <p class="font-bold text-4xl text-yellow-500">{{ $activePomodoro->formatted_remaining_time }}</p>
            @else
                <p class="font-bold text-4xl">{{ now()->format('H:i:s') }}</p>
            @endif
            <button @click="modelOpen =!modelOpen" class="ml-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                     class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </button>
        </div>

        <div x-show="modelOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 text-center md:items-center sm:block sm:p-0">
                <div x-cloak @click="modelOpen = false" x-show="modelOpen"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-40" aria-hidden="true"
                ></div>
                <div x-cloak x-show="modelOpen"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block w-[300px]  my-20 overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl"
                >
                    <div class="">
                        <div class="bg-purple-200 py-2 w-full text-center text-xl ">
                            Toplam Calisma Suresi
                        </div>
                        <div class="p-2">
                            <div class="bg-purple-800 rounded">
                                <p class="text-white text-center px-6 py-2 text-sm">
                                    <span>{{ $firstPomodoroDate ?? 'Başlangıç tarihi' }}</span> tarihinden itibaren bugune
                                    <br>
                                    <span class="text-amber-300 font-extrabold text-2xl">{{ $totalStudyTime ?? '0sa 0dk' }}</span>
                                    <br>
                                    Calistin!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="mt-14 mb-4">
        <div class="flex justify-between text-center px-3 text-sm">
            <div>
                <p>Bugunun Hedefi</p>
                <p class="text-xl">{{ $dailyTargetTime ?? '02:00:00' }}</p>
            </div>
            <div>
                <p>Tamamlanan</p>
                <p class="text-xl">{{ $todayCompletedTime ?? '00:00:00' }}</p>
            </div>
            <div>
                <p>Basari Orani</p>
                <p class="text-xl">{{ $todaySuccessRate ?? '0' }}%</p>
            </div>
        </div>
        <hr class="mt-2">
        <div>
            <div class="flex justify-between mt-2 text-sm mb-4">
                <div class="flex items-center">
                    <x-hugeicons-target-02 class="size-4 mr-2 text-purple-500"/>
                    <p>Bugunun Pomodoro Listesi</p>
                </div>
                <div>
                    <a href="/pomodoros" class="text-purple-600">Tum Pomodorolaer</a>
                </div>
            </div>

            @forelse($todayPomodoros ?? [] as $pomodoro)
                <div class="rounded-lg w-full h-16 py-1 mb-2
                @if($pomodoro->status === 'completed') bg-green-400
                @elseif($pomodoro->status === 'in_progress') bg-red-400
                @elseif($pomodoro->status === 'paused') bg-yellow-400
                @else bg-gray-400 @endif">
                    <div class="px-3 py-2 text-white flex items-center text-sm justify-between">
                        <div class="rounded-full w-10 h-10 bg-white flex items-center justify-center">
                            @if($pomodoro->type === 'work')
                                <span class="text-red-500 font-bold">W</span>
                            @elseif($pomodoro->type === 'short_break')
                                <span class="text-yellow-500 font-bold">S</span>
                            @else
                                <span class="text-green-500 font-bold">L</span>
                            @endif
                        </div>

                        <div class="justify-between flex items-center space-x-8">
                            <div class="space-y-1">
                                <p>{{ $pomodoro->project_name ?? 'Genel Çalışma' }}</p>
                                <p class="text-xs">{{ ucfirst($pomodoro->type) }} - {{ ucfirst($pomodoro->status) }}</p>
                            </div>
                            <div class="space-y-1">
                                @if($pomodoro->status === 'in_progress')
                                    <p>Kalan: {{ $pomodoro->formatted_remaining_time }}</p>
                                @elseif($pomodoro->status === 'completed')
                                    <p>Tamamlandı ✓</p>
                                @elseif($pomodoro->status === 'paused')
                                    <p>Duraklatıldı ⏸</p>
                                @endif
                                <p>{{ sprintf('%02d:%02d', floor($pomodoro->duration_seconds / 60), $pomodoro->duration_seconds % 60) }}</p>
                            </div>
                            <div
                                x-data="{
                                    open: false,
                                    toggle() {
                                        if (this.open) {
                                            return this.close()
                                        }
                                        this.$refs.button.focus()
                                        this.open = true
                                    },
                                    close(focusAfter) {
                                        if (! this.open) return
                                        this.open = false
                                        focusAfter && focusAfter.focus()
                                    }
                                }"
                                x-on:keydown.escape.prevent.stop="close($refs.button)"
                                x-on:focusin.window="! $refs.panel.contains($event.target) && close()"
                                x-id="['dropdown-button']"
                                class="relative"
                            >
                                <button
                                    x-ref="button"
                                    x-on:click="toggle()"
                                    :aria-expanded="open"
                                    :aria-controls="$id('dropdown-button')"
                                    type="button"
                                    class=""
                                >
                                    <x-fas-ellipsis-vertical class="size-5"/>
                                </button>

                                <div
                                    x-ref="panel" x-show="open" x-transition.origin.top.left
                                    x-on:click.outside="close($refs.button)" :id="$id('dropdown-button')" x-cloak
                                    class="absolute right-0 min-w-48 rounded-lg shadow-sm mt-2 z-50 origin-top-left bg-white p-1.5 outline-none border border-gray-200"
                                >
                                    @if($pomodoro->status === 'paused')
                                        <a href="/pomodoros/{{ $pomodoro->id }}/resume"
                                           class="px-2 lg:py-1.5 py-2 w-full flex items-center rounded-md transition-colors text-left text-gray-800 hover:bg-green-50 hover:text-green-600">
                                            ▶️ Devam Et
                                        </a>
                                    @elseif($pomodoro->status === 'in_progress')
                                        <a href="/pomodoros/{{ $pomodoro->id }}/pause"
                                           class="px-2 lg:py-1.5 py-2 w-full flex items-center rounded-md transition-colors text-left text-gray-800 hover:bg-yellow-50 hover:text-yellow-600">
                                            ⏸️ Duraklat
                                        </a>
                                    @endif

                                    @if($pomodoro->status !== 'completed')
                                        <a href="/pomodoros/{{ $pomodoro->id }}/complete"
                                           class="px-2 lg:py-1.5 py-2 w-full flex items-center rounded-md transition-colors text-left text-gray-800 hover:bg-green-50 hover:text-green-600">
                                            ✅ Tamamla
                                        </a>
                                    @endif

                                    <a href="/pomodoros/{{ $pomodoro->id }}/edit"
                                       class="px-2 lg:py-1.5 py-2 w-full flex items-center rounded-md transition-colors text-left text-gray-800 hover:bg-gray-50">
                                        ✏️ Duzenle
                                    </a>

                                    <a href="/pomodoros/{{ $pomodoro->id }}/delete"
                                       class="px-2 lg:py-1.5 py-2 w-full flex items-center rounded-md transition-colors text-left text-gray-800 hover:bg-red-50 hover:text-red-600">
                                        🗑️ Sil
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 py-4">
                    Bugün için pomodoro bulunmuyor.
                    <br>
                    <a href="/pomodoros/create" class="text-purple-600 underline">Yeni Pomodoro Başlat</a>
                </div>
            @endforelse

            <div class="rounded-full w-10 h-10 bg-purple-300 mt-3 text-white">
                <a href="/pomodoros/create" class="block">
                    <button class="ml-2 mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                    </button>
                </a>
            </div>
        </div>
    </div>
</div>
