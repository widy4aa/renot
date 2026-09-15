<template>
    <div class="space-y-5">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold" style="color:#111827;">Semua Dokumen</h2>
                <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">Kelola seluruh dokumen sertifikasi pegawai</p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="exportExcel"
                    :disabled="isExporting"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold card-elevated transition-colors disabled:opacity-50"
                    style="color:#374151;"
                >
                    <svg v-if="!isExporting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ACC42A;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" style="color:#9CA3AF;">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    {{ isExporting ? 'Mengexport...' : 'Export Excel' }}
                </button>
                <RouterLink
                    :to="{ name: 'admin.dokumen.tambah' }"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors"
                    style="background:#ED1B2F;"
                    onmouseover="this.style.background='#c8102e'"
                    onmouseout="this.style.background='#ED1B2F'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Dokumen
                </RouterLink>
            </div>
        </div>

        <!-- ── Filter Bar ─────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-5 py-4 space-y-3">
            <!-- Baris 1: Search + counter + toggle view -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                        </svg>
                    </span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama pegawai, nama sertifikasi, atau nomor sertifikat..."
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl text-sm font-medium outline-none transition-all"
                        style="background:#F9FAFB; border:1.5px solid #ED1B2F; color:#111827;"
                        @focus="e => e.target.style.borderColor='#c8102e'"
                        @blur="e => e.target.style.borderColor='#ED1B2F'"
                    />
                </div>
                <span class="text-xs font-semibold shrink-0" style="color:#6B7280;">
                    {{ filteredDocs.length }} dokumen
                </span>
                <div class="w-px h-6 shrink-0" style="background:#E2E8F0;"></div>
                <div class="flex items-center gap-1 shrink-0">
                    <button @click="viewMode = 'grid'" class="w-8 h-8 rounded-lg flex items-center justify-center transition-all"
                        :style="viewMode === 'grid' ? 'background:#ED1B2F; color:#fff;' : 'background:#F3F4F6; color:#6B7280;'" title="Grid view">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </button>
                    <button @click="viewMode = 'list'" class="w-8 h-8 rounded-lg flex items-center justify-center transition-all"
                        :style="viewMode === 'list' ? 'background:#ED1B2F; color:#fff;' : 'background:#F3F4F6; color:#6B7280;'" title="List view">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>

            <!-- Baris 2: Dropdown filter -->
            <div class="flex items-center gap-2 flex-wrap">
                <select v-model="filterStatus" class="px-3 py-2 rounded-xl text-xs font-semibold outline-none cursor-pointer"
                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="segera_expired">Segera Expired</option>
                    <option value="expired">Expired</option>
                    <option value="pending_approval">Pending Approval</option>
                    <option value="ditolak">Ditolak</option>
                </select>

                <select v-model="filterKategori" class="px-3 py-2 rounded-xl text-xs font-semibold outline-none cursor-pointer"
                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'">
                    <option value="">Semua Kategori</option>
                    <option v-for="k in kategoriOptions" :key="k" :value="k">{{ k }}</option>
                </select>

                <select v-model="filterJenis" class="px-3 py-2 rounded-xl text-xs font-semibold outline-none cursor-pointer"
                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'">
                    <option value="">Semua Jenis</option>
                    <option v-for="j in jenisOptions" :key="j" :value="j">{{ j }}</option>
                </select>

                <select v-model="filterDepartemen" class="px-3 py-2 rounded-xl text-xs font-semibold outline-none cursor-pointer"
                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'">
                    <option value="">Semua Departemen</option>
                    <option v-for="d in departemenOptions" :key="d" :value="d">{{ d }}</option>
                </select>

                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold shrink-0" style="color:#6B7280;">Exp:</span>
                    <input v-model="filterExpFrom" type="date" class="px-3 py-2 rounded-xl text-xs font-semibold outline-none"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                        @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                    <span class="text-xs font-semibold" style="color:#9CA3AF;">—</span>
                    <input v-model="filterExpTo" type="date" class="px-3 py-2 rounded-xl text-xs font-semibold outline-none"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                        @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                </div>

                <button v-if="hasActiveFilter" @click="resetFilters"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold"
                    style="background:#FEE2E2; color:#ED1B2F;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset Filter
                </button>
            </div>
        </div>

        <!-- ── Loading ────────────────────────────────────── -->
        <div v-if="isLoading">
            <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="i in 6" :key="i" class="card-elevated rounded-xl p-5 animate-pulse">
                    <div class="h-1 bg-gray-200 rounded mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded w-2/3 mb-2"></div>
                    <div class="h-3 bg-gray-100 rounded w-1/2 mb-3"></div>
                    <div class="h-3 bg-gray-100 rounded w-3/4 mb-5"></div>
                    <div class="h-6 bg-gray-100 rounded-full w-1/3"></div>
                </div>
            </div>
            <div v-else class="space-y-3">
                <div v-for="i in 5" :key="i" class="card-elevated rounded-xl p-5 animate-pulse flex gap-4">
                    <div class="w-1 bg-gray-200 rounded shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                        <div class="h-3 bg-gray-100 rounded w-1/2"></div>
                        <div class="h-3 bg-gray-100 rounded w-1/4"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Kosong ─────────────────────────────────────── -->
        <div v-else-if="filteredDocs.length === 0" class="card-elevated rounded-xl py-16 text-center">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#F3F4F6;">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#D1D5DB;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-sm font-bold" style="color:#374151;">Tidak ada dokumen ditemukan</p>
            <p class="text-xs mt-1 font-medium" style="color:#9CA3AF;">
                {{ hasActiveFilter ? 'Coba ubah atau reset filter' : 'Belum ada dokumen di sistem' }}
            </p>
            <button v-if="hasActiveFilter" @click="resetFilters" class="mt-3 text-xs font-bold" style="color:#ED1B2F;">Reset Filter</button>
        </div>

        <!-- ── Grid View ──────────────────────────────────── -->
        <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="doc in filteredDocs" :key="doc.id"
                class="card-elevated rounded-xl overflow-hidden flex flex-col cursor-pointer doc-card"
                @click="goToDetail(doc.id)">
                <div class="h-1 w-full shrink-0" :style="{ background: getStatusConfig(doc.status).accentColor }"></div>
                <div class="p-5 flex flex-col flex-1">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <h3 class="text-sm font-bold leading-snug" style="color:#111827;">{{ doc.certification_type?.name }}</h3>
                        <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold"
                            :style="getStatusConfig(doc.status).badgeStyle">{{ getStatusConfig(doc.status).label }}</span>
                    </div>
                    <p class="text-xs font-semibold mb-2" style="color:#6B7280;">{{ doc.certification_type?.category?.name }}</p>

                    <!-- Info pegawai -->
                    <div class="flex items-center gap-1.5 mb-3 px-2.5 py-1.5 rounded-lg" style="background:#F9FAFB;">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#9CA3AF;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-xs font-semibold truncate" style="color:#374151;">{{ doc.user?.name }}</span>
                        <span class="text-xs shrink-0" style="color:#D1D5DB;">·</span>
                        <span class="text-xs font-medium shrink-0" style="color:#6B7280;">{{ doc.user?.department?.name }}</span>
                    </div>

                    <p v-if="doc.certificate_number" class="text-xs font-medium mb-2" style="color:#9CA3AF;">No. {{ doc.certificate_number }}</p>

                    <div class="flex flex-col gap-1 mb-4 flex-1">
                        <div class="flex items-center gap-1.5 text-xs font-semibold" style="color:#6B7280;">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Exp: <span class="font-bold" :style="{ color: getStatusConfig(doc.status).accentColor }">{{ formatDate(doc.expiry_date) }}</span>
                        </div>
                        <p v-if="['aktif','segera_expired'].includes(doc.status)" class="text-xs font-bold ml-5" :style="{ color: getStatusConfig(doc.status).accentColor }">
                            {{ daysUntilExpiry(doc.expiry_date) }} hari lagi
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 pt-3 border-t flex-wrap" style="border-color:#F3F4F6;" @click.stop>
                        <RouterLink :to="{ name: 'admin.dokumen.detail', params: { id: doc.id } }"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold"
                            style="background:#EFF6FF; color:#006CB8;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Detail
                        </RouterLink>
                        <button v-if="doc.status === 'pending_approval'" @click="openApprove(doc)"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold"
                            style="background:#F7FEE7; color:#5a6e0f;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Approve
                        </button>
                        <button @click="confirmDelete(doc)"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold"
                            style="background:#FEE2E2; color:#ED1B2F;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── List View ──────────────────────────────────── -->
        <div v-else class="space-y-2">
            <div v-for="doc in filteredDocs" :key="doc.id" class="card-elevated rounded-xl overflow-hidden doc-card">
                <div class="flex">
                    <!-- Accent bar kiri -->
                    <div class="w-1 shrink-0" :style="{ background: getStatusConfig(doc.status).accentColor }"></div>

                    <!-- KIRI: Identitas dokumen -->
                    <div class="flex-1 px-4 py-3 min-w-0 flex flex-col justify-center">
                        <h3 class="text-sm font-bold leading-snug" style="color:#111827;">{{ doc.certification_type?.name }}</h3>
                        <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                            <span class="text-xs font-semibold px-1.5 py-0.5 rounded" style="background:#F3F4F6; color:#6B7280;">{{ doc.certification_type?.category?.name }}</span>
                            <template v-if="doc.certificate_number">
                                <span class="text-xs" style="color:#D1D5DB;">·</span>
                                <span class="text-xs" style="color:#9CA3AF;">No. <span class="font-semibold" style="color:#6B7280;">{{ doc.certificate_number }}</span></span>
                            </template>
                        </div>
                        <div class="flex items-center gap-1 mt-1.5 flex-wrap">
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#9CA3AF;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-xs font-medium" style="color:#374151;">{{ doc.user?.name }}</span>
                            <span class="text-xs" style="color:#D1D5DB;">·</span>
                            <span class="text-xs" style="color:#6B7280;">{{ doc.user?.department?.name }}</span>
                            <template v-if="doc.user?.employee_number">
                                <span class="text-xs" style="color:#D1D5DB;">·</span>
                                <span class="text-xs" style="color:#9CA3AF;">{{ doc.user.employee_number }}</span>
                            </template>
                        </div>
                    </div>

                    <!-- TENGAH: Info tanggal & countdown -->
                    <div class="w-36 shrink-0 border-l flex flex-col justify-center px-4 py-3" style="border-color:#F3F4F6;">
                        <p class="text-xs font-medium" style="color:#9CA3AF;">Kadaluarsa</p>
                        <p class="text-xs font-bold mt-0.5" :style="{ color: getStatusConfig(doc.status).accentColor }">{{ formatDate(doc.expiry_date) }}</p>
                        <template v-if="['aktif','segera_expired'].includes(doc.status)">
                            <div class="flex items-baseline gap-1 mt-1">
                                <span class="text-lg font-black leading-none" :style="{ color: getStatusConfig(doc.status).accentColor }">{{ daysUntilExpiry(doc.expiry_date) }}</span>
                                <span class="text-xs font-medium" style="color:#9CA3AF;">hari lagi</span>
                            </div>
                        </template>
                        <template v-else-if="doc.status === 'expired'">
                            <p class="text-xs font-bold mt-1" style="color:#ED1B2F;">Sudah Kadaluarsa</p>
                        </template>
                        <template v-else-if="doc.status === 'pending_approval'">
                            <p class="text-xs font-semibold mt-1" style="color:#6B7280;">Menunggu Approval</p>
                        </template>
                    </div>

                    <!-- KANAN: Status badge + tombol aksi 2x2 -->
                    <div class="w-44 shrink-0 border-l flex flex-col justify-center gap-1.5 px-3 py-3" style="border-color:#F3F4F6;">
                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold self-center mb-0.5"
                            :style="getStatusConfig(doc.status).badgeStyle">{{ getStatusConfig(doc.status).label }}</span>
                        <!-- Baris 1: Lihat Detail + Approve/Edit -->
                        <div class="grid grid-cols-2 gap-1">
                            <RouterLink :to="{ name: 'admin.dokumen.detail', params: { id: doc.id } }"
                                class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold"
                                style="background:#EFF6FF; color:#006CB8;">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Detail
                            </RouterLink>
                            <button v-if="doc.status === 'pending_approval'" @click="openApprove(doc)"
                                class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold"
                                style="background:#F7FEE7; color:#5a6e0f;">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Approve
                            </button>
                            <RouterLink v-else :to="{ name: 'admin.dokumen.edit', params: { id: doc.id } }"
                                class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold"
                                style="background:#F3F4F6; color:#374151;">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </RouterLink>
                        </div>
                        <!-- Baris 2: Edit (jika ada approve di baris 1) + Hapus -->
                        <div class="grid gap-1" :class="doc.status === 'pending_approval' ? 'grid-cols-2' : 'grid-cols-1'">
                            <RouterLink v-if="doc.status === 'pending_approval'" :to="{ name: 'admin.dokumen.edit', params: { id: doc.id } }"
                                class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold"
                                style="background:#F3F4F6; color:#374151;">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </RouterLink>
                            <button @click="confirmDelete(doc)"
                                class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold"
                                style="background:#FEE2E2; color:#ED1B2F;">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Modal Approve ──────────────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="docToApprove" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="docToApprove = null">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#F7FEE7;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#5a6e0f;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Setujui Dokumen?</h4>
                    <p class="text-sm text-center mt-2" style="color:#6B7280;">
                        Dokumen <span class="font-bold" style="color:#111827;">{{ docToApprove?.certification_type?.name }}</span> milik <span class="font-bold" style="color:#111827;">{{ docToApprove?.user?.name }}</span> akan disetujui. Status akan dihitung dari tanggal kadaluarsa.
                    </p>
                    <div class="flex gap-3 mt-6">
                        <button @click="docToApprove = null" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="approveDoc" :disabled="isProcessing" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                            style="background:#ACC42A;" onmouseover="this.style.background='#94a81d'" onmouseout="this.style.background='#ACC42A'">
                            {{ isProcessing ? 'Memproses...' : 'Ya, Setujui' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ── Modal Hapus ────────────────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="docToDelete" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="docToDelete = null">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#FEE2E2;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Hapus Dokumen?</h4>
                    <p class="text-sm text-center mt-2" style="color:#6B7280;">
                        Dokumen <span class="font-bold" style="color:#111827;">{{ docToDelete?.certification_type?.name }}</span> milik <span class="font-bold" style="color:#111827;">{{ docToDelete?.user?.name }}</span> akan dihapus permanen.
                    </p>
                    <div class="flex gap-3 mt-6">
                        <button @click="docToDelete = null" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="deleteDoc" :disabled="isProcessing" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                            style="background:#ED1B2F;" onmouseover="this.style.background='#c8102e'" onmouseout="this.style.background='#ED1B2F'">
                            {{ isProcessing ? 'Menghapus...' : 'Ya, Hapus' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useDocumentHelpers } from '@/composables/useDocumentHelpers.js';
import { useFaq } from '@/composables/useFaq.js';
import axios from 'axios';

const router = useRouter();
const { getStatusConfig, formatDate, daysUntilExpiry } = useDocumentHelpers();

const isLoading   = ref(true);
const isExporting = ref(false);
const isProcessing = ref(false);
const allDocs     = ref([]);
const viewMode    = ref('list');
const docToApprove = ref(null);
const docToDelete  = ref(null);

// ── Filter state ───────────────────────────────────────
const search           = ref('');
const filterStatus     = ref('');
const filterKategori   = ref('');
const filterJenis      = ref('');
const filterDepartemen = ref('');
const filterExpFrom    = ref('');
const filterExpTo      = ref('');

// ── Dropdown options dinamis ───────────────────────────
const kategoriOptions = computed(() => [...new Set(allDocs.value.map(d => d.certification_type?.category?.name).filter(Boolean))].sort());
const jenisOptions    = computed(() => {
    const docs = filterKategori.value ? allDocs.value.filter(d => d.certification_type?.category?.name === filterKategori.value) : allDocs.value;
    return [...new Set(docs.map(d => d.certification_type?.name).filter(Boolean))].sort();
});
const departemenOptions = computed(() => [...new Set(allDocs.value.map(d => d.user?.department?.name).filter(Boolean))].sort());

watch(filterKategori, () => { filterJenis.value = ''; });

const hasActiveFilter = computed(() =>
    !!(search.value || filterStatus.value || filterKategori.value || filterJenis.value || filterDepartemen.value || filterExpFrom.value || filterExpTo.value)
);

// ── Client-side filter ─────────────────────────────────
const filteredDocs = computed(() => {
    let docs = allDocs.value;
    if (search.value) {
        const q = search.value.toLowerCase();
        docs = docs.filter(d =>
            d.user?.name?.toLowerCase().includes(q) ||
            d.certification_type?.name?.toLowerCase().includes(q) ||
            (d.certificate_number ?? '').toLowerCase().includes(q)
        );
    }
    if (filterStatus.value)     docs = docs.filter(d => d.status === filterStatus.value);
    if (filterKategori.value)   docs = docs.filter(d => d.certification_type?.category?.name === filterKategori.value);
    if (filterJenis.value)      docs = docs.filter(d => d.certification_type?.name === filterJenis.value);
    if (filterDepartemen.value) docs = docs.filter(d => d.user?.department?.name === filterDepartemen.value);
    if (filterExpFrom.value)    docs = docs.filter(d => d.expiry_date && d.expiry_date >= filterExpFrom.value);
    if (filterExpTo.value)      docs = docs.filter(d => d.expiry_date && d.expiry_date <= filterExpTo.value);
    return docs;
});

function resetFilters() {
    search.value = filterStatus.value = filterKategori.value = '';
    filterJenis.value = filterDepartemen.value = filterExpFrom.value = filterExpTo.value = '';
}

// ── Fetch ──────────────────────────────────────────────
async function fetchDocuments() {
    isLoading.value = true;
    try {
        const { data } = await axios.get('/api/admin/documents');
        allDocs.value = data.data;
    } catch {
        allDocs.value = [];
    } finally {
        isLoading.value = false;
    }
}

function goToDetail(id) { router.push({ name: 'admin.dokumen.detail', params: { id } }); }
function openApprove(doc) { docToApprove.value = doc; }
function confirmDelete(doc) { docToDelete.value = doc; }

async function approveDoc() {
    if (!docToApprove.value) return;
    isProcessing.value = true;
    try {
        const { data } = await axios.post(`/api/admin/documents/${docToApprove.value.id}/approve`);
        const idx = allDocs.value.findIndex(d => d.id === docToApprove.value.id);
        if (idx !== -1) allDocs.value[idx] = data.document;
        docToApprove.value = null;
    } catch { /**/ } finally {
        isProcessing.value = false;
    }
}

async function deleteDoc() {
    if (!docToDelete.value) return;
    isProcessing.value = true;
    try {
        await axios.delete(`/api/admin/documents/${docToDelete.value.id}`);
        allDocs.value = allDocs.value.filter(d => d.id !== docToDelete.value.id);
        docToDelete.value = null;
    } catch { /**/ } finally {
        isProcessing.value = false;
    }
}

async function exportExcel() {
    isExporting.value = true;
    try {
        const params = new URLSearchParams();
        if (filterStatus.value)     params.set('status', filterStatus.value);
        if (filterDepartemen.value) params.set('departemen', filterDepartemen.value);
        if (filterKategori.value)   params.set('kategori', filterKategori.value);
        if (filterJenis.value)      params.set('jenis', filterJenis.value);
        if (search.value)           params.set('search', search.value);
        if (filterExpFrom.value)    params.set('exp_from', filterExpFrom.value);
        if (filterExpTo.value)      params.set('exp_to', filterExpTo.value);

        const token = localStorage.getItem('auth_token');
        const url   = `/api/admin/documents/export${params.toString() ? '?' + params.toString() : ''}`;
        const response = await axios.get(url, {
            responseType: 'blob',
            headers: { Authorization: `Bearer ${token}` },
        });

        const disposition = response.headers['content-disposition'] ?? '';
        const match = disposition.match(/filename="?([^";]+)"?/);
        const filename = match ? match[1] : 'semua-dokumen.xlsx';

        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.click();
        URL.revokeObjectURL(link.href);
    } catch {
        alert('Gagal mengexport dokumen. Coba lagi.');
    } finally {
        isExporting.value = false;
    }
}

onMounted(fetchDocuments);

useFaq([
    { q: 'Cara approve dokumen pegawai?', a: 'Klik tombol "Approve" pada dokumen berstatus Pending, atau buka Detail dokumen lalu klik Setujui. Status otomatis dihitung dari tanggal kadaluarsa.', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { q: 'Cara filter dokumen per departemen?', a: 'Gunakan dropdown "Semua Departemen" di filter bar untuk menyaring dokumen berdasarkan departemen pegawai.', icon: 'M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z' },
    { q: 'Export Excel mengikuti filter?', a: 'Ya. Export Excel akan menghasilkan file sesuai filter yang sedang aktif. Reset filter dulu jika ingin export semua data.', icon: 'M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
]);
</script>

<style scoped>
.doc-card { transition: box-shadow 150ms ease; }
.doc-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.10), 0 2px 6px rgba(0,0,0,0.06) !important; }
</style>
