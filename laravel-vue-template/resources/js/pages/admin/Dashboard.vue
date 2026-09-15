<template>
    <div class="space-y-6">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center gap-5">
            <div class="shrink-0">
                <img v-if="auth.user?.avatar" :src="auth.user.avatar" alt="Avatar"
                    class="w-16 h-16 rounded-2xl object-cover" style="border:2px solid #E2E8F0;"/>
                <div v-else class="w-16 h-16 rounded-2xl flex items-center justify-center text-white text-2xl font-bold"
                    style="background:#006CB8;">
                    {{ auth.user?.name?.charAt(0)?.toUpperCase() ?? 'A' }}
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-tight" style="color:#111827;">
                    Selamat datang, {{ auth.user?.name }}
                </h2>
                <div class="flex items-center gap-2 mt-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold" style="background:#EFF6FF; color:#006CB8;">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Admin HR
                    </span>
                </div>
            </div>
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
            <template v-for="card in statCards" :key="card.key">
                <button v-if="card.clickable" @click="router.push({ name: 'admin.pegawai' })"
                    class="stat-card card-elevated rounded-xl text-left relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1" :style="{ background: card.accentColor }"></div>
                    <div class="p-3 pt-4">
                        <div class="flex justify-end mb-3">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center" :style="{ background: card.accentColor + '22' }">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color: card.accentColor }">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-5xl font-extrabold leading-none mb-1 -mt-2" :style="{ color: card.accentColor }">{{ stats[card.key] ?? 0 }}</p>
                        <p class="text-sm font-bold" style="color:#374151;">{{ card.label }}</p>
                    </div>
                </button>
                <div v-else class="card-elevated rounded-xl relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1" :style="{ background: card.accentColor }"></div>
                    <div class="p-3 pt-4">
                        <div class="flex justify-end mb-3">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center" :style="{ background: card.accentColor + '22' }">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color: card.accentColor }">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-5xl font-extrabold leading-none mb-1 -mt-2" :style="{ color: card.accentColor }">{{ stats[card.key] ?? 0 }}</p>
                        <p class="text-sm font-bold" style="color:#374151;">{{ card.label }}</p>
                    </div>
                </div>
            </template>
        </div>

        <!-- ── Row 2: Donut + Bar Chart ───────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

            <!-- Donut Chart: Distribusi Status (2/5) -->
            <div class="lg:col-span-2 card-elevated rounded-xl flex flex-col">
                <div class="flex items-center gap-2.5 px-5 py-4" style="border-bottom:1px solid #F3F4F6;">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#EFF6FF;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#006CB8;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold" style="color:#111827;">Distribusi Status Dokumen</h3>
                </div>

                <!-- Skeleton -->
                <div v-if="isLoading" class="flex-1 flex items-center justify-center py-10">
                    <div class="w-36 h-36 rounded-full animate-pulse" style="background:#F3F4F6;"></div>
                </div>

                <!-- Kosong -->
                <div v-else-if="!stats.total_dokumen" class="flex-1 flex items-center justify-center py-10 text-center">
                    <p class="text-sm font-medium" style="color:#6B7280;">Belum ada data dokumen</p>
                </div>

                <!-- Chart -->
                <div v-else class="flex-1 flex flex-col items-center justify-center px-5 py-5 gap-5">
                    <div class="relative">
                        <svg width="180" height="180" viewBox="0 0 180 180" style="transform:rotate(-90deg);">
                            <circle cx="90" cy="90" r="70" fill="none" stroke="#F3F4F6" stroke-width="22"/>
                            <circle v-for="(slice, i) in donutSlices" :key="i"
                                cx="90" cy="90" r="70"
                                fill="none"
                                :stroke="slice.color"
                                stroke-width="22"
                                :stroke-dasharray="`${slice.dash} ${CIRCUMFERENCE - slice.dash}`"
                                :stroke-dashoffset="slice.offset"
                                stroke-linecap="butt"
                                style="transition: stroke-dasharray 400ms ease;"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <p class="text-3xl font-extrabold" style="color:#111827;">{{ stats.total_dokumen }}</p>
                            <p class="text-xs font-semibold" style="color:#6B7280;">Dokumen</p>
                        </div>
                    </div>
                    <div class="w-full space-y-2.5">
                        <div v-for="(slice, i) in donutSlices" :key="i" class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-3 h-3 rounded-full shrink-0" :style="{ background: slice.color }"></span>
                                <span class="text-sm font-semibold truncate" style="color:#374151;">{{ slice.label }}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-sm font-bold" :style="{ color: slice.color }">{{ slice.count }}</span>
                                <span class="text-[10px] font-medium" style="color:#9CA3AF;">
                                    {{ stats.total_dokumen > 0 ? Math.round(slice.count / stats.total_dokumen * 100) : 0 }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bar Chart: Sebaran Per Departemen (3/5) -->
            <div class="lg:col-span-3 card-elevated rounded-xl flex flex-col">
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #F3F4F6;">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#FEE2E2;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold" style="color:#111827;">Sebaran Dokumen per Departemen</h3>
                    </div>
                    <!-- Legend warna -->
                    <div class="hidden sm:flex items-center gap-3">
                        <div v-for="leg in barLegend" :key="leg.label" class="flex items-center gap-1">
                            <span class="w-2.5 h-2.5 rounded-sm shrink-0" :style="{ background: leg.color }"></span>
                            <span class="text-[10px] font-medium" style="color:#9CA3AF;">{{ leg.label }}</span>
                        </div>
                    </div>
                </div>

                <!-- Skeleton -->
                <div v-if="isLoading" class="flex-1 px-5 py-5 space-y-5">
                    <div v-for="i in 5" :key="i" class="animate-pulse">
                        <div class="flex items-center gap-3 mb-1.5">
                            <div class="h-3 bg-gray-200 rounded w-24 shrink-0"></div>
                            <div class="flex-1 h-5 bg-gray-100 rounded-full"></div>
                            <div class="h-3 bg-gray-100 rounded w-8 shrink-0"></div>
                        </div>
                    </div>
                </div>

                <!-- Kosong -->
                <div v-else-if="departmentStats.length === 0" class="flex-1 flex items-center justify-center py-10 text-center">
                    <p class="text-sm font-medium" style="color:#6B7280;">Belum ada data departemen</p>
                </div>

                <!-- Bars -->
                <div v-else class="flex-1 px-5 py-5 space-y-4">
                    <div v-for="dep in departmentStats" :key="dep.name">
                        <div class="flex items-center gap-3 mb-1.5">
                            <p class="text-xs font-semibold shrink-0 w-24 truncate" style="color:#374151;">{{ dep.name }}</p>
                            <div class="flex-1 relative">
                                <!-- Background track -->
                                <div class="h-5 rounded-full overflow-hidden" style="background:#F3F4F6;">
                                    <div class="h-full flex">
                                        <!-- Aktif -->
                                        <div v-if="dep.aktif > 0"
                                            class="h-full transition-all duration-500"
                                            :style="{ width: pct(dep.aktif, dep.total) + '%', background: '#ACC42A' }"
                                            :title="`Aktif: ${dep.aktif}`">
                                        </div>
                                        <!-- Segera Expired -->
                                        <div v-if="dep.segera_expired > 0"
                                            class="h-full transition-all duration-500"
                                            :style="{ width: pct(dep.segera_expired, dep.total) + '%', background: '#D97706' }"
                                            :title="`Segera Expired: ${dep.segera_expired}`">
                                        </div>
                                        <!-- Expired -->
                                        <div v-if="dep.expired > 0"
                                            class="h-full transition-all duration-500"
                                            :style="{ width: pct(dep.expired, dep.total) + '%', background: '#ED1B2F' }"
                                            :title="`Expired: ${dep.expired}`">
                                        </div>
                                        <!-- Pending -->
                                        <div v-if="dep.pending > 0"
                                            class="h-full transition-all duration-500"
                                            :style="{ width: pct(dep.pending, dep.total) + '%', background: '#6B7280' }"
                                            :title="`Pending: ${dep.pending}`">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0 text-right w-16">
                                <span class="text-xs font-bold" style="color:#111827;">{{ dep.total }}</span>
                                <span class="text-[10px] font-medium" style="color:#9CA3AF;"> dok</span>
                            </div>
                        </div>
                        <!-- Breakdown mini per status -->
                        <div class="flex items-center gap-3 pl-27">
                            <div class="w-24 shrink-0"></div>
                            <div class="flex-1 flex gap-3 flex-wrap">
                                <span v-if="dep.aktif > 0" class="text-[10px] font-semibold" style="color:#ACC42A;">{{ dep.aktif }} aktif</span>
                                <span v-if="dep.segera_expired > 0" class="text-[10px] font-semibold" style="color:#D97706;">{{ dep.segera_expired }} segera exp</span>
                                <span v-if="dep.expired > 0" class="text-[10px] font-semibold" style="color:#ED1B2F;">{{ dep.expired }} expired</span>
                                <span v-if="dep.pending > 0" class="text-[10px] font-semibold" style="color:#6B7280;">{{ dep.pending }} pending</span>
                                <span v-if="dep.total === 0" class="text-[10px] font-medium" style="color:#9CA3AF;">Tidak ada dokumen</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Row 3: 3 Panel ─────────────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            <!-- Menunggu Approval -->
            <div class="card-elevated rounded-xl flex flex-col">
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #F3F4F6;">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#F3F4F6;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#6B7280;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold" style="color:#111827;">Menunggu Approval</h3>
                        <span v-if="stats.pending_approval > 0" class="text-[10px] font-bold px-1.5 py-0.5 rounded-full text-white" style="background:#6B7280;">
                            {{ stats.pending_approval }}
                        </span>
                    </div>
                    <RouterLink :to="{ name: 'admin.approval' }"
                        class="text-xs font-semibold px-2.5 py-1 rounded-lg transition-colors" style="color:#ED1B2F;"
                        onmouseover="this.style.background='#FEE2E2'" onmouseout="this.style.background='transparent'">
                        Lihat Semua →
                    </RouterLink>
                </div>
                <div v-if="isLoading" class="divide-y" style="border-color:#F3F4F6;">
                    <div v-for="i in 3" :key="i" class="px-5 py-3.5 animate-pulse flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-200 shrink-0"></div>
                        <div class="flex-1 space-y-1.5">
                            <div class="h-2.5 bg-gray-200 rounded w-3/4"></div>
                            <div class="h-2 bg-gray-100 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
                <div v-else-if="pendingDocuments.length === 0" class="flex-1 flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center mb-2" style="background:#F7FEE7;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ACC42A;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-medium" style="color:#6B7280;">Tidak ada yang menunggu</p>
                </div>
                <ul v-else class="flex-1">
                    <li v-for="doc in pendingDocuments" :key="doc.id"
                        class="pending-row px-5 py-3.5 cursor-pointer"
                        @click="router.push({ name: 'admin.approval' })">
                        <div class="flex items-start gap-3">
                            <img
                                v-if="doc.user_avatar"
                                :src="doc.user_avatar"
                                :alt="doc.user_name"
                                class="w-8 h-8 rounded-lg object-cover shrink-0"
                            />
                            <div v-else class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-xs font-bold text-white" style="background:#ED1B2F;">
                                {{ doc.user_name?.charAt(0)?.toUpperCase() ?? '?' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold truncate" style="color:#111827;">{{ doc.user_name }}</p>
                                <p class="text-xs mt-0.5 truncate" style="color:#6B7280;">{{ doc.user_department }} · {{ doc.certification_type }}</p>
                                <p class="text-xs mt-1 flex items-center gap-1" style="color:#9CA3AF;">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ timeAgo(doc.created_at) }}
                                </p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Segera Kadaluarsa -->
            <div class="card-elevated rounded-xl flex flex-col">
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #F3F4F6;">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#FEF3C7;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#D97706;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold" style="color:#111827;">Segera Kadaluarsa</h3>
                    </div>
                    <RouterLink :to="{ name: 'admin.dokumen' }"
                        class="text-xs font-semibold px-2.5 py-1 rounded-lg transition-colors" style="color:#ED1B2F;"
                        onmouseover="this.style.background='#FEE2E2'" onmouseout="this.style.background='transparent'">
                        Lihat Semua →
                    </RouterLink>
                </div>
                <div v-if="isLoading" class="divide-y" style="border-color:#F3F4F6;">
                    <div v-for="i in 3" :key="i" class="px-5 py-3.5 animate-pulse flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-200 shrink-0"></div>
                        <div class="flex-1 space-y-1.5">
                            <div class="h-2.5 bg-gray-200 rounded w-3/4"></div>
                            <div class="h-2 bg-gray-100 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
                <div v-else-if="expiringSoon.length === 0" class="flex-1 flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center mb-2" style="background:#F7FEE7;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ACC42A;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-medium" style="color:#6B7280;">Semua dokumen aman</p>
                </div>
                <ul v-else class="flex-1">
                    <li v-for="doc in expiringSoon" :key="doc.id" class="expiry-row px-5 py-3.5">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" :style="{ background: statusConfig(doc.status).bgLight }">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color: statusConfig(doc.status).color }">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold truncate" style="color:#111827;">{{ doc.user_name }}</p>
                                <p class="text-xs mt-0.5 truncate" style="color:#6B7280;">{{ doc.certification_type }}</p>
                                <p class="text-xs mt-1 flex items-center gap-1" style="color:#9CA3AF;">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Exp: {{ formatDate(doc.expiry_date) }}
                                    <span v-if="daysLeft(doc.expiry_date) >= 0"> · {{ daysLeft(doc.expiry_date) }} hari lagi</span>
                                    <span v-else style="color:#ED1B2F;"> · Sudah expired</span>
                                </p>
                            </div>
                            <span class="shrink-0 mt-0.5 text-[10px] font-semibold px-1.5 py-0.5 rounded-full" :style="statusConfig(doc.status).badge">
                                {{ statusConfig(doc.status).label }}
                            </span>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Aktivitas Terkini -->
            <div class="card-elevated rounded-xl flex flex-col">
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #F3F4F6;">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#EFF6FF;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#006CB8;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold" style="color:#111827;">Aktivitas Terkini</h3>
                    </div>
                    <RouterLink :to="{ name: 'admin.audit' }"
                        class="text-xs font-semibold px-2.5 py-1 rounded-lg transition-colors" style="color:#ED1B2F;"
                        onmouseover="this.style.background='#FEE2E2'" onmouseout="this.style.background='transparent'">
                        Lihat Semua →
                    </RouterLink>
                </div>
                <div v-if="isLoading" class="divide-y" style="border-color:#F3F4F6;">
                    <div v-for="i in 3" :key="i" class="px-5 py-3.5 animate-pulse flex items-center gap-3">
                        <div class="w-16 h-5 bg-gray-200 rounded shrink-0"></div>
                        <div class="flex-1 space-y-1.5">
                            <div class="h-2.5 bg-gray-200 rounded w-3/4"></div>
                            <div class="h-2 bg-gray-100 rounded w-1/3"></div>
                        </div>
                    </div>
                </div>
                <div v-else-if="recentActivities.length === 0" class="flex-1 flex flex-col items-center justify-center py-10 text-center">
                    <p class="text-xs font-medium" style="color:#6B7280;">Belum ada aktivitas</p>
                </div>
                <ul v-else class="flex-1">
                    <li v-for="act in recentActivities" :key="act.id" class="activity-row px-5 py-3.5">
                        <div class="flex items-start gap-3">
                            <span class="shrink-0 mt-0.5 text-[10px] font-bold px-1.5 py-0.5 rounded whitespace-nowrap" :style="activityBadge(act.activity_type).style">
                                {{ activityBadge(act.activity_type).label }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold truncate" style="color:#111827;">{{ act.user_name }}</p>
                                <p class="text-[10px] mt-0.5" style="color:#9CA3AF;">{{ timeAgo(act.created_at) }}</p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useFaq } from '@/composables/useFaq.js';
import axios from 'axios';

const auth   = useAuthStore();
const router = useRouter();

const isLoading         = ref(true);
const stats             = ref({});
const pendingDocuments  = ref([]);
const expiringSoon      = ref([]);
const departmentStats   = ref([]);
const recentActivities  = ref([]);

// ── Stat cards config ─────────────────────────────────
const statCards = [
    { key: 'total_pegawai',    label: 'Total Pegawai',    accentColor: '#006CB8', clickable: true,  icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
    { key: 'total_dokumen',    label: 'Total Dokumen',    accentColor: '#374151', clickable: false, icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { key: 'pending_approval', label: 'Pending Approval', accentColor: '#6B7280', clickable: false, icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { key: 'expired',          label: 'Expired',          accentColor: '#ED1B2F', clickable: false, icon: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { key: 'segera_expired',   label: 'Segera Expired',   accentColor: '#D97706', clickable: false, icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { key: 'aktif',            label: 'Aktif',            accentColor: '#ACC42A', clickable: false, icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
];

// ── Donut chart config ────────────────────────────────
const CIRCUMFERENCE = 2 * Math.PI * 70; // 439.82

const donutSlices = computed(() => {
    const total = stats.value.total_dokumen ?? 0;
    if (!total) return [];

    const segments = [
        { key: 'aktif',          label: 'Aktif',          color: '#ACC42A' },
        { key: 'segera_expired', label: 'Segera Expired', color: '#D97706' },
        { key: 'expired',        label: 'Expired',        color: '#ED1B2F' },
        { key: 'pending_approval', label: 'Pending',      color: '#6B7280' },
    ].filter(s => (stats.value[s.key] ?? 0) > 0);

    let offsetAngle = 0;
    return segments.map(s => {
        const count = stats.value[s.key] ?? 0;
        const dash  = (count / total) * CIRCUMFERENCE;
        const slice = { ...s, count, dash, offset: CIRCUMFERENCE - offsetAngle };
        offsetAngle += dash;
        return slice;
    });
});

// ── Bar chart legend ──────────────────────────────────
const barLegend = [
    { label: 'Aktif',    color: '#ACC42A' },
    { label: 'Segera',   color: '#D97706' },
    { label: 'Expired',  color: '#ED1B2F' },
    { label: 'Pending',  color: '#6B7280' },
];

function pct(val, total) {
    if (!total) return 0;
    return Math.max((val / total) * 100, val > 0 ? 2 : 0); // min 2% agar tetap terlihat
}

// ── Aktivitas badge ───────────────────────────────────
const activityMap = {
    dokumen_approve:        { label: 'Disetujui',     style: 'background:#F7FEE7; color:#5a6e0f;' },
    dokumen_reject:         { label: 'Ditolak',       style: 'background:#FCE7F3; color:#DB2777;' },
    dokumen_upload:         { label: 'Upload',        style: 'background:#EFF6FF; color:#006CB8;' },
    dokumen_edit:           { label: 'Edit Dok',      style: 'background:#FEF3C7; color:#92400E;' },
    dokumen_hapus:          { label: 'Hapus Dok',     style: 'background:#FEE2E2; color:#991B1B;' },
    pegawai_tambah:         { label: 'Pegawai Baru',  style: 'background:#EFF6FF; color:#006CB8;' },
    pegawai_edit:           { label: 'Edit Pegawai',  style: 'background:#FEF3C7; color:#92400E;' },
    pegawai_hapus:          { label: 'Hapus Pegawai', style: 'background:#FEE2E2; color:#991B1B;' },
    pegawai_toggle_active:  { label: 'Toggle Aktif',  style: 'background:#FEF3C7; color:#92400E;' },
    pegawai_reset_password: { label: 'Reset Pwd',     style: 'background:#F3F4F6; color:#6B7280;' },
    admin_tambah:           { label: 'Admin Baru',    style: 'background:#EFF6FF; color:#006CB8;' },
    admin_hapus:            { label: 'Hapus Admin',   style: 'background:#FEE2E2; color:#991B1B;' },
};

function activityBadge(type) {
    return activityMap[type] ?? { label: type?.replace(/_/g, ' ') ?? 'Aktivitas', style: 'background:#F3F4F6; color:#6B7280;' };
}

// ── Helpers ───────────────────────────────────────────
const todayDate = computed(() =>
    new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
);

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function daysLeft(dateStr) {
    if (!dateStr) return 0;
    return Math.floor((new Date(dateStr) - new Date()) / 86400000);
}

function timeAgo(iso) {
    if (!iso) return '';
    const diff = Math.floor((Date.now() - new Date(iso)) / 1000);
    if (diff < 60)    return `${diff}d lalu`;
    if (diff < 3600)  return `${Math.floor(diff / 60)}m lalu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}j lalu`;
    return `${Math.floor(diff / 86400)} hari lalu`;
}

function statusConfig(status) {
    const map = {
        segera_expired: { label: 'Segera Expired', color: '#D97706', bgLight: '#FEF3C7', badge: 'background:#FEF3C7; color:#92400E;' },
        expired:        { label: 'Expired',         color: '#ED1B2F', bgLight: '#FEE2E2', badge: 'background:#FEE2E2; color:#991B1B;' },
    };
    return map[status] ?? { label: status, color: '#6B7280', bgLight: '#F3F4F6', badge: 'background:#F3F4F6; color:#6B7280;' };
}

// ── Fetch ─────────────────────────────────────────────
async function fetchDashboard() {
    isLoading.value = true;
    try {
        const { data } = await axios.get('/api/admin/dashboard');
        stats.value            = data.stats;
        pendingDocuments.value = data.pending_documents;
        expiringSoon.value     = data.expiring_soon;
        departmentStats.value  = data.department_stats ?? [];
        recentActivities.value = data.recent_activities ?? [];
    } catch (e) {
        console.error('Dashboard admin error:', e);
    } finally {
        isLoading.value = false;
    }
}

onMounted(fetchDashboard);

useFaq([
    { q: 'Cara membaca grafik sebaran departemen?', a: 'Setiap baris menampilkan satu departemen. Bar warna menunjukkan komposisi status dokumen: hijau = aktif, oranye = segera expired, merah = expired, abu = pending.', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { q: 'Cara approve dokumen pegawai?', a: 'Buka menu Approval di sidebar, pilih dokumen yang ingin ditinjau, lalu klik Setujui atau Tolak dengan alasan yang jelas.', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { q: 'Cara menambah pegawai baru?', a: 'Buka menu Pegawai di sidebar, klik tombol Tambah Pegawai, lalu isi data lengkap termasuk departemen dan nomor pegawai.', icon: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z' },
]);
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
.pending-row, .expiry-row, .activity-row {
    border-bottom: 1px solid #F3F4F6;
    transition: background 150ms;
}
.pending-row:last-child, .expiry-row:last-child, .activity-row:last-child { border-bottom: none; }
.pending-row:hover, .activity-row:hover { background: #F9FAFB; }
</style>
