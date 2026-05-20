<header class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-white/10 px-4 shadow-lg sm:gap-x-6 sm:px-6 lg:px-8" style="background-color: #B3006C;">
    <!-- Mobile menu button -->
    <button type="button" class="-m-2.5 p-2.5 text-white lg:hidden" @click="sidebarOpen = true">
        <span class="sr-only">Ouvrir le menu</span>
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    <!-- Separator -->
    <div class="h-6 w-px bg-white/20 lg:hidden" aria-hidden="true"></div>

    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
        <!-- Search -->
        <form class="relative flex flex-1" action="#" method="GET">
            <label for="search-field" class="sr-only">Rechercher</label>
            <svg class="pointer-events-none absolute inset-y-0 left-0 h-full w-5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input id="search-field" 
                   class="block h-full w-full border-0 py-0 pl-8 pr-0 text-white placeholder:text-white/60 focus:ring-0 sm:text-sm bg-transparent" 
                   placeholder="Rechercher Travaux..." 
                   type="search" 
                   name="q">
        </form>

        <div class="flex items-center gap-x-4 lg:gap-x-6">
            <!-- Notifications -->
            <div class="relative" x-data="notificationBell({{ $unreadNotificationsCount ?? 0 }})" x-init="init()">
                <button type="button"
                        @click.stop="open = !open; if (open) load()"
                        class="-m-2.5 p-2.5 text-white/80 hover:text-white relative">
                    <span class="sr-only">Notifications</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span x-show="unreadCount > 0"
                          x-text="unreadCount > 99 ? '99+' : unreadCount"
                          class="absolute -top-0.5 -right-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-[#e30613] px-1 text-[10px] font-bold text-white"></span>
                </button>

                <div x-cloak
                     x-show="open"
                     @click.outside.window="open = false"
                     x-transition
                     class="absolute right-0 z-50 mt-2 w-80 sm:w-96 origin-top-right rounded-xl bg-white py-2 shadow-lg ring-1 ring-gray-900/5">
                    <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-900">Notifications</p>
                        <a href="{{ route('notifications.index') }}" class="text-xs text-[#B3006C] hover:underline">Tout voir</a>
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        <template x-if="loading">
                            <p class="px-4 py-6 text-sm text-gray-500 text-center">Chargement…</p>
                        </template>
                        <template x-if="!loading && items.length === 0">
                            <p class="px-4 py-6 text-sm text-gray-500 text-center">Aucune notification</p>
                        </template>
                        <template x-for="item in items" :key="item.id">
                            <a :href="item.url || '{{ route('notifications.index') }}'"
                               class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-50 last:border-0"
                               :class="!item.is_read ? 'bg-blue-50/40' : ''"
                               @click="open = false">
                                <p class="text-sm font-medium text-gray-900" x-text="item.title"></p>
                                <p class="text-xs text-gray-600 mt-0.5 line-clamp-2" x-text="item.message"></p>
                            </a>
                        </template>
                    </div>
                </div>
            </div>

            <script>
            window.notificationBell = function (initialCount) {
                return {
                    open: false,
                    loading: false,
                    unreadCount: initialCount,
                    items: [],
                    init() {
                        setInterval(() => this.refreshCount(), 60000);
                    },
                    async refreshCount() {
                        try {
                            const r = await fetch(@json(route('notifications.count')));
                            const d = await r.json();
                            this.unreadCount = d.count ?? 0;
                        } catch (e) {}
                    },
                    async load() {
                        this.loading = true;
                        try {
                            const r = await fetch(@json(route('notifications.data')));
                            const d = await r.json();
                            this.items = d.notifications ?? [];
                            this.unreadCount = d.unread_count ?? 0;
                        } catch (e) {
                            this.items = [];
                        }
                        this.loading = false;
                    }
                };
            };
            </script>

            <!-- Separator -->
            <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-white/20" aria-hidden="true"></div>

            <!-- Profile dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button type="button"
                        class="-m-1.5 flex items-center p-1.5"
                        @click.stop="open = !open"
                        @keydown.escape.window="open = false">
                    <span class="sr-only">Menu utilisateur</span>
                    @if(auth()->user()->photo)
                        <img class="h-9 w-9 rounded-full object-cover shadow-md"
                             src="{{ asset(auth()->user()->photo) }}"
                             alt="{{ auth()->user()->full_name }}">
                    @else
                        <div class="h-9 w-9 rounded-full bg-white/20 flex items-center justify-center text-white text-sm font-bold shadow-md">
                            {{ substr(auth()->user()->prenom ?? 'U', 0, 1) }}{{ substr(auth()->user()->nom ?? '', 0, 1) }}
                        </div>
                    @endif
                    <span class="hidden lg:flex lg:items-center">
                        <span class="ml-4 text-sm font-semibold leading-6 text-white" aria-hidden="true">
                            {{ auth()->user()->prenom ?: auth()->user()->name }}
                        </span>
                        <svg class="ml-2 h-5 w-5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>
                </button>

                <div x-cloak
                     x-show="open"
                     x-transition.opacity.scale.100
                     @click.outside.window="open = false"
                     class="absolute right-0 z-50 mt-2.5 w-56 origin-top-right rounded-xl bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->full_name }}</p>
                        <p class="text-xs text-senelec-purple mt-1">{{ auth()->user()->matricule }}</p>
                        @if(auth()->user()->roles->count() > 0)
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach(auth()->user()->roles as $role)
                                    <span class="badge badge-purple text-xs">{{ ucfirst($role->name) }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Mon profil
                        </div>
                    </a>

                    <a href="{{ route('profile.signature') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 20h9M5 4h14a2 2 0 012 2v4a2 2 0 01-2 2H5l-3 3V6a2 2 0 012-2z"/>
                            </svg>
                            Ma signature
                        </div>
                    </a>

                    <div class="border-t border-gray-100 my-1"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Se déconnecter
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
