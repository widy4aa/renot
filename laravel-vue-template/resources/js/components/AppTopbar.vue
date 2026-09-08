<template>
    <header class="h-14 shrink-0 flex items-center justify-between px-5 z-20"
        style="background:#ED1B2F; border-radius:16px; box-shadow:0 2px 16px rgba(237,27,47,0.25), 0 1px 4px rgba(0,0,0,0.08);">

        <!-- Kiri: Logo -->
        <div class="flex items-center gap-4 shrink-0">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl" style="background:#ffffff;">
                <img src="/icon.png" alt="ReNot" class="h-7 w-7 object-contain" />
            </div>
            <div class="flex items-center gap-3">
                <p class="text-2xl font-bold tracking-tight" style="color:#ffffff;">ReNot</p>
                <span class="w-px h-5" style="background:rgba(255,255,255,0.40);"></span>
                <p class="text-base font-bold tracking-widest uppercase" style="color:rgba(255,255,255,0.90);">Pertamina</p>
            </div>
        </div>

        <!-- Kanan: bell + nama user (no dropdown) -->
        <div class="flex items-center gap-2 shrink-0">

            <!-- Bell Notifikasi -->
            <div v-if="notifRoute" class="relative" ref="notifRef">
                <button
                    @click="toggleNotif"
                    class="relative flex items-center justify-center w-9 h-9 rounded-xl transition-all duration-150 notif-btn"
                    title="Notifikasi"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:rgba(255,255,255,0.90);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span v-if="localUnread > 0" class="absolute top-1.5 right-1.5 flex">
                        <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full opacity-60" style="background:#fff;"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2" style="background:#fff;"></span>
                    </span>
                </button>

                <!-- Notif Dropdown -->
                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="opacity-0 translate-y-1 scale-95"
                    enter-to-class="opacity-100 translate-y-0 scale-100"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="opacity-100 translate-y-0 scale-100"
                    leave-to-class="opacity-0 translate-y-1 scale-95"
                >
                    <div
                        v-if="notifOpen"
                        class="notif-panel absolute right-0 top-full mt-2.5 w-80 rounded-2xl z-50 bg-white overflow-hidden"
                        style="border:1px solid #E5E7EB; box-shadow:0 8px 32px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06); transform-origin: top right;"
                    >
                        <div class="flex items-center justify-between px-4 py-3" style="border-bottom:1px solid #F3F4F6; background:#FAFAFA;">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold" style="color:#111827;">Notifikasi</span>
                                <span v-if="localUnread > 0" class="text-[10px] font-bold px-2 py-0.5 rounded-full text-white" style="background:#ED1B2F;">
                                    {{ localUnread }}
                                </span>
                            </div>
                            <button
                                v-if="localUnread > 0"
                                @click="markAllRead"
                                :disabled="markingAll"
                                class="text-xs font-semibold px-2 py-1 rounded-lg transition-colors"
                                style="color:#ED1B2F;"
                                onmouseover="this.style.background='#FEE2E2'"
                                onmouseout="this.style.background='transparent'"
                            >
                                {{ markingAll ? '...' : 'Baca semua' }}
                            </button>
                        </div>

                        <div v-if="notifLoading" class="px-4 py-6 text-center">
                            <svg class="w-5 h-5 animate-spin mx-auto" fill="none" viewBox="0 0 24 24" style="color:#9CA3AF;">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>

                        <div v-else-if="notifList.length === 0" class="px-4 py-10 text-center">
                            <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#F3F4F6;">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#D1D5DB;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium" style="color:#6B7280;">Belum ada notifikasi</p>
                        </div>

                        <ul v-else>
                            <li
                                v-for="notif in notifList"
                                :key="notif.id"
                                @click="handleNotifClick(notif)"
                                class="flex gap-3 px-4 py-3.5 cursor-pointer transition-colors notif-item"
                                :class="!notif.is_read ? 'notif-unread' : ''"
                            >
                                <span class="shrink-0 mt-1.5">
                                    <span class="block w-2 h-2 rounded-full" :style="notif.is_read ? 'background:#E5E7EB;' : 'background:#ED1B2F;'"></span>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm leading-snug truncate" :style="notif.is_read ? 'color:#6B7280;' : 'color:#111827; font-weight:500;'">{{ notif.title }}</p>
                                    <p class="text-xs mt-0.5 line-clamp-2 leading-relaxed" style="color:#9CA3AF;">{{ notif.body }}</p>
                                    <p class="text-[10px] mt-1" style="color:#9CA3AF;">{{ timeAgo(notif.created_at) }}</p>
                                </div>
                                <span class="shrink-0 self-start mt-1 text-[10px] font-semibold px-2 py-0.5 rounded-full" :style="typeBadge(notif.type)">
                                    {{ typeLabel(notif.type) }}
                                </span>
                            </li>
                        </ul>

                        <div class="px-4 py-3" style="border-top:1px solid #F3F4F6; background:#FAFAFA;">
                            <RouterLink
                                :to="{ name: notifRoute }"
                                class="flex items-center justify-center gap-1.5 w-full text-sm font-semibold py-1 rounded-lg transition-colors"
                                style="color:#ED1B2F;"
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
            <div class="w-px h-5 mx-1" style="background:rgba(255,255,255,0.30);"></div>

            <!-- Jam live -->
            <div class="flex items-center gap-1.5 px-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:rgba(255,255,255,0.70);">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-base font-bold tabular-nums" style="color:#ffffff; letter-spacing:0.03em;">{{ currentTime }}</span>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const props = defineProps({
    user: { type: Object, default: null },
    unreadCount: { type: Number, default: null },
    notifRoute: { type: String, default: null },
});

const emit = defineEmits(['notif-read']);
const router = useRouter();

const notifOpen    = ref(false);
const notifList    = ref([]);
const notifLoading = ref(false);
const markingAll   = ref(false);
const localUnread  = ref(props.unreadCount ?? 0);
const notifRef     = ref(null);
const currentTime  = ref('');

watch(() => props.unreadCount, val => { if (val !== null) localUnread.value = val; });

function updateTime() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    currentTime.value = `${h}:${m}`;
}

let clockInterval = null;
onMounted(() => {
    updateTime();
    clockInterval = setInterval(updateTime, 1000);
    document.addEventListener('click', onClickOutside);
});
onUnmounted(() => {
    clearInterval(clockInterval);
    document.removeEventListener('click', onClickOutside);
});

async function fetchNotif() {
    if (!props.notifRoute) return;
    notifLoading.value = true;
    try {
        const { data } = await axios.get('/api/pegawai/notifications');
        notifList.value   = data.data.slice(0, 5);
        localUnread.value = data.unread_count;
    } catch {
        notifList.value = [];
    } finally {
        notifLoading.value = false;
    }
}

function toggleNotif() {
    notifOpen.value = !notifOpen.value;
    if (notifOpen.value) fetchNotif();
}

async function handleNotifClick(notif) {
    if (!notif.is_read) {
        try {
            await axios.post(`/api/pegawai/notifications/${notif.id}/read`);
            notif.is_read = true;
            localUnread.value = Math.max(0, localUnread.value - 1);
            emit('notif-read', localUnread.value);
        } catch { /**/ }
    }
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
    } catch { /**/ } finally {
        markingAll.value = false;
    }
}

function onClickOutside(e) {
    if (notifRef.value && !notifRef.value.contains(e.target)) notifOpen.value = false;
}

function timeAgo(iso) {
    if (!iso) return '';
    const diff = Math.floor((Date.now() - new Date(iso)) / 1000);
    if (diff < 60)    return `${diff}d lalu`;
    if (diff < 3600)  return `${Math.floor(diff / 60)}m lalu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}j lalu`;
    const d = Math.floor(diff / 86400);
    return d === 1 ? 'Kemarin' : `${d} hari lalu`;
}

const typeMap = {
    reminder_akan_expired: { label: 'Reminder', bg: '#FEF3C7', color: '#92400E' },
    dokumen_expired:       { label: 'Expired',  bg: '#FEE2E2', color: '#991B1B' },
    dokumen_pending:       { label: 'Pending',  bg: '#F3F4F6', color: '#6B7280' },
    dokumen_approved:      { label: 'Disetujui',bg: '#F7FEE7', color: '#4a6a0a' },
    dokumen_ditolak:       { label: 'Ditolak',  bg: '#FCE7F3', color: '#9D174D' },
};
function typeLabel(type) { return typeMap[type]?.label ?? 'Info'; }
function typeBadge(type) {
    const t = typeMap[type] ?? { bg: '#EFF6FF', color: '#1D4ED8' };
    return `background:${t.bg}; color:${t.color};`;
}
</script>

<style scoped>
.notif-btn:hover { background: rgba(255,255,255,0.15); }
.notif-item { border-bottom: 1px solid #F9FAFB; }
.notif-item:hover { background: #F9FAFB; }
.notif-item:last-child { border-bottom: none; }
.notif-unread { background: #FFFBFB; }
</style>
