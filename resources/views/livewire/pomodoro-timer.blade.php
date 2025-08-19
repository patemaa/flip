<div x-data="pomodoroTimer" x-init="init()" class="max-w-4xl mx-auto">
    <!-- Timer Selection -->
    <div x-show="!sessionStarted" class="space-y-8">
        <!-- Preset Sessions -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-6 text-center">Choose Your Session</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <button
                    @click="selectSession('work', 25)"
                    class="group relative overflow-hidden bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 border-2 border-red-200 dark:border-red-700 rounded-xl p-6 hover:border-red-400 dark:hover:border-red-500 hover:shadow-lg transition-all duration-300 hover:scale-105"
                >
                    <div class="text-center relative z-10">
                        <div class="text-4xl mb-3">🍅</div>
                        <div class="font-bold text-lg text-red-800 dark:text-red-200 mb-1">Work Session</div>
                        <div class="text-sm text-red-600 dark:text-red-400 mb-2">25 minutes</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Deep focus time</div>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-r from-red-400/0 to-red-400/10 group-hover:from-red-400/10 group-hover:to-red-400/20 transition-all duration-300"></div>
                </button>

                <button
                    @click="selectSession('short_break', 5)"
                    class="group relative overflow-hidden bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-2 border-green-200 dark:border-green-700 rounded-xl p-6 hover:border-green-400 dark:hover:border-green-500 hover:shadow-lg transition-all duration-300 hover:scale-105"
                >
                    <div class="text-center relative z-10">
                        <div class="text-4xl mb-3">☕</div>
                        <div class="font-bold text-lg text-green-800 dark:text-green-200 mb-1">Short Break</div>
                        <div class="text-sm text-green-600 dark:text-green-400 mb-2">5 minutes</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Quick refresh</div>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-r from-green-400/0 to-green-400/10 group-hover:from-green-400/10 group-hover:to-green-400/20 transition-all duration-300"></div>
                </button>

                <button
                    @click="selectSession('long_break', 15)"
                    class="group relative overflow-hidden bg-gradient-to-br from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 border-2 border-purple-200 dark:border-purple-700 rounded-xl p-6 hover:border-purple-400 dark:hover:border-purple-500 hover:shadow-lg transition-all duration-300 hover:scale-105"
                >
                    <div class="text-center relative z-10">
                        <div class="text-4xl mb-3">🌅</div>
                        <div class="font-bold text-lg text-purple-800 dark:text-purple-200 mb-1">Long Break</div>
                        <div class="text-sm text-purple-600 dark:text-purple-400 mb-2">15 minutes</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Extended rest</div>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-400/0 to-purple-400/10 group-hover:from-purple-400/10 group-hover:to-purple-400/20 transition-all duration-300"></div>
                </button>
            </div>
        </div>

        <!-- Custom Session -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-6 text-center">Custom Session</h3>

            <div class="max-w-2xl mx-auto space-y-6">
                <!-- Duration Slider -->
                <div class="space-y-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration: <span x-text="customMinutes"></span> minutes</label>
                    <div class="relative">
                        <input
                            x-model="customMinutes"
                            type="range"
                            min="0"
                            max="120"
                            step="1"
                            class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer slider"
                        >
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <span>0 min</span>
                            <span>30 min</span>
                            <span>60 min</span>
                            <span>90 min</span>
                            <span>120 min</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Duration Buttons -->
                <div class="flex flex-wrap gap-2 justify-center">
                    <template x-for="duration in [10, 15, 30, 45, 60, 90]">
                        <button
                            @click="customMinutes = duration"
                            :class="customMinutes == duration ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                            class="px-3 py-1 rounded-full text-sm font-medium transition-colors"
                            x-text="duration + ' min'"
                        ></button>
                    </template>
                </div>

                <!-- Project Name -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project/Task (optional)</label>
                    <div class="relative">
                        <input
                            x-model="projectName"
                            type="text"
                            placeholder="e.g., Website Development, Study Session, Writing..."
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Priority Level -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority Level</label>
                    <div class="grid grid-cols-4 gap-2">
                        <button
                            @click="priority = 'low', console.log(priority)"
                            class="px-3 py-2 border rounded-lg text-sm font-medium transition-colors dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                            :class="priority === 'low' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-gray-50 border-gray-300 text-gray-700'"
                        >
                            🟢 Low
                        </button>
                        <button
                            @click="priority = 'medium', console.log(priority)"
                            :class="priority === 'medium' ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-gray-50 border-gray-300 text-gray-700'"
                            class="px-3 py-2 border rounded-lg text-sm font-medium transition-colors dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                        >
                            🔵 Medium
                        </button>
                        <button
                            @click="priority = 'high'"
                            :class="priority === 'high' ? 'bg-orange-100 border-orange-500 text-orange-700' : 'bg-gray-50 border-gray-300 text-gray-700'"
                            class="px-3 py-2 border rounded-lg text-sm font-medium transition-colors dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                        >
                            🟠 High
                        </button>
                        <button
                            @click="priority = 'urgent'"
                            :class="priority === 'urgent' ? 'bg-red-100 border-red-500 text-red-700' : 'bg-gray-50 border-gray-300 text-gray-700'"
                            class="px-3 py-2 border rounded-lg text-sm font-medium transition-colors dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                        >
                            🔴 Urgent
                        </button>
                    </div>
                </div>

                <!-- Start Custom Button -->
                <button
                    @click="selectSession('custom', customMinutes)"
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-4 px-8 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl"
                >
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>Start Custom Session</span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Timer Display -->
    <div x-show="sessionStarted" class="min-h-screen items-center justify-center mx-auto py-6">
        <!-- Background Glow Effect -->

        <div class="relative z-10 text-center space-y-8 max-w-4xl mx-auto">
            <!-- Session Info -->
            <div class="space-y-4">
                <div class="inline-flex items-center space-x-2 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-full px-6 py-3 shadow-lg">
                    <span class="text-2xl" x-text="getSessionIcon()"></span>
                    <span class="text-lg font-semibold text-gray-700 dark:text-gray-200" x-text="getSessionTypeLabel()"></span>
                </div>

                <div x-show="projectName" class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl px-6 py-3 shadow-lg">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Working on</div>
                    <div class="text-lg font-medium text-gray-800 dark:text-gray-200" x-text="projectName"></div>
                </div>
            </div>

            <!-- Timer Circle -->
            <div class="relative flex justify-center items-center w-full">
                <svg class="transform -rotate-90 w-full h-full" viewBox="0 0 100 100">
                    <!-- Background circle -->
                    <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="4" class="text-gray-200 dark:text-gray-700"></circle>
                    <!-- Progress circle -->
                    <circle
                        cx="50"
                        cy="50"
                        r="45"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="5"
                        stroke-linecap="round"
                        :stroke-dasharray="282.7"
                        :stroke-dashoffset="282.7 * (1 - (totalSeconds - seconds) / totalSeconds)"
                        :class="getTimerColor()"
                        class="transition-all duration-1000 ease-in-out"
                    ></circle>
                </svg>

                <!-- Timer Text - Absolute positioning -->
                <div class="absolute  flex flex-col items-center justify-center w-[400] h-[400]">
                    <div class="text-6xl font-mono font-bold text-gray-800 dark:text-gray-200" x-show="!isFinished" x-text="formatTime(seconds)"></div>
                    <div class="text-4xl font-bold text-green-600 animate-pulse" x-show="isFinished">
                        <div>🎉</div>
                        <div class="text-2xl mt-2">Complete!</div>
                    </div>
                    <div class="text-sm text-gray-400 dark:text-gray-500 mt-2" x-show="!isFinished">
                        <span x-text="Math.round((totalSeconds - seconds) / totalSeconds * 100)"></span>% complete
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="flex flex-wrap items-center justify-center gap-4">
                <button
                    x-show="!isFinished"
                    @click="isPaused ? resumeTimer() : pauseTimer()"
                    :class="isPaused ? 'bg-green-600 hover:bg-green-700' : 'bg-orange-600 hover:bg-orange-700'"
                    class="flex items-center space-x-2 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg"
                >
                    <svg x-show="isPaused" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                    </svg>
                    <svg x-show="!isPaused" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span x-text="isPaused ? 'Resume' : 'Pause'"></span>
                </button>

                <button
                    x-show="!isFinished"
                    @click="stopTimer()"
                    class="flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg"
                >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Stop</span>
                </button>


            </div>

            <!-- Completion Actions -->
            <div x-show="isFinished" class="flex flex-row gap-4 justify-center">
                <button
                    @click="completeSession()"
                    class="w-1/2 flex items-center justify-center space-x-2 bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-4 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg"
                >
                    <span class="text-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-7">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                    <span>Mark Complete</span>
                </button>
                <button
                    @click="resetSession()"
                    class="w-1/2 flex items-center justify-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-4 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg"
                >
                    <span class="text-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-7">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </span>
                    <span>New Session</span>
                </button>
            </div>
        </div>
    </div>
</div>

