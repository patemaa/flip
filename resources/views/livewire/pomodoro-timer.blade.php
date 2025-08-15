<div x-data="countdown">
    <!-- Placeholder -->
    <div
        class="flex flex-col items-center justify-center gap-6 rounded-lg border-2 border-dashed border-zinc-200/75 bg-zinc-50 px-4 py-40 text-sm font-medium dark:border-zinc-700 dark:bg-zinc-950/25"
    >
        <div class="text-5xl font-bold text-zinc-700 dark:text-zinc-300">
            <span x-cloak x-show="!isFinished" x-text="formatTime(seconds)"></span>
            <span x-cloak x-show="isFinished">Done!</span>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-2">
            <button
                x-show="!isFinished"
                @click="isPaused ? resumeTimer() : pauseTimer()"
                type="button"
                class="inline-flex min-w-28 items-center justify-center gap-2 rounded-lg border border-zinc-800 bg-zinc-800 px-3 py-2 text-sm font-medium leading-5 text-white hover:border-zinc-900 hover:bg-zinc-900 hover:text-white focus:outline-hidden focus:ring-2 focus:ring-zinc-500/50 active:border-zinc-700 active:bg-zinc-700 dark:border-zinc-700/50 dark:bg-zinc-700/50 dark:ring-zinc-700/50 dark:hover:border-zinc-700 dark:hover:bg-zinc-700/75 dark:active:border-zinc-700/50 dark:active:bg-zinc-700/50"
            >
                <svg
                    x-show="isPaused"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    class="hi-mini hi-play-circle inline-block size-5 text-emerald-400"
                >
                    <path
                        fill-rule="evenodd"
                        d="M2 10a8 8 0 1 1 16 0 8 8 0 0 1-16 0Zm6.39-2.908a.75.75 0 0 1 .766.027l3.5 2.25a.75.75 0 0 1 0 1.262l-3.5 2.25A.75.75 0 0 1 8 12.25v-4.5a.75.75 0 0 1 .39-.658Z"
                        clip-rule="evenodd"
                    />
                </svg>
                <svg
                    x-show="!isPaused"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    class="hi-mini hi-pause-circle inline-block size-5 text-orange-400"
                >
                    <path
                        fill-rule="evenodd"
                        d="M2 10a8 8 0 1 1 16 0 8 8 0 0 1-16 0Zm5-2.25A.75.75 0 0 1 7.75 7h.5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-.75.75h-.5a.75.75 0 0 1-.75-.75v-4.5Zm4 0a.75.75 0 0 1 .75-.75h.5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-.75.75h-.5a.75.75 0 0 1-.75-.75v-4.5Z"
                        clip-rule="evenodd"
                    />
                </svg>
                <span x-text="isPaused ? 'Resume' : 'Pause'"></span>
            </button>
            <button
                x-show="!isFinished"
                @click="stopTimer()"
                type="button"
                class="inline-flex min-w-28 items-center justify-center gap-2 rounded-lg border border-zinc-800 bg-zinc-800 px-3 py-2 text-sm font-medium leading-5 text-white hover:border-zinc-900 hover:bg-zinc-900 hover:text-white focus:outline-hidden focus:ring-2 focus:ring-zinc-500/50 active:border-zinc-700 active:bg-zinc-700 dark:border-zinc-700/50 dark:bg-zinc-700/50 dark:ring-zinc-700/50 dark:hover:border-zinc-700 dark:hover:bg-zinc-700/75 dark:active:border-zinc-700/50 dark:active:bg-zinc-700/50"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    class="hi-mini hi-stop-circle inline-block size-5 text-rose-400"
                >
                    <path
                        fill-rule="evenodd"
                        d="M2 10a8 8 0 1 1 16 0 8 8 0 0 1-16 0Zm5-2.25A.75.75 0 0 1 7.75 7h4.5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75v-4.5Z"
                        clip-rule="evenodd"
                    />
                </svg>
                <span>Stop</span>
            </button>
            <button
                @click="restartTimer()"
                type="button"
                class="inline-flex min-w-28 items-center justify-center gap-2 rounded-lg border border-zinc-800 bg-zinc-800 px-3 py-2 text-sm font-medium leading-5 text-white hover:border-zinc-900 hover:bg-zinc-900 hover:text-white focus:outline-hidden focus:ring-2 focus:ring-zinc-500/50 active:border-zinc-700 active:bg-zinc-700 dark:border-zinc-700/50 dark:bg-zinc-700/50 dark:ring-zinc-700/50 dark:hover:border-zinc-700 dark:hover:bg-zinc-700/75 dark:active:border-zinc-700/50 dark:active:bg-zinc-700/50"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    class="hi-mini hi-arrow-path inline-block size-5 text-zinc-400"
                >
                    <path
                        fill-rule="evenodd"
                        d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H3.989a.75.75 0 0 0-.75.75v4.242a.75.75 0 0 0 1.5 0v-2.43l.31.31a7 7 0 0 0 11.712-3.138.75.75 0 0 0-1.449-.39Zm1.23-3.723a.75.75 0 0 0 .219-.53V2.929a.75.75 0 0 0-1.5 0V5.36l-.31-.31A7 7 0 0 0 3.239 8.188a.75.75 0 1 0 1.448.389A5.5 5.5 0 0 1 13.89 6.11l.311.31h-2.432a.75.75 0 0 0 0 1.5h4.243a.75.75 0 0 0 .53-.219Z"
                        clip-rule="evenodd"
                    />
                </svg>
                <span>Restart</span>
            </button>
        </div>
    </div>
    <!-- END Placeholder -->
</div>
<!-- End Countdown: Feedback on finish -->
