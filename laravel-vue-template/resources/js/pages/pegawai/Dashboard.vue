<template>
    <div class="space-y-6">

        <!-- Header -->
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Selamat datang, {{ auth.user?.name }}
            </h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                    Pegawai
                </span>
                <span v-if="auth.user?.employee_number" class="text-xs text-gray-400">
                    {{ auth.user.employee_number }}
                </span>
                <span v-if="auth.user?.department" class="text-xs text-gray-400">
                    · {{ auth.user.department.name }}
                </span>
            </div>
        </div>

        <!-- Stat Cards -->
        <div v-if="isLoading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div
                v-for="i in 6"
                :key="i"
                class="bg-white rounded-xl border border-gray-200 p-4 animate-pulse"
            >
                <div class="h-3 bg-gray-200 rounded w-2/3 mb-3"></div>
                <div class="h-7 bg-gray-200 rounded w-1/3"></div>
            </div>
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <button
                v-for="card in statCards"
                :key="card.key"
                @click="goToDokumen(card.key)"
                class="bg-white rounded-xl border border-gray-200 p-4 text-left hover:shadow-sm transition-shadow group"
            >
                <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700 transition-colors">
                    {{ card.label }}
                </p>
                <p :class="['text-2xl font-bold mt-1', card.color]">
                    {{ stats[card.key] ?? 0 }}
                </p>
                <div :class="['w-6 h-1 rounded-full mt-2', card.bar]"></div>
            </button>
        </div>

        <!-- Notifikasi Terbaru -->
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Notifikasi Terbaru</h3>
                <button
                    @click="goToNotifikasi"
                    class="text-xs text-blue-600 hover:underline"
                >
                    Lihat Semua →
                </button>
            </div>

            <!-- Loading -->
            <div v-if="isLoading" class="divide-y divide-gray-50">
                <div
                    v-for="i in 3"
                    :key="i"
                    class="px-5 py-4 animate-pulse flex gap-3"
                >
                    <div class="w-2 h-2 rounded-full bg-gray-200 mt-1.5 shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                    </div>
                </div>
            </div>

            <!-- Kosong -->
            <div
                v-else-if="notifications.length === 0"
                class="px-5 py-10 text-center text-sm text-gray-400"
            >
                Belum ada notifikasi
            </div>

            <!-- List -->
            <ul v-else class="divide-y divide-gray-50">
                <li
                    v-for="notif in notifications"
                    :key="notif.id"
                    class="px-5 py-4 flex items-start gap-3 hover:bg-gray-50 transition-colors"
                >
                    <!-- Unread dot -->
                    <span
                        :class="[
                            'w-2 h-2 rounded-full mt-1.5 shrink-0',
                            notif.is_read ? 'bg-gray-200' : 'bg-blue-500'
                        ]"
                    ></span>

                    <div class="flex-1 min-w-0">
                        <p :class="['text-sm truncate', notif.is_read ? 'text-gray-600' : 'text-gray-900 font-medium']">
                            {{ notif.title }}
                        </p>
                        <p class="text-xs text-gray-400 truncate mt-0.5">
                            {{ notif.body }}
                        </p>
                    </div>

                    <span class="text-xs text-gray-400 shrink-0 mt-0.5">
                        {{ timeAgo(notif.created_at) }}
                    </span>
                </li>
            </ul>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const auth = useAuthStore();
const router = useRouter();

const isLoading = ref(true);
const stats = ref({});
const notifications = ref([]);

const statCards = [
    { key: 'total',          label: 'Total Dokumen',   color: 'text-gray-900',   bar: 'bg-gray-300' },
    { key: 'aktif',          label: 'Aktif',            color: 'text-green-600',  bar: 'bg-green-400' },
    { key: 'segera_expired', label: 'Segera Expired',  color: 'text-amber-600',  bar: 'bg-amber-400' },
    { key: 'expired',        label: 'Expired',          color: 'text-red-600',    bar: 'bg-red-400' },
    { key: 'pending_approval', label: 'Pending',        color: 'text-gray-500',   bar: 'bg-gray-300' },
    { key: 'ditolak',        label: 'Ditolak',          color: 'text-pink-600',   bar: 'bg-pink-400' },
];

async function fetchDashboard() {
    isLoading.value = true;
    try {
        const { data } = await axios.get('/api/pegawai/dashboard');
        stats.value = data.stats;
        notifications.value = data.notifications;
    } catch {
        // handle error diam-diam, data tetap kosong
    } finally {
        isLoading.value = false;
    }
}

function goToDokumen(status) {
    router.push({ name: 'pegawai.dokumen', query: { status } }).catch(() => {});
}

function goToNotifikasi() {
    router.push({ name: 'pegawai.notifikasi' });
}

function timeAgo(dateString) {
    if (!dateString) return '';
    const diff = Math.floor((Date.now() - new Date(dateString)) / 1000);
    if (diff < 60) return `${diff}d lalu`;
    if (diff < 3600) return `${Math.floor(diff / 60)}m lalu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}j lalu`;
    return `${Math.floor(diff / 86400)}h lalu`;
}

onMounted(fetchDashboard);
</script>
