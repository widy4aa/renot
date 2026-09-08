<template>
    <div class="space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Notifikasi</h2>
                <p class="text-sm text-gray-500 mt-1">
                    <span v-if="unreadCount > 0">{{ unreadCount }} belum dibaca</span>
                    <span v-else>Semua sudah dibaca</span>
                </p>
            </div>
            <button
                v-if="unreadCount > 0"
                @click="markAllAsRead"
                :disabled="isMarkingAll"
                class="text-sm text-blue-600 hover:underline disabled:opacity-50"
            >
                {{ isMarkingAll ? 'Memproses...' : 'Tandai semua dibaca' }}
            </button>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-50">
            <div v-for="i in 5" :key="i" class="px-5 py-4 flex gap-4 animate-pulse">
                <div class="w-2 h-2 rounded-full bg-gray-200 mt-2 shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                    <div class="h-3 bg-gray-200 rounded w-full"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/4"></div>
                </div>
            </div>
        </div>

        <!-- Kosong -->
        <div
            v-else-if="notifications.length === 0"
            class="bg-white rounded-xl border border-gray-200 py-20 text-center"
        >
            <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="text-sm text-gray-400">Belum ada notifikasi</p>
        </div>

        <!-- List -->
        <div v-else class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-50">
            <div
                v-for="notif in notifications"
                :key="notif.id"
                :class="[
                    'px-5 py-4 flex gap-4 transition-colors',
                    notif.is_read ? 'bg-white' : 'bg-blue-50/40'
                ]"
            >
                <!-- Unread dot -->
                <div class="shrink-0 mt-2">
                    <span
                        :class="[
                            'block w-2 h-2 rounded-full',
                            notif.is_read ? 'bg-gray-200' : 'bg-blue-500'
                        ]"
                    ></span>
                </div>

                <!-- Konten -->
                <div class="flex-1 min-w-0">
                    <!-- Icon type + judul -->
                    <div class="flex items-start gap-2">
                        <span :class="['shrink-0 mt-0.5', typeConfig(notif.type).color]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="typeConfig(notif.type).icon"/>
                            </svg>
                        </span>
                        <p :class="['text-sm leading-snug', notif.is_read ? 'text-gray-700' : 'text-gray-900 font-medium']">
                            {{ notif.title }}
                        </p>
                    </div>

                    <!-- Body -->
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ notif.body }}</p>

                    <!-- Meta: waktu + aksi -->
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-xs text-gray-400">{{ timeAgo(notif.created_at) }}</span>

                        <template v-if="!notif.is_read">
                            <span class="text-gray-200">·</span>
                            <button
                                @click="markAsRead(notif)"
                                class="text-xs text-blue-600 hover:underline"
                            >
                                Tandai dibaca
                            </button>
                        </template>

                        <template v-if="notif.document_id">
                            <span class="text-gray-200">·</span>
                            <RouterLink
                                :to="{ name: 'pegawai.dokumen.detail', params: { id: notif.document_id } }"
                                class="text-xs text-green-600 hover:underline"
                            >
                                Lihat Dokumen
                            </RouterLink>
                        </template>
                    </div>
                </div>

                <!-- Badge tipe -->
                <div class="shrink-0">
                    <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', typeConfig(notif.type).badge]">
                        {{ typeConfig(notif.type).label }}
                    </span>
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
        color: 'text-amber-500',
        badge: 'bg-amber-100 text-amber-700',
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    dokumen_expired: {
        label: 'Expired',
        color: 'text-red-500',
        badge: 'bg-red-100 text-red-700',
        icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    },
    dokumen_pending: {
        label: 'Pending',
        color: 'text-gray-400',
        badge: 'bg-gray-100 text-gray-600',
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    dokumen_approved: {
        label: 'Disetujui',
        color: 'text-green-500',
        badge: 'bg-green-100 text-green-700',
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    dokumen_ditolak: {
        label: 'Ditolak',
        color: 'text-pink-500',
        badge: 'bg-pink-100 text-pink-700',
        icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
    },
};

function typeConfig(type) {
    return typeMap[type] ?? {
        label: 'Info',
        color: 'text-blue-400',
        badge: 'bg-blue-100 text-blue-600',
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
    } catch {
        //
    }
}

async function markAllAsRead() {
    isMarkingAll.value = true;
    try {
        await axios.post('/api/pegawai/notifications/read-all');
        notifications.value.forEach(n => { n.is_read = true; });
    } catch {
        //
    } finally {
        isMarkingAll.value = false;
    }
}

onMounted(fetchNotifications);
</script>
