<template>
    <div class="space-y-6">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center gap-5">
            <!-- Avatar -->
            <div class="shrink-0 cursor-pointer" @click="router.push({ name: 'pegawai.profile' })">
                <img
                    v-if="auth.user?.avatar"
                    :src="auth.user.avatar"
                    alt="Avatar"
                    class="w-16 h-16 rounded-2xl object-cover transition-opacity duration-150 hover:opacity-80"
                    style="border:2px solid #E2E8F0;"
                />
                <div
                    v-else
                    class="w-16 h-16 rounded-2xl flex items-center justify-center text-white text-2xl font-bold transition-opacity duration-150 hover:opacity-80"
                    style="background:#ED1B2F;"
                >
                    {{ auth.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                </div>
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-tight" style="color:#111827;">
                    Selamat datang, {{ auth.user?.name }}
                </h2>
                <div class="flex items-center gap-2 mt-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold" style="background:#FEE2E2; color:#991B1B;">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Pegawai
                    </span>
                    <span v-if="auth.user?.employee_number" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold" style="background:#F3F4F6; color:#111827;">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c0 1.657 1.343 3 3 3s3-1.343 3-3"/></svg>
                        {{ auth.user.employee_number }}
                    </span>
                    <span v-if="auth.user?.department" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold" style="background:#F3F4F6; color:#111827;">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        {{ auth.user.department.name }}
                    </span>
                </div>
            </div>

            <!-- Tanggal -->
            <div class="shrink-0 text-right hidden sm:block">
                <p class="text-xs font-semibold uppercase tracking-wide" style="color:#6B7280;">Hari ini</p>
                <p class="text-sm font-bold mt-0.5" style="color:#111827;">{{ todayDate }}</p>
            </div>
        </div>

        <!-- ── Stat Cards skeleton ────────────────────────── -->
        <div v-if="isLoading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div v-for="i in 6" :key="i" class="card-elevated rounded-xl p-4 animate-pulse">
                <div class="flex justify-between items-start mb-3">
                    <div class="h-2.5 bg-gray-200 rounded w-2/3"></div>
                    <div class="w-8 h-8 bg-gray-100 rounded-lg shrink-0"></div>
                </div>
                <div class="h-7 bg-gray-200 rounded w-1/3"></div>
            </div>
        </div>

        <!-- ── Stat Cards ─────────────────────────────────── -->
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <button
                v-for="card in statCards"
                :key="card.key"
                @click="goToDokumen(card.key)"
                class="stat-card card-elevated rounded-xl text-left relative overflow-hidden"
            >
                <!-- Top accent bar -->
                <div class="absolute top-0 left-0 right-0 h-1" :style="{ background: card.accentColor }"></div>

                <div class="p-3 pt-4">
                    <!-- Icon kanan -->
                    <div class="flex justify-end mb-3">
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center"
                            :style="{ background: card.accentColor + '22' }"
                        >
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color: card.accentColor }">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon"/>
                            </svg>
                        </div>
                    </div>
                    <!-- Angka -->
                    <p class="text-5xl font-extrabold leading-none mb-1 -mt-2" :style="{ color: card.accentColor }">
                        {{ stats[card.key] ?? 0 }}
                    </p>
                    <!-- Label -->
                    <p class="text-sm font-bold" style="color:#374151;">{{ card.label }}</p>
                </div>
            </button>
        </div>

        <!-- ── Quick Actions + Search ─────────────────────── -->
        <div class="flex items-center gap-3">
            <!-- Tombol kiri -->
            <RouterLink
                :to="{ name: 'pegawai.dokumen.tambah' }"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shrink-0"
                style="background:#ED1B2F;"
                onmouseover="this.style.background='#c8102e'"
                onmouseout="this.style.background='#ED1B2F'"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Upload Dokumen
            </RouterLink>
            <RouterLink
                :to="{ name: 'pegawai.dokumen' }"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all card-elevated shrink-0"
                style="color:#374151;"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#6B7280;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Semua Dokumen
            </RouterLink>

            <!-- Search field -->
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                    </svg>
                </span>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari dokumen atau sertifikasi..."
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl text-sm font-medium outline-none transition-all"
                    style="background:#fff; border:1.5px solid #ED1B2F; color:#111827;"
                    @focus="e => e.target.style.borderColor='#c8102e'"
                    @blur="e => e.target.style.borderColor='#ED1B2F'"
                />
            </div>
        </div>

        <!-- ── Bottom: 3 kolom ────────────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">

            <!-- Notifikasi Terbaru (2/4) -->
            <div class="card-elevated rounded-xl lg:col-span-2">
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #F3F4F6;">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#FEE2E2;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold" style="color:#111827;">Notifikasi Terbaru</h3>
                    </div>
                    <button
                        @click="goToNotifikasi"
                        class="text-xs font-semibold px-2.5 py-1 rounded-lg transition-colors"
                        style="color:#ED1B2F;"
                        onmouseover="this.style.background='#FEE2E2'"
                        onmouseout="this.style.background='transparent'"
                    >
                        Lihat Semua →
                    </button>
                </div>

                <!-- Loading skeleton -->
                <div v-if="isLoading" class="divide-y" style="divide-color:#F9FAFB;">
                    <div v-for="i in 3" :key="i" class="px-5 py-4 animate-pulse flex gap-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg shrink-0"></div>
                        <div class="flex-1 space-y-2 pt-1">
                            <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>

                <!-- Kosong -->
                <div v-else-if="notifications.length === 0" class="px-5 py-10 text-center">
                    <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#F3F4F6;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#D1D5DB;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium" style="color:#6B7280;">Belum ada notifikasi</p>
                    <p class="text-xs mt-0.5" style="color:#9CA3AF;">Kamu sudah up to date</p>
                </div>

                <!-- List -->
                <ul v-else>
                    <li
                        v-for="notif in notifications"
                        :key="notif.id"
                        class="notif-row flex items-start gap-3 px-5 py-3.5"
                        :class="!notif.is_read ? 'notif-row-unread' : ''"
                    >
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5" :style="{ background: typeConfig(notif.type).bgColor }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color: typeConfig(notif.type).iconColor }">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="typeConfig(notif.type).icon"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm leading-snug" :style="notif.is_read ? 'color:#374151; font-weight:500;' : 'color:#111827; font-weight:700;'">{{ notif.title }}</p>
                            <p class="text-xs mt-0.5 truncate font-medium" style="color:#6B7280;">{{ notif.body }}</p>
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-1.5 mt-0.5">
                            <span class="text-xs whitespace-nowrap" style="color:#9CA3AF;">{{ timeAgo(notif.created_at) }}</span>
                            <span v-if="!notif.is_read" class="w-2 h-2 rounded-full" style="background:#ED1B2F;"></span>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Sertifikat Terakhir Diinput (1/4) -->
            <div class="card-elevated rounded-xl flex flex-col">
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #F3F4F6;">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#F7FEE7;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ACC42A;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold" style="color:#111827;">Sertifikat Terakhir</h3>
                    </div>
                    <RouterLink
                        :to="{ name: 'pegawai.dokumen' }"
                        class="text-xs font-semibold px-2.5 py-1 rounded-lg transition-colors"
                        style="color:#ED1B2F;"
                        onmouseover="this.style.background='#FEE2E2'"
                        onmouseout="this.style.background='transparent'"
                    >Lihat Semua →</RouterLink>
                </div>

                <!-- Loading -->
                <div v-if="isLoading" class="flex-1 divide-y" style="divide-color:#F9FAFB;">
                    <div v-for="i in 4" :key="i" class="px-5 py-3.5 animate-pulse">
                        <div class="h-3 bg-gray-200 rounded w-3/4 mb-2"></div>
                        <div class="h-2.5 bg-gray-100 rounded w-1/2"></div>
                    </div>
                </div>

                <!-- Kosong -->
                <div v-else-if="recentDocs.length === 0" class="flex-1 flex flex-col items-center justify-center px-5 py-10 text-center">
                    <div class="w-12 h-12 rounded-2xl mb-3 flex items-center justify-center" style="background:#F3F4F6;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#D1D5DB;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium" style="color:#6B7280;">Belum ada dokumen</p>
                    <RouterLink :to="{ name: 'pegawai.dokumen.tambah' }" class="text-xs mt-1 font-semibold" style="color:#ED1B2F;">Upload sekarang →</RouterLink>
                </div>

                <!-- List dokumen terakhir -->
                <ul v-else class="flex-1 divide-y" style="divide-color:#F9FAFB;">
                    <li
                        v-for="doc in recentDocs"
                        :key="doc.id"
                        class="px-5 py-3.5 hover:bg-gray-50 transition-colors cursor-pointer"
                        @click="goToDetail(doc.id)"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-sm font-bold truncate" style="color:#111827;">
                                    {{ doc.certification_type?.name ?? '—' }}
                                </p>
                                <p class="text-xs mt-0.5 truncate font-semibold" style="color:#6B7280;">
                                    {{ doc.certification_type?.category?.name ?? '—' }}
                                </p>
                            </div>
                            <!-- Status dot -->
                            <span
                                class="shrink-0 mt-0.5 inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold"
                                :style="statusStyle(doc.status)"
                            >
                                {{ statusLabel(doc.status) }}
                            </span>
                        </div>
                        <!-- Expiry -->
                        <p class="text-xs mt-1.5 flex items-center gap-1 font-semibold" style="color:#6B7280;">
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Exp: {{ formatExpiry(doc.expiry_date) }}
                        </p>
                    </li>
                </ul>
            </div>

            <!-- ── Chart Donut: Sertifikat per Kategori (1/4) ── -->
            <div class="card-elevated rounded-xl flex flex-col">
                <!-- Header dengan "Lihat semua" di kanan -->
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #F3F4F6;">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#EFF6FF;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#006CB8;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold" style="color:#111827;">Sebaran Kategori</h3>
                    </div>
                    <RouterLink
                        :to="{ name: 'pegawai.dokumen' }"
                        class="text-xs font-semibold px-2.5 py-1 rounded-lg transition-colors"
                        style="color:#ED1B2F;"
                        onmouseover="this.style.background='#FEE2E2'"
                        onmouseout="this.style.background='transparent'"
                    >Lihat Semua →</RouterLink>
                </div>

                <!-- Loading -->
                <div v-if="isLoading" class="flex-1 flex items-center justify-center py-8">
                    <div class="w-36 h-36 rounded-full animate-pulse" style="background:#F3F4F6;"></div>
                </div>

                <!-- Kosong -->
                <div v-else-if="donutSlices.length === 0" class="flex-1 flex flex-col items-center justify-center px-5 py-8 text-center">
                    <p class="text-sm font-medium" style="color:#6B7280;">Belum ada data</p>
                </div>

                <!-- Chart + Legend -->
                <div v-else class="flex-1 flex flex-col items-center justify-center px-5 py-5 gap-5">
                    <!-- SVG Donut diperbesar -->
                    <div class="relative">
                        <svg width="180" height="180" viewBox="0 0 180 180" style="transform:rotate(-90deg);">
                            <circle cx="90" cy="90" r="70" fill="none" stroke="#F3F4F6" stroke-width="22"/>
                            <circle
                                v-for="(slice, i) in donutSlices"
                                :key="i"
                                cx="90" cy="90" r="70"
                                fill="none"
                                :stroke="slice.color"
                                stroke-width="22"
                                :stroke-dasharray="`${slice.dash} ${CIRCUMFERENCE - slice.dash}`"
                                :stroke-dashoffset="slice.offset"
                                stroke-linecap="butt"
                                style="transition: stroke-dasharray 400ms ease;"
                            />
                        </svg>
                        <!-- Total di tengah -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <p class="text-3xl font-extrabold" style="color:#111827;">{{ totalDocs }}</p>
                            <p class="text-xs font-semibold" style="color:#6B7280;">Dokumen</p>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="w-full space-y-2.5">
                        <div v-for="(slice, i) in donutSlices" :key="i" class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-3 h-3 rounded-full shrink-0" :style="{ background: slice.color }"></span>
                                <span class="text-sm font-semibold truncate" style="color:#374151;">{{ slice.label }}</span>
                            </div>
                            <span class="text-sm font-bold shrink-0" :style="{ color: slice.color }">{{ slice.count }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const auth = useAuthStore();
const router = useRouter();

const isLoading = ref(true);
const stats = ref({});
const notifications = ref([]);
const recentDocs = ref([]);
const allDocs = ref([]);
const searchQuery = ref('');

const todayDate = computed(() =>
    new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
);

// Donut chart — kelompok berdasar kategori dari semua dokumen
const DONUT_COLORS = ['#ED1B2F', '#006CB8', '#ACC42A', '#D97706', '#DB2777', '#6B7280'];
const CIRCUMFERENCE = 2 * Math.PI * 70; // 439.82

const totalDocs = computed(() => allDocs.value.length);

const donutSlices = computed(() => {
    if (!allDocs.value.length) return [];
    // Group by category
    const groups = {};
    allDocs.value.forEach(doc => {
        const cat = doc.certification_type?.category?.name ?? 'Lainnya';
        groups[cat] = (groups[cat] ?? 0) + 1;
    });
    const total = allDocs.value.length;
    let offsetAngle = 0;
    return Object.entries(groups).map(([label, count], i) => {
        const pct = count / total;
        const dash = pct * CIRCUMFERENCE;
        // offset: mulai dari atas (rotate -90deg = offset = CIRCUMFERENCE * 0.25 dari sudut 0)
        const offset = CIRCUMFERENCE - offsetAngle;
        offsetAngle += dash;
        return { label, count, color: DONUT_COLORS[i % DONUT_COLORS.length], dash, offset };
    });
});

const statCards = [
    { key: 'total',            label: 'Total Dokumen',  accentColor: '#374151', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
    { key: 'aktif',            label: 'Aktif',          accentColor: '#ACC42A', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { key: 'segera_expired',   label: 'Segera Expired', accentColor: '#D97706', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { key: 'expired',          label: 'Expired',        accentColor: '#ED1B2F', icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' },
    { key: 'pending_approval', label: 'Pending',        accentColor: '#6B7280', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
    { key: 'ditolak',          label: 'Ditolak',        accentColor: '#DB2777', icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' },
];

const typeMap = {
    reminder_akan_expired: { bgColor: '#FEF3C7', iconColor: '#D97706', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    dokumen_expired:       { bgColor: '#FEE2E2', iconColor: '#ED1B2F', icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' },
    dokumen_pending:       { bgColor: '#F3F4F6', iconColor: '#6B7280', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
    dokumen_approved:      { bgColor: '#F7FEE7', iconColor: '#ACC42A', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    dokumen_ditolak:       { bgColor: '#FCE7F3', iconColor: '#DB2777', icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' },
};

function typeConfig(type) {
    return typeMap[type] ?? { bgColor: '#EFF6FF', iconColor: '#006CB8', icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' };
}

async function fetchDashboard() {
    isLoading.value = true;
    try {
        const { data } = await axios.get('/api/pegawai/dashboard');
        stats.value = data.stats;
        notifications.value = data.notifications;
        // Ambil dokumen terakhir dari API dokumen (4 terbaru)
        try {
            const docsRes = await axios.get('/api/pegawai/documents');
            const docs = docsRes.data.data ?? [];
            recentDocs.value = docs.slice(0, 4);
            allDocs.value = docs;
        } catch { recentDocs.value = []; allDocs.value = []; }
    } catch { /**/ } finally {
        isLoading.value = false;
    }
}

function goToDokumen(status) {
    router.push({ name: 'pegawai.dokumen', query: { status } }).catch(() => {});
}
function goToNotifikasi() {
    router.push({ name: 'pegawai.notifikasi' });
}
function goToDetail(id) {
    router.push({ name: 'pegawai.dokumen.detail', params: { id } });
}

const statusMap = {
    aktif:            { label: 'Aktif',   bg: '#F7FEE7', color: '#5a6e0f' },
    segera_expired:   { label: 'Segera',  bg: '#FEF3C7', color: '#92400E' },
    expired:          { label: 'Expired', bg: '#FEE2E2', color: '#991B1B' },
    pending_approval: { label: 'Pending', bg: '#F3F4F6', color: '#6B7280' },
    ditolak:          { label: 'Ditolak', bg: '#FCE7F3', color: '#9D174D' },
};
function statusLabel(status) { return statusMap[status]?.label ?? status; }
function statusStyle(status) {
    const s = statusMap[status] ?? { bg: '#F3F4F6', color: '#6B7280' };
    return `background:${s.bg}; color:${s.color};`;
}
function formatExpiry(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
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

<style scoped>
.stat-card {
    transition: box-shadow 200ms ease, transform 200ms ease;
    cursor: pointer;
}
.stat-card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.10), 0 2px 6px rgba(0,0,0,0.06) !important;
    transform: translateY(-2px);
}
.notif-row {
    border-bottom: 1px solid #F3F4F6;
    transition: background 150ms;
}
.notif-row:last-child { border-bottom: none; }
.notif-row:hover { background: #F9FAFB; }
.notif-row-unread { background: #FFFBFB; border-left: 3px solid #ED1B2F; padding-left: calc(1.25rem - 3px); }
</style>
