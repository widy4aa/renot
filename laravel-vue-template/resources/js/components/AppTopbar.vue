<template>
    <header class="h-14 shrink-0 flex items-center justify-between px-5 bg-white z-20"
        style="border-radius:16px; box-shadow:0 1px 4px rgba(0,0,0,0.08);">

        <!-- Kiri: Logo + nama aplikasi -->
        <div class="flex items-center gap-2.5 shrink-0">
            <img src="/icon.png" alt="ReNot" class="h-8 w-8 object-contain" />
            <div class="leading-none">
                <p class="text-[10px] font-semibold tracking-widest uppercase" style="color:#006CB8;">Pertamina</p>
                <p class="text-sm font-bold" style="color:#111827;">ReNot</p>
            </div>
        </div>

        <!-- Kanan: actions -->
        <div class="flex items-center gap-1 shrink-0">

            <!-- ── Bell Notifikasi ─────────────────────── -->
            <div v-if="notifRoute" class="relative" ref="notifRef">
                <button
                    @click="toggleNotif"
                    class="relative flex items-center justify-center w-8 h-8 rounded-lg transition-colors duration-150 notif-btn"
                    title="Notifikasi"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#6B7280;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <!-- Badge merah -->
                    <span
                        v-if="localUnread > 0"
                        class="absolute top-1 right-1 w-2 h-2 rounded-full"
                        style="background:#ED1B2F;"
                    ></span>
                </button>

                <!-- Notif Dropdown -->
                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="opacity-0 translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-1"
                >
                    <div
                        v-if="notifOpen"
                        class="notif-panel absolute right-0 top-full mt-2 w-80 rounded-xl shadow-lg z-50 bg-white"
                        style="border:1px solid #E5E7EB;"
                    >
                        <!-- Header panel -->
                        <div class="flex items-center justify-between px-4 py-3" style="border-bottom:1px solid #F3F4F6;">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold" style="color:#111827;">Notifikasi</span>
                                <span
                                    v-if="localUnread > 0"
                                    class="text-[10px] font-bold px-1.5 py-0.5 rounded-full text-white"
                                    style="background:#ED1B2F;"
                                >
                                    {{ localUnread }}
                                </span>
                            </div>
                            <button
                                v-if="localUnread > 0"
                                @click="markAllRead"
                                :disabled="markingAll"
                                class="text-xs font-medium transition-colors"
                                style="color:#006CB8;"
                            >
                                {{ markingAll ? '...' : 'Baca semua' }}
                            </button>
                        </div>

                        <!-- Loading -->
                        <div v-if="notifLoading" class="px-4 py-6 text-center">
                            <svg class="w-5 h-5 animate-spin mx-auto" fill="none" viewBox="0 0 24 24" style="color:#9CA3AF;">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>

                        <!-- Kosong -->
                        <div v-else-if="notifList.length === 0" class="px-4 py-8 text-center">
                            <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#D1D5DB;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <p class="text-sm" style="color:#9CA3AF;">Belum ada notifikasi</p>
                        </div>

                        <!-- List -->
                        <ul v-else class="divide-y" style="--tw-divide-opacity:1; divide-color:#F9FAFB;">
                            <li
                                v-for="notif in notifList"
                                :key="notif.id"
                                @click="handleNotifClick(notif)"
                                class="flex gap-3 px-4 py-3 cursor-pointer transition-colors duration-150 notif-item"
                                :class="!notif.is_read ? 'notif-unread' : ''"
                            >
                                <!-- Dot unread -->
                                <span class="shrink-0 mt-1.5">
                                    <span
                                        class="block w-2 h-2 rounded-full"
                                        :style="notif.is_read ? 'background:#E5E7EB;' : 'background:#006CB8;'"
                                    ></span>
                                </span>

                                <!-- Konten -->
                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-sm leading-snug truncate"
                                        :style="notif.is_read ? 'color:#6B7280;' : 'color:#111827; font-weight:500;'"
                                    >
                                        {{ notif.title }}
                                    </p>
                                    <p class="text-xs mt-0.5 line-clamp-2 leading-relaxed" style="color:#9CA3AF;">
                                        {{ notif.body }}
                                    </p>
                                    <p class="text-[10px] mt-1" style="color:#9CA3AF;">
                                        {{ timeAgo(notif.created_at) }}
                                    </p>
                                </div>

                                <!-- Type badge -->
                                <span
                                    class="shrink-0 self-start mt-1 text-[10px] font-medium px-1.5 py-0.5 rounded-full"
                                    :style="typeBadge(notif.type)"
                                >
                                    {{ typeLabel(notif.type) }}
                                </span>
                            </li>
                        </ul>

                        <!-- Footer: Lihat Semua -->
                        <div class="px-4 py-3" style="border-top:1px solid #F3F4F6;">
                            <RouterLink
                                :to="{ name: notifRoute }"
                                class="flex items-center justify-center gap-1.5 w-full text-sm font-medium transition-colors"
                                style="color:#006CB8;"
                                @click="notifOpen = false"
                            >
                                Lihat Semua Notifikasi
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </RouterLink>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- Divider -->
            <div class="w-px h-5 mx-2" style="background:#E5E7EB;"></div>

            <!-- ── User Dropdown ───────────────────────── -->
            <div class="relative" ref="userRef">
                <button
                    @click="userOpen = !userOpen"
                    class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-lg transition-colors duration-150 user-btn"
                >
                    <img
                        v-if="user?.avatar"
                        :src="user.avatar"
                        alt="Avatar"
                        class="w-7 h-7 rounded-full object-cover shrink-0"
                        style="border:1.5px solid #E5E7EB;"
                    />
                    <div
                        v-else
                        class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                        style="background:#006CB8;"
                    >
                        {{ user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                    </div>
                    <span class="text-sm font-medium hidden sm:block max-w-[120px] truncate" style="color:#374151;">
                        {{ user?.name ?? '—' }}
                    </span>
                    <svg
                        class="w-3.5 h-3.5 shrink-0 transition-transform duration-150"
                        :class="userOpen ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        style="color:#9CA3AF;"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- User Dropdown -->
                <Transition
                    enter-active-class="transition duration-100 ease-out"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition duration-75 ease-in"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div
                        v-if="userOpen"
                        class="absolute right-0 top-full mt-2 w-52 rounded-xl shadow-md py-1 z-50 bg-white"
                        style="border:1px solid #E5E7EB; transform-origin: top right;"
                    >
                        <!-- Info -->
                        <div class="px-4 py-3" style="border-bottom:1px solid #F3F4F6;">
                            <p class="text-xs font-semibold truncate" style="color:#111827;">{{ user?.name }}</p>
                            <p class="text-xs truncate mt-0.5" style="color:#9CA3AF;">{{ user?.email }}</p>
                            <span
                                class="inline-block mt-1.5 text-[10px] font-semibold px-2 py-0.5 rounded-full"
                                :style="user?.role === 'admin'
                                    ? 'background:#EFF6FF; color:#006CB8;'
                                    : 'background:#F0FDF4; color:#16A34A;'"
                            >
                                {{ user?.role === 'admin' ? 'Admin' : 'Pegawai' }}
                            </span>
                        </div>

                        <div class="py-1">
                            <RouterLink
                                v-if="profileRoute"
                                :to="{ name: profileRoute }"
                                class="dropdown-item"
                                @click="userOpen = false"
                            >
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#9CA3AF;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Profil Saya
                            </RouterLink>
                        </div>

                        <div class="py-1" style="border-top:1px solid #F3F4F6;">
                            <button @click="logout" class="dropdown-item dropdown-danger w-full text-left">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Keluar
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const props = defineProps({
    user: { type: Object, default: null },
    unreadCount: { type: Number, default: null },
    notifRoute: { type: String, default: null },
    profileRoute: { type: String, default: null },
});

const emit = defineEmits(['logout', 'notif-read']);

const route  = useRoute();
const router = useRouter();

// ── State ─────────────────────────────────────────────────
const notifOpen   = ref(false);
const userOpen    = ref(false);
const notifList   = ref([]);
const notifLoading = ref(false);
const markingAll  = ref(false);
const localUnread = ref(props.unreadCount ?? 0);

const notifRef = ref(null);
const userRef  = ref(null);

const pageTitle = computed(() => route.meta?.title ?? '');

watch(() => props.unreadCount, val => { if (val !== null) localUnread.value = val; });

// ── Notif functions ───────────────────────────────────────
async function fetchNotif() {
    if (!props.notifRoute) return;
    notifLoading.value = true;
    try {
        const { data } = await axios.get('/api/pegawai/notifications');
        notifList.value  = data.data.slice(0, 5);
        localUnread.value = data.unread_count;
    } catch {
        notifList.value = [];
    } finally {
        notifLoading.value = false;
    }
}

function toggleNotif() {
    notifOpen.value = !notifOpen.value;
    userOpen.value  = false;
    if (notifOpen.value) fetchNotif();
}

async function handleNotifClick(notif) {
    // Mark as read
    if (!notif.is_read) {
        try {
            await axios.post(`/api/pegawai/notifications/${notif.id}/read`);
            notif.is_read = true;
            localUnread.value = Math.max(0, localUnread.value - 1);
            emit('notif-read', localUnread.value);
        } catch { /* */ }
    }
    // Redirect jika ada document_id
    if (notif.document_id) {
        notifOpen.value = false;
        router.push({ name: 'pegawai.dokumen.detail', params: { id: notif.document_id } });
    }
}

async function markAllRead() {
    markingAll.value = true;
    try {
        await axios.post('/api/pegawai/notifications/read-all');
        notifList.value.forEach(n => { n.is_read = true; });
        localUnread.value = 0;
        emit('notif-read', 0);
    } catch { /* */ } finally {
        markingAll.value = false;
    }
}

// ── User dropdown ─────────────────────────────────────────
function logout() {
    userOpen.value = false;
    emit('logout');
}

// ── Click outside ─────────────────────────────────────────
function onClickOutside(e) {
    if (notifRef.value && !notifRef.value.contains(e.target)) notifOpen.value = false;
    if (userRef.value && !userRef.value.contains(e.target)) userOpen.value = false;
}
onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));

// ── Helpers ───────────────────────────────────────────────
function timeAgo(iso) {
    if (!iso) return '';
    const diff = Math.floor((Date.now() - new Date(iso)) / 1000);
    if (diff < 60)   return `${diff}d lalu`;
    if (diff < 3600) return `${Math.floor(diff / 60)}m lalu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}j lalu`;
    const d = Math.floor(diff / 86400);
    return d === 1 ? 'Kemarin' : `${d} hari lalu`;
}

const typeMap = {
    reminder_akan_expired: { label: 'Reminder', bg: '#FEF3C7', color: '#92400E' },
    dokumen_expired:       { label: 'Expired',  bg: '#FEE2E2', color: '#991B1B' },
    dokumen_pending:       { label: 'Pending',  bg: '#F3F4F6', color: '#6B7280' },
    dokumen_approved:      { label: 'Disetujui',bg: '#D1FAE5', color: '#065F46' },
    dokumen_ditolak:       { label: 'Ditolak',  bg: '#FCE7F3', color: '#9D174D' },
};

function typeLabel(type) { return typeMap[type]?.label ?? 'Info'; }
function typeBadge(type) {
    const t = typeMap[type] ?? { bg: '#EFF6FF', color: '#1D4ED8' };
    return `background:${t.bg}; color:${t.color};`;
}
</script>

<style scoped>
.notif-btn:hover { background: #F9FAFB; border-radius: 8px; }
.user-btn:hover  { background: #F9FAFB; }

.notif-item { border-bottom: 1px solid #F9FAFB; }
.notif-item:hover { background: #F9FAFB; }
.notif-item:last-child { border-bottom: none; }
.notif-unread { background: #FAFBFF; }

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 16px;
    font-size: 14px;
    color: #374151;
    transition: background 150ms;
    text-decoration: none;
}
.dropdown-item:hover { background: #F9FAFB; }
.dropdown-danger:hover { background: #FEF2F2; color: #ED1B2F; }
</style>
