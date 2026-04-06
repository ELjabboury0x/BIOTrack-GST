@php
    $authUser = auth()->user();
    $displayName = $authUser?->name ?: $authUser?->login ?: 'Utilisateur';
    $displayRole = match ($authUser?->role) {
        'admin' => 'Administrateur',
        'manager' => 'Manager',
        'operator' => 'Operator',
        'technician' => 'Technician',
        'major' => 'Major',
        'technicien' => 'Technicien',
        'ingenieur' => 'Ingénieur',
        default => 'Utilisateur',
    };
    $avatarLetter = strtoupper(mb_substr($displayName, 0, 1));
@endphp

<style>
@keyframes gstBellSwing {
    0%, 100% { transform: rotate(0deg); }
    20% { transform: rotate(12deg); }
    40% { transform: rotate(-10deg); }
    60% { transform: rotate(6deg); }
    80% { transform: rotate(-4deg); }
}

@keyframes gstBellPulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.2); }
    50% { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0); }
}

@keyframes gstBellAura {
    0%, 100% { opacity: 0.35; transform: scale(1); }
    50% { opacity: 0.7; transform: scale(1.08); }
}

@keyframes gstBellSpark {
    0%, 100% { opacity: 0; transform: translateY(0) scale(0.8); }
    50% { opacity: 1; transform: translateY(-2px) scale(1); }
}

@keyframes gstBellIdleBreath {
    0%, 100% { transform: scale(1); box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22); }
    50% { transform: scale(1.04); box-shadow: 0 12px 24px rgba(59, 130, 246, 0.32); }
}

.gst-bell-active {
    animation: gstBellSwing 1.4s ease-in-out infinite;
    transform-origin: top center;
}

.gst-bell-btn.gst-bell-has-alert {
    animation: gstBellPulse 1.8s ease-out infinite;
}

.gst-bell-btn {
    overflow: visible;
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    border: 1px solid rgba(59, 130, 246, 0.35);
    color: #1d4ed8;
    box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22);
}

.gst-bell-btn.gst-bell-idle {
    animation: gstBellIdleBreath 2.2s ease-in-out infinite;
}

.gst-bell-btn .gst-bell-aura {
    position: absolute;
    inset: -25%;
    background: radial-gradient(circle, rgba(59,130,246,0.35) 0%, rgba(99,102,241,0.18) 45%, rgba(255,255,255,0) 75%);
    opacity: 0;
    pointer-events: none;
}

.gst-bell-btn.gst-bell-has-alert .gst-bell-aura {
    opacity: 1;
    animation: gstBellAura 1.8s ease-in-out infinite;
}

.gst-bell-btn .gst-bell-spark {
    position: absolute;
    top: 4px;
    right: 6px;
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 0 8px rgba(255,255,255,0.9);
    opacity: 0;
    pointer-events: none;
}

.gst-bell-btn.gst-bell-has-alert .gst-bell-spark {
    animation: gstBellSpark 1.6s ease-in-out infinite;
}

.gst-bell-idle-icon {
    color: #1d4ed8;
}

.gst-notif-badge {
    min-width: 20px;
    height: 20px;
    line-height: 20px;
    font-size: 11px;
    border: 2px solid #ffffff;
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.25);
    z-index: 3;
}
</style>

<!-- Top Navbar -->
<header class="gst-navbar bg-white/80 backdrop-blur-md shadow-sm border-b border-gray-200/60 sticky top-0 z-20">
    <div class="px-3 sm:px-6 lg:px-8 py-3 sm:py-4 flex items-center justify-between gap-2 sm:gap-3">
        <div class="flex items-center gap-2 sm:gap-3">
            <button @click="mobileSidebarOpen = true" class="lg:hidden w-9 h-9 sm:w-10 sm:h-10 rounded-xl border border-gray-200 text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-200 shrink-0">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div class="ml-auto flex items-center gap-2 sm:gap-3">
            <button type="button"
                    onclick="if (window.GSTDarkMode) { GSTDarkMode.toggle(); }"
                    class="gst-dark-toggle gst-dark-toggle-switch transition-all duration-200"
                    title="Mode sombre">
                <i class="fas fa-moon dark-icon"></i>
                <i class="fas fa-sun light-icon"></i>
            </button>

            <!-- Notifications -->
            @php
                $role = $authUser?->role;
                $canSeeComplaintNotifications = in_array($role, ['admin', 'ingenieur', 'technicien', 'technician'], true);
            @endphp
            @if($canSeeComplaintNotifications)
            <div x-data="complaintNotifications()" x-init="init()" class="relative">
                <button @click="open = !open" :class="count > 0 ? 'gst-bell-has-alert' : 'gst-bell-idle'" class="gst-bell-btn relative w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-105">
                    <span class="gst-bell-aura"></span>
                    <i class="fas fa-bell" :class="count > 0 ? 'gst-bell-active text-blue-600' : 'gst-bell-idle-icon'" style="position: relative; z-index: 1;"></i>
                    <span class="gst-bell-spark"></span>
                    <span class="gst-notif-badge absolute -top-1.5 -right-1.5 px-1 text-center rounded-full font-bold"
                          :class="count > 0 ? 'bg-red-500 text-white' : 'bg-white text-blue-600 border-blue-200'"
                          x-text="count"></span>
                </button>

                <div x-show="open"
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="absolute right-0 mt-2 w-80 max-w-[90vw] bg-white rounded-2xl shadow-2xl p-4 z-50 border border-gray-100 ring-1 ring-black/5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="font-bold text-sm text-gray-800 flex items-center gap-2">
                            <i class="fas fa-bell text-blue-500 text-xs"></i>
                            Notifications & rappels
                        </p>
                        <button @click="markAllAsRead()" class="text-xs text-blue-600 hover:text-blue-700 font-medium hover:underline" x-show="count > 0">Tout marquer lu</button>
                    </div>

                    <div class="space-y-2 max-h-72 overflow-y-auto gst-scrollbar" x-show="items.length > 0">
                        <template x-for="item in items" :key="item.id">
                            <div class="p-3 rounded-xl border-l-4 hover:shadow-md transition-shadow duration-200"
                                 :class="item.type === 'planning_reminder' ? 'bg-gradient-to-r from-amber-50 to-yellow-50 border-amber-500' : 'bg-gradient-to-r from-red-50 to-orange-50 border-red-500'">
                                <div class="flex items-start gap-3">
                                    <template x-if="item.attachment_image_url">
                                        <a :href="item.open_url" class="block w-12 h-12 rounded-lg overflow-hidden border border-gray-200 bg-white flex-shrink-0">
                                            <img :src="item.attachment_image_url" alt="Pièce jointe réclamation" class="w-full h-full object-cover" loading="lazy">
                                        </a>
                                    </template>
                                    <template x-if="!item.attachment_image_url">
                                        <div class="w-12 h-12 rounded-lg border border-gray-200 bg-white flex items-center justify-center flex-shrink-0 text-amber-600">
                                            <i class="fas" :class="item.type === 'planning_reminder' ? 'fa-calendar-check' : 'fa-bell'"></i>
                                        </div>
                                    </template>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-sm text-gray-800" x-text="item.title || item.service_name"></p>
                                        <p class="text-xs text-gray-700 mt-0.5" x-text="item.equipment_label"></p>
                                        <div class="mt-1.5 flex items-center gap-2">
                                            <span class="text-[11px] px-2 py-0.5 rounded-full font-semibold"
                                                  :class="statusBadgeClass(item.status)"
                                                  x-text="statusLabel(item.status)"></span>
                                            <span class="text-xs text-gray-500" x-text="'Priorité: ' + priorityLabel(item.priority) + ' • Réf: ' + (item.service_name || '-')"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <p class="text-xs text-gray-400" x-text="item.created_at"></p>
                                    <a :href="item.open_url" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline">Ouvrir &rarr;</a>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="text-center text-xs text-gray-400 mt-4" x-show="items.length === 0">
                        Aucune notification.
                    </div>
                </div>
            </div>
            @endif

            <!-- User Profile Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center space-x-2 hover:bg-gray-100 px-2 sm:px-3 py-1.5 sm:py-2 rounded-xl transition-all duration-200">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold ring-2 ring-blue-100 shadow-md">
                        {{ $avatarLetter }}
                    </div>
                    <div class="text-left hidden sm:block">
                        <p class="text-sm font-semibold text-gray-800">{{ $displayName }}</p>
                        <p class="text-xs text-gray-500">{{ $displayRole }}</p>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 text-[10px] hidden sm:inline transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" 
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-2xl overflow-hidden z-50 border border-gray-100 ring-1 ring-black/5">
                    <div class="p-3 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
                        <p class="text-xs text-gray-500">Connecté en tant que</p>
                        <p class="text-sm font-bold text-gray-800">{{ $displayName }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 text-sm text-gray-700 transition-colors">
                        <i class="fas fa-user text-blue-500 w-4"></i> Mon Profil
                    </a>
                    <a href="{{ route('parametres') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 border-b border-gray-100 text-sm text-gray-700 transition-colors">
                        <i class="fas fa-cog text-gray-400 w-4"></i> Paramètres
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 hover:bg-red-50 text-sm text-red-600 font-semibold transition-colors">
                            <i class="fas fa-sign-out-alt w-4"></i> Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
function complaintNotifications() {
    return {
        open: false,
        count: 0,
        items: [],
        poller: null,
        wsUrl: '{{ (request()->isSecure() ? 'wss' : 'ws') . '://' . request()->getHost() . ':' . (int) env('REALTIME_PORT', 6001) . '/ws' }}',
        socket: null,
        reconnectDelay: 3000,

        async load() {
            try {
                const response = await fetch('{{ route('dashboard.notifications.complaints') }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                this.count = payload.count || 0;
                this.items = payload.items || [];
            } catch (e) {
            }
        },

        async markAllAsRead() {
            try {
                await fetch('{{ route('dashboard.notifications.complaints.read-all') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
            } catch (e) {
            }

            this.count = 0;
            this.items = [];
            this.open = false;
        },

        init() {
            this.load();
            this.poller = setInterval(() => this.load(), 30000);
            this.startRealtime();
        },

        startRealtime() {
            if (!this.wsUrl) {
                return;
            }

            try {
                this.socket = new WebSocket(this.wsUrl);

                this.socket.onmessage = (event) => {
                    try {
                        const message = JSON.parse(event.data || '{}');
                        if (message.channel === 'complaint.created') {
                            this.load();
                        }
                    } catch (e) {
                    }
                };

                this.socket.onclose = () => {
                    setTimeout(() => this.startRealtime(), this.reconnectDelay);
                };

                this.socket.onerror = () => {
                    try {
                        this.socket.close();
                    } catch (e) {
                    }
                };
            } catch (e) {
            }
        },

        statusLabel(status) {
            const normalized = (status || '').toLowerCase();

            if (normalized === 'urgent') return 'Planning proche';
            if (normalized === 'scheduled') return 'Planifié';
            if (normalized === 'resolved') return 'Clôturée';
            if (normalized === 'in_progress') return 'En cours';
            return 'Ouverte';
        },

        statusBadgeClass(status) {
            const normalized = (status || '').toLowerCase();

            if (normalized === 'urgent') return 'bg-amber-100 text-amber-700';
            if (normalized === 'scheduled') return 'bg-blue-100 text-blue-700';
            if (normalized === 'resolved') return 'bg-green-100 text-green-700';
            if (normalized === 'in_progress') return 'bg-amber-100 text-amber-700';
            return 'bg-red-100 text-red-700';
        },

        priorityLabel(priority) {
            const normalized = (priority || '').toLowerCase();

            if (normalized === 'urgent' || normalized === 'high') {
                return 'Urgente';
            }

            return 'Normale';
        },
    };
}
</script>
