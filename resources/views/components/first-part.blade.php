<div class="bg-white w-full h-[430px] text-black rounded-xl px-4 py-8 space-y-9">
    <div class="flex justify-center space-x-4">
        <button>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                 stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
            </svg>
        </button>

        <p>date</p>{{--19.08.2025 Sali--}}
        <button>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                 stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
            </svg>
        </button>
    </div>

    <div x-data="{ modelOpen: false }" class="">
        <div class="flex items-end justify-center">
            <p class="font-bold text-4xl">time</p>
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
                                    <span>date</span> tarihinden itibaren bugune
                                    <br>
                                    <span
                                        class="text-amber-300 font-extrabold text-2xl">time</span>{{--0sa 27dk 44sn--}}
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
                <p>Hedef Sure</p>
                <p class="text-xl">time</p>{{--                02:00:00--}}
            </div>
            <div>
                <p>Seviyesi</p>
                <p class="text-xl">seviye</p>{{--                a/b/c/d/e--}}
            </div>
            <div>
                <p>Basari Orani</p>
                <p class="text-xl">yuzdelik</p>{{--                %50/%60/%70--}}
            </div>
        </div>
        <hr class="mt-2">
        <div>
            <div class="flex justify-between mt-2 text-sm mb-4">
                <div class="flex items-center">
                    <x-hugeicons-target-02 class="size-4 mr-2 text-purple-500"/>
                    <p>Bugunun Heedf Listesi</p>
                </div>
                <div>
                    <a href="" class="text-purple-600">Hedeflerim</a>
                </div>
            </div>
            <div class="bg-red-400 rounded-lg w-full h-16 py-1">
                <div class="px-3 py-2 text-white flex items-center text-sm justify-between">
                    <div class="rounded-full w-10 h-10 bg-white">
                        <p>
                            <x-fas-a class="size-7 text-red-500 mt-1"/>
                        </p>
                    </div>

                    <div class="justify-between flex items-center space-x-8">
                        <div class="space-y-1">
                            <p>project_name</p>{{--                        Okume/yazma...--}}
                            <p>frequency</p>{{--                        haftada 1 / ayda1 /her gun--}}
                        </div>
                        <div class="space-y-1">
                            <p>yuzdelik</p>{{--    tamamlanma yuzdeligi                    %100--}}
                            <p>sure</p>{{--      harcanan sure            00:27:26--}}
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
                            <!-- Button -->
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

                            <!-- Panel -->
                            <div
                                x-ref="panel" x-show="open" x-transition.origin.top.left
                                x-on:click.outside="close($refs.button)" :id="$id('dropdown-button')" x-cloak
                                class="absolute right-0 min-w-48 rounded-lg shadow-sm mt-2 z-50 origin-top-left bg-white p-1.5 outline-none border border-gray-200"
                            >
                                <a href=""
                                   class="px-2 lg:py-1.5 py-2 w-full flex items-center rounded-md transition-colors text-left text-gray-800 hover:bg-gray-50 focus-visible:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Kaydi Duzenle
                                </a>

                                <a href=""
                                   class="px-2 lg:py-1.5 py-2 w-full flex items-center rounded-md transition-colors text-left text-gray-800 hover:bg-gray-50 focus-visible:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Hedef Duzenle
                                </a>

                                <a href=""
                                   class="px-2 lg:py-1.5 py-2 w-full flex items-center rounded-md transition-colors text-left text-gray-800 hover:bg-gray-50 focus-visible:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Yapilacaklara Ekle
                                </a>
                                <a href=""
                                   class="px-2 lg:py-1.5 py-2 w-full flex items-center rounded-md transition-colors text-left text-gray-800 hover:bg-gray-50 focus-visible:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Hedefi Tamamla
                                </a>
                                <a href=""
                                   class="px-2 lg:py-1.5 py-2 w-full flex items-center rounded-md transition-colors text-left text-gray-800 hover:bg-red-50 hover:text-red-600 focus-visible:bg-red-50 focus-visible:text-red-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Sil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-full w-10 h-10 bg-purple-300 mt-3 text-white">
                <button class="ml-2 mt-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
