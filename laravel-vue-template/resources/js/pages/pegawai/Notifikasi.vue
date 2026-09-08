<template>
    <div class="space-y-5">

        <!-- ── Header card ─────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold" style="color:#111827;">Notifikasi</h2>
                <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">
                    <span v-if="unreadCount > 0">{{ unreadCount }} belum dibaca</span>
                    <span v-else>Semua notifikasi sudah dibaca</span>
                </p>
            </div>
            <button
                v-if="unreadCount > 0"
                @click="markAllAsRead"
                :disabled="isMarkingAll"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors disabled:opacity-50"
                style="background:#EFF6FF; color:#006CB8;"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ isMarkingAll ? 'Memproses...' : 'Tandai semua dibaca' }}
            </button>
        </div>

        <!-- ── Loading ─────────────────────────────────── -->
        <div v-if="isLoading" class="space-y-3">
            <div v-for="i in 5" :key="i" class="card-elevated rounded-2xl px-5 py-5 flex gap-4 animate-pulse">
                <div class="w-11 h-11 rounded-xl bg-gray-100 shrink-0"></div>
                <div class="flex-1 space-y-2.5 pt-1">
                    <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                    <div class="h-3 bg-gray-100 rounded w-full"></div>
                    <div class="h-3 bg-gray-100 rounded w-1/3"></div>
                </div>
            </div>
        </div>

        <!-- ── Kosong ─────────────────────────────────── -->
        <div v-else-if="notifications.length === 0" class="card-elevated rounded-2xl py-20 text-center">
            <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#F3F4F6;">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#D1D5DB;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <p class="text-sm font-bold" style="color:#374151;">Belum ada notifikasi</p>
            <p class="text-xs mt-1 font-medium" style="color:#9CA3AF;">Kamu sudah up to date</p>
        </div>

        <!-- ── List notifikasi ─────────────────────────── -->
        <div v-else class="space-y-3">
            <div
                v-for="notif in notifications"
                :key="notif.id"
                class="card-elevated rounded-2xl px-5 py-5 flex gap-4 transition-all"
                :style="!notif.is_read ? 'border-left:3px solid ' + typeConfig(notif.type).accentColor + ';' : ''"
            >
                <!-- Icon container -->
                <div
                    class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                    :style="{ background: typeConfig(notif.type).bgColor }"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color: typeConfig(notif.type).accentColor }">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="typeConfig(notif.type).icon"/>
                    </svg>
                </div>

                <!-- Konten -->
                <div class="flex-1 min-w-0">
                    <!-- Judul + badge -->
                    <div class="flex items-start justify-between gap-3 mb-1">
                        <p
                            class="text-base leading-snug"
                            :style="notif.is_read ? 'color:#374151; font-weight:500;' : 'color:#111827; font-weight:700;'"
                        >{{ notif.title }}</p>
                        <span
                            class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold"
                            :style="{ background: typeConfig(notif.type).bgColor, color: typeConfig(notif.type).accentColor }"
                        >{{ typeConfig(notif.type).label }}</span>
                    </div>

                    <!-- Body -->
                    <p class="text-sm font-medium leading-relaxed mb-3" style="color:#6B7280;">{{ notif.body }}</p>

                    <!-- Meta + aksi -->
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-xs font-semibold" style="color:#9CA3AF;">{{ timeAgo(notif.created_at) }}</span>

                        <template v-if="!notif.is_read">
                            <span style="color:#E2E8F0;">·</span>
                            <button
                                @click="markAsRead(notif)"
                                class="text-xs font-bold transition-colors"
                                style="color:#006CB8;"
                            >Tandai dibaca</button>
                        </template>

                        <template v-if="notif.document_id">
                            <span style="color:#E2E8F0;">·</span>
                            <RouterLink
                                :to="{ name: 'pegawai.dokumen.detail', params: { id: notif.document_id } }"
                                class="inline-flex items-center gap-1 text-xs font-bold transition-colors"
                                style="color:#ED1B2F;"
                            >
                                Lihat Dokumen
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </RouterLink>
                        </template>

                        <!-- Unread dot -->
                        <span v-if="!notif.is_read" class="ml-auto w-2.5 h-2.5 rounded-full shrink-0" :style="{ background: typeConfig(notif.type).accentColor }"></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const isLoading    = ref(true);
const isMarkingAll = ref(false);
const notifications = ref([]);
const unreadCount   = computed(() => notifications.value.filter(n => !n.is_read).length);

const typeMap = {
    reminder_akan_expired: {
        label: 'Reminder',
        accentColor: '#D97706',
        bgColor: '#FEF3C7',
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    dokumen_expired: {
        label: 'Expired',
        accentColor: '#ED1B2F',
        bgColor: '#FEE2E2',
        icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    },
    dokumen_pending: {
        label: 'Pending',
        accentColor: '#6B7280',
        bgColor: '#F3F4F6',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    },
    dokumen_approved: {
        label: 'Disetujui',
        accentColor: '#ACC42A',
        bgColor: '#F7FEE7',
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    dokumen_ditolak: {
        label: 'Ditolak',
        accentColor: '#DB2777',
        bgColor: '#FCE7F3',
        icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
    },
};

function typeConfig(type) {
    return typeMap[type] ?? {
        label: 'Info',
        accentColor: '#006CB8',
        bgColor: '#EFF6FF',
        icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    };
}

function timeAgo(iso) {
    if (!iso) return '';
    const diff = Math.floor((Date.now() - new Date(iso)) / 1000);
    if (diff < 60) return `${diff}d lalu`;
    if (diff < 3600) return `${Math.floor(diff / 60)}m lalu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}j lalu`;
    const days = Math.floor(diff / 86400);
    return days === 1 ? 'Kemarin' : `${days} hari lalu`;
}

async function fetchNotifications() {
    isLoading.value = true;
    try {
        const { data } = await axios.get('/api/pegawai/notifications');
        notifications.value = data.data;
    } catch {
        notifications.value = [];
    } finally {
        isLoading.value = false;
    }
}

async function markAsRead(notif) {
    try {
        await axios.post(`/api/pegawai/notifications/${notif.id}/read`);
        notif.is_read = true;
    } catch { /**/ }
}

async function markAllAsRead() {
    isMarkingAll.value = true;
    try {
        await axios.post('/api/pegawai/notifications/read-all');
        notifications.value.forEach(n => { n.is_read = true; });
    } catch { /**/ } finally {
        isMarkingAll.value = false;
    }
}

onMounted(fetchNotifications);
</script>
