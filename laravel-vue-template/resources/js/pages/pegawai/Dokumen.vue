<template>
    <div class="space-y-5">

        <!-- Header -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold" style="color:#111827;">Dokumen Saya</h2>
                <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">Kelola sertifikasi dan dokumen Anda</p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="exportExcel"
                    :disabled="isExporting"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold card-elevated transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    style="color:#374151;"
                >
                    <svg v-if="!isExporting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#16A34A;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" style="color:#9CA3AF;">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    {{ isExporting ? 'Mengexport...' : 'Export Excel' }}
                </button>
                <RouterLink
                    :to="{ name: 'pegawai.dokumen.tambah' }"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors"
                    style="background:#ED1B2F;"
                    onmouseover="this.style.background='#c8102e'"
                    onmouseout="this.style.background='#ED1B2F'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Upload Dokumen
                </RouterLink>
            </div>
        </div>

        <!-- ── Filter Bar ─────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-5 py-4 space-y-3">
            <!-- Baris 1: Search + jumlah + toggle view -->
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
                        placeholder="Cari nama sertifikasi atau nomor sertifikat..."
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

                <!-- Toggle List / Grid -->
                <div class="flex items-center gap-1 shrink-0">
                    <button
                        @click="viewMode = 'grid'"
                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-all"
                        :style="viewMode === 'grid' ? 'background:#ED1B2F; color:#fff;' : 'background:#F3F4F6; color:#6B7280;'"
                        title="Grid view"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </button>
                    <button
                        @click="viewMode = 'list'"
                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-all"
                        :style="viewMode === 'list' ? 'background:#ED1B2F; color:#fff;' : 'background:#F3F4F6; color:#6B7280;'"
                        title="List view"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Baris 2: Dropdown filter + range kadaluarsa + reset -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Status -->
                <select
                    v-model="filterStatus"
                    class="px-3 py-2 rounded-xl text-xs font-semibold outline-none cursor-pointer transition-all"
                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                    @focus="e => e.target.style.borderColor='#ED1B2F'"
                    @blur="e => e.target.style.borderColor='#E2E8F0'"
                >
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="segera_expired">Segera Expired</option>
                    <option value="expired">Expired</option>
                    <option value="pending_approval">Pending</option>
                    <option value="ditolak">Ditolak</option>
                </select>

                <!-- Kategori -->
                <select
                    v-model="filterKategori"
                    class="px-3 py-2 rounded-xl text-xs font-semibold outline-none cursor-pointer transition-all"
                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                    @focus="e => e.target.style.borderColor='#ED1B2F'"
                    @blur="e => e.target.style.borderColor='#E2E8F0'"
                >
                    <option value="">Semua Kategori</option>
                    <option v-for="k in kategoriOptions" :key="k" :value="k">{{ k }}</option>
                </select>

                <!-- Jenis -->
                <select
                    v-model="filterJenis"
                    class="px-3 py-2 rounded-xl text-xs font-semibold outline-none cursor-pointer transition-all"
                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                    @focus="e => e.target.style.borderColor='#ED1B2F'"
                    @blur="e => e.target.style.borderColor='#E2E8F0'"
                >
                    <option value="">Semua Jenis</option>
                    <option v-for="j in jenisOptions" :key="j" :value="j">{{ j }}</option>
                </select>

                <!-- Range Kadaluarsa -->
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold shrink-0" style="color:#6B7280;">Exp:</span>
                    <input
                        v-model="filterExpFrom"
                        type="date"
                        class="px-3 py-2 rounded-xl text-xs font-semibold outline-none transition-all"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                        @focus="e => e.target.style.borderColor='#ED1B2F'"
                        @blur="e => e.target.style.borderColor='#E2E8F0'"
                    />
                    <span class="text-xs font-semibold" style="color:#9CA3AF;">—</span>
                    <input
                        v-model="filterExpTo"
                        type="date"
                        class="px-3 py-2 rounded-xl text-xs font-semibold outline-none transition-all"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                        @focus="e => e.target.style.borderColor='#ED1B2F'"
                        @blur="e => e.target.style.borderColor='#E2E8F0'"
                    />
                </div>

                <!-- Reset -->
                <button
                    v-if="hasActiveFilter"
                    @click="resetFilters"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition-all"
                    style="background:#FEE2E2; color:#ED1B2F;"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
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
                    <div class="h-3 bg-gray-100 rounded w-1/2 mb-5"></div>
                    <div class="h-6 bg-gray-100 rounded-full w-1/3 mb-5"></div>
                    <div class="flex gap-3"><div class="h-3 bg-gray-100 rounded w-1/3"></div><div class="h-3 bg-gray-100 rounded w-1/3"></div></div>
                </div>
            </div>
            <div v-else class="space-y-3">
                <div v-for="i in 4" :key="i" class="card-elevated rounded-xl p-5 animate-pulse">
                    <div class="flex justify-between mb-4">
                        <div class="space-y-2 flex-1"><div class="h-4 bg-gray-200 rounded w-1/3"></div><div class="h-3 bg-gray-100 rounded w-1/4"></div></div>
                        <div class="h-6 w-24 bg-gray-100 rounded-full"></div>
                    </div>
                    <div class="flex gap-4"><div class="h-3 bg-gray-100 rounded w-1/4"></div><div class="h-3 bg-gray-100 rounded w-1/4"></div></div>
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
                {{ hasActiveFilter ? 'Coba ubah atau reset filter' : 'Belum ada dokumen yang diupload' }}
            </p>
            <button v-if="hasActiveFilter" @click="resetFilters" class="mt-3 text-xs font-bold" style="color:#ED1B2F;">Reset Filter</button>
            <RouterLink v-else :to="{ name: 'pegawai.dokumen.tambah' }" class="mt-3 inline-block text-xs font-bold" style="color:#ED1B2F;">
                Upload dokumen pertama Anda →
            </RouterLink>
        </div>

        <!-- ── Grid View ──────────────────────────────────── -->
        <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="doc in filteredDocs"
                :key="doc.id"
                class="card-elevated rounded-xl overflow-hidden flex flex-col cursor-pointer doc-card"
                @click="goToDetail(doc.id)"
            >
                <!-- Accent bar atas -->
                <div class="h-1 w-full shrink-0" :style="{ background: getStatusConfig(doc.status).accentColor }"></div>

                <div class="p-5 flex flex-col flex-1">
                    <!-- Nama + Badge -->
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <h3 class="text-sm font-bold leading-snug" style="color:#111827;">
                            {{ doc.certification_type?.name }}
                        </h3>
                        <span
                            class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold"
                            :style="getStatusConfig(doc.status).badgeStyle"
                        >{{ getStatusConfig(doc.status).label }}</span>
                    </div>

                    <!-- Kategori -->
                    <p class="text-xs font-semibold mb-3" style="color:#6B7280;">
                        {{ doc.certification_type?.category?.name ?? '—' }}
                    </p>

                    <!-- No. sertifikat -->
                    <p v-if="doc.certificate_number" class="text-xs font-medium mb-2" style="color:#9CA3AF;">
                        No. {{ doc.certificate_number }}
                    </p>

                    <!-- Tanggal -->
                    <div class="flex flex-col gap-1 mb-4 flex-1">
                        <div class="flex items-center gap-1.5 text-xs font-semibold" style="color:#6B7280;">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Exp: <span class="font-bold" :style="{ color: getStatusConfig(doc.status).accentColor }">{{ formatDate(doc.expiry_date) }}</span>
                        </div>
                        <p v-if="['aktif','segera_expired'].includes(doc.status)" class="text-xs font-bold ml-5" :style="{ color: getStatusConfig(doc.status).accentColor }">
                            {{ daysUntilExpiry(doc.expiry_date) }} hari lagi
                        </p>
                    </div>

                    <!-- Alasan tolak -->
                    <div v-if="doc.status === 'ditolak' && doc.rejection_reason" class="text-xs rounded-lg px-3 py-2 mb-3 font-medium" style="background:#FCE7F3; color:#9D174D;">
                        {{ doc.rejection_reason }}
                    </div>

                    <!-- Actions Grid -->
                    <div class="flex items-center gap-2 pt-3 border-t flex-wrap" style="border-color:#F3F4F6;" @click.stop>
                        <RouterLink
                            :to="{ name: 'pegawai.dokumen.detail', params: { id: doc.id } }"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                            style="background:#EFF6FF; color:#006CB8;"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Detail
                        </RouterLink>
                        <RouterLink
                            :to="{ name: 'pegawai.dokumen.edit', params: { id: doc.id } }"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                            style="background:#F3F4F6; color:#374151;"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </RouterLink>
                        <a
                            v-if="doc.has_file"
                            :href="`/api/pegawai/documents/${doc.id}/download`"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                            style="background:#F3F4F6; color:#374151;"
                            target="_blank"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download
                        </a>
                        <button
                            @click="confirmDelete(doc)"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                            style="background:#FEE2E2; color:#ED1B2F;"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── List View ──────────────────────────────────── -->
        <div v-else class="space-y-3">
            <div
                v-for="doc in filteredDocs"
                :key="doc.id"
                class="card-elevated rounded-xl overflow-hidden doc-card"
            >
                <div class="flex">
                    <!-- Accent bar kiri -->
                    <div class="w-1 shrink-0" :style="{ background: getStatusConfig(doc.status).accentColor }"></div>
                    <div class="flex-1 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm font-bold" style="color:#111827;">{{ doc.certification_type?.name }}</h3>
                                    <span class="text-xs" style="color:#D1D5DB;">·</span>
                                    <span class="text-xs font-semibold" style="color:#6B7280;">{{ doc.certification_type?.category?.name }}</span>
                                </div>
                                <p v-if="doc.certificate_number" class="text-xs font-medium mt-0.5" style="color:#9CA3AF;">No. {{ doc.certificate_number }}</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                :style="getStatusConfig(doc.status).badgeStyle"
                            >{{ getStatusConfig(doc.status).label }}</span>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-xs font-semibold" style="color:#6B7280;">
                            <span v-if="doc.issued_date">Terbit: <span class="font-bold" style="color:#374151;">{{ formatDate(doc.issued_date) }}</span></span>
                            <span>Kadaluarsa: <span class="font-bold" :style="{ color: getStatusConfig(doc.status).accentColor }">{{ formatDate(doc.expiry_date) }}</span></span>
                            <span v-if="['aktif','segera_expired'].includes(doc.status)" class="font-bold" :style="{ color: getStatusConfig(doc.status).accentColor }">
                                {{ daysUntilExpiry(doc.expiry_date) }} hari lagi
                            </span>
                        </div>

                        <div v-if="doc.status === 'ditolak' && doc.rejection_reason" class="mt-3 text-xs rounded-lg px-3 py-2 font-medium" style="background:#FCE7F3; color:#9D174D;">
                            <span class="font-bold">Alasan ditolak:</span> {{ doc.rejection_reason }}
                        </div>

                        <!-- Actions List -->
                        <div class="mt-4 flex items-center gap-2 pt-3 border-t flex-wrap" style="border-color:#F3F4F6;">
                            <RouterLink
                                :to="{ name: 'pegawai.dokumen.detail', params: { id: doc.id } }"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                                style="background:#EFF6FF; color:#006CB8;"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Lihat Detail
                            </RouterLink>
                            <RouterLink
                                :to="{ name: 'pegawai.dokumen.edit', params: { id: doc.id } }"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                                style="background:#F3F4F6; color:#374151;"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </RouterLink>
                            <a
                                v-if="doc.has_file"
                                :href="`/api/pegawai/documents/${doc.id}/download`"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                                style="background:#F3F4F6; color:#374151;"
                                target="_blank"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Download
                            </a>
                            <button
                                @click="confirmDelete(doc)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                                style="background:#FEE2E2; color:#ED1B2F;"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Modal Hapus ────────────────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="docToDelete" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40);" @click.self="docToDelete = null">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#FEE2E2;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Hapus Dokumen?</h4>
                    <p class="text-sm text-center mt-2" style="color:#6B7280;">
                        Dokumen <span class="font-bold" style="color:#111827;">{{ docToDelete.certification_type?.name }}</span> akan dihapus permanen beserta file-nya.
                    </p>
                    <div class="flex gap-3 mt-6">
                        <button @click="docToDelete = null" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button
                            @click="deleteDocument"
                            :disabled="isDeleting"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                            style="background:#ED1B2F;"
                            onmouseover="this.style.background='#c8102e'"
                            onmouseout="this.style.background='#ED1B2F'"
                        >{{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}</button>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useDocumentHelpers } from '@/composables/useDocumentHelpers.js';

const route  = useRoute();
const router = useRouter();
const { getStatusConfig, formatDate, daysUntilExpiry } = useDocumentHelpers();

const isLoading   = ref(true);
const allDocs     = ref([]);
const docToDelete = ref(null);
const isDeleting  = ref(false);
const isExporting = ref(false);
const viewMode    = ref('grid');

// ── Filter state ───────────────────────────────────────
const search         = ref('');
const filterStatus   = ref(route.query.status ?? '');
const filterKategori = ref('');
const filterJenis    = ref('');
const filterExpFrom  = ref('');
const filterExpTo    = ref('');

// ── Opsi dropdown dinamis ──────────────────────────────
const kategoriOptions = computed(() => {
    const set = new Set();
    allDocs.value.forEach(d => {
        if (d.certification_type?.category?.name) set.add(d.certification_type.category.name);
    });
    return [...set].sort();
});

const jenisOptions = computed(() => {
    const set = new Set();
    allDocs.value.forEach(d => {
        const kat = d.certification_type?.category?.name;
        if (!filterKategori.value || kat === filterKategori.value) {
            if (d.certification_type?.name) set.add(d.certification_type.name);
        }
    });
    return [...set].sort();
});

// Reset jenis saat kategori berubah
watch(filterKategori, () => { filterJenis.value = ''; });

const hasActiveFilter = computed(() =>
    !!(search.value || filterStatus.value || filterKategori.value ||
    filterJenis.value || filterExpFrom.value || filterExpTo.value)
);

// ── Filtered docs (client-side) ────────────────────────
const filteredDocs = computed(() => {
    let docs = allDocs.value;

    if (search.value) {
        const q = search.value.toLowerCase();
        docs = docs.filter(d =>
            d.certification_type?.name?.toLowerCase().includes(q) ||
            (d.certificate_number ?? '').toLowerCase().includes(q)
        );
    }
    if (filterStatus.value) {
        docs = docs.filter(d => d.status === filterStatus.value);
    }
    if (filterKategori.value) {
        docs = docs.filter(d => d.certification_type?.category?.name === filterKategori.value);
    }
    if (filterJenis.value) {
        docs = docs.filter(d => d.certification_type?.name === filterJenis.value);
    }
    if (filterExpFrom.value) {
        docs = docs.filter(d => d.expiry_date && d.expiry_date >= filterExpFrom.value);
    }
    if (filterExpTo.value) {
        docs = docs.filter(d => d.expiry_date && d.expiry_date <= filterExpTo.value);
    }

    return docs;
});

function resetFilters() {
    search.value         = '';
    filterStatus.value   = '';
    filterKategori.value = '';
    filterJenis.value    = '';
    filterExpFrom.value  = '';
    filterExpTo.value    = '';
}

// ── Fetch semua dokumen sekali ─────────────────────────
async function fetchDocuments() {
    isLoading.value = true;
    try {
        const { data } = await axios.get('/api/pegawai/documents');
        allDocs.value = data.data;
    } catch {
        allDocs.value = [];
    } finally {
        isLoading.value = false;
    }
}

function goToDetail(id) {
    router.push({ name: 'pegawai.dokumen.detail', params: { id } });
}

function confirmDelete(doc) {
    docToDelete.value = doc;
}

async function deleteDocument() {
    if (!docToDelete.value) return;
    isDeleting.value = true;
    try {
        await axios.delete(`/api/pegawai/documents/${docToDelete.value.id}`);
        allDocs.value = allDocs.value.filter(d => d.id !== docToDelete.value.id);
        docToDelete.value = null;
    } catch {
        //
    } finally {
        isDeleting.value = false;
    }
}

async function exportExcel() {
    isExporting.value = true;
    try {
        const params = new URLSearchParams();
        if (filterStatus.value) params.set('status', filterStatus.value);

        const token = localStorage.getItem('auth_token');
        const url   = `/api/pegawai/documents/export${params.toString() ? '?' + params.toString() : ''}`;

        const response = await axios.get(url, {
            responseType: 'blob',
            headers: { Authorization: `Bearer ${token}` },
        });

        const disposition = response.headers['content-disposition'] ?? '';
        const match = disposition.match(/filename="?([^";\n]+)"?/);
        const filename = match ? match[1] : 'dokumen.xlsx';

        const blob = new Blob([response.data], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        });
        const link = document.createElement('a');
        link.href  = URL.createObjectURL(blob);
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
</script>

<style scoped>
.doc-card { transition: box-shadow 150ms ease; }
.doc-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.10), 0 2px 6px rgba(0,0,0,0.06) !important; }
</style>
