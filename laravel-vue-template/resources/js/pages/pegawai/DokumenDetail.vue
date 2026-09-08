<template>
    <div class="space-y-5">

        <!-- ── Header card: Back + Judul + Actions ─────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <RouterLink
                    :to="{ name: 'pegawai.dokumen' }"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-xl transition-colors shrink-0"
                    style="background:#F3F4F6; color:#374151;"
                    onmouseover="this.style.background='#E5E7EB'"
                    onmouseout="this.style.background='#F3F4F6'"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </RouterLink>
                <div>
                    <h2 class="text-2xl font-bold" style="color:#111827;">Detail Dokumen</h2>
                    <p v-if="doc" class="text-sm font-medium mt-0.5" style="color:#6B7280;">
                        {{ doc.certification_type?.name }} · {{ doc.certification_type?.category?.name }}
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div v-if="doc" class="flex items-center gap-2 shrink-0">
                <RouterLink
                    :to="{ name: 'pegawai.dokumen.edit', params: { id: doc.id } }"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors"
                    style="background:#F3F4F6; color:#374151;"
                    onmouseover="this.style.background='#E5E7EB'"
                    onmouseout="this.style.background='#F3F4F6'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </RouterLink>
                <button
                    @click="showDeleteModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors"
                    style="background:#FEE2E2; color:#ED1B2F;"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            </div>
        </div>

        <!-- ── Loading ─────────────────────────────────── -->
        <div v-if="isLoading" class="grid grid-cols-1 lg:grid-cols-5 gap-5">
            <div class="lg:col-span-3 space-y-5">
                <div class="card-elevated rounded-2xl p-6 animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div v-for="i in 6" :key="i" class="h-12 bg-gray-100 rounded-xl"></div>
                    </div>
                </div>
                <div class="card-elevated rounded-2xl p-6 animate-pulse">
                    <div class="h-4 bg-gray-200 rounded w-1/4 mb-4"></div>
                    <div class="h-16 bg-gray-100 rounded-xl"></div>
                </div>
            </div>
            <div class="lg:col-span-2 card-elevated rounded-2xl animate-pulse" style="min-height:320px;"></div>
        </div>

        <!-- ── Konten utama ────────────────────────────── -->
        <div v-else-if="doc" class="grid grid-cols-1 lg:grid-cols-5 gap-5">

            <!-- Kolom kiri: detail (3/5) -->
            <div class="lg:col-span-3 space-y-5">

                <!-- Status card -->
                <div class="card-elevated rounded-2xl overflow-hidden">
                    <div class="h-1 w-full" :style="{ background: statusConfig.accentColor }"></div>
                    <div class="px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest mb-2" style="color:#6B7280;">Status Dokumen</p>
                                <span
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-bold"
                                    :style="statusConfig.badgeStyle"
                                >
                                    <span class="w-2 h-2 rounded-full" :style="{ background: statusConfig.accentColor }"></span>
                                    {{ statusConfig.label }}
                                </span>
                            </div>
                            <div v-if="daysLeft !== null && ['aktif','segera_expired'].includes(doc.status)" class="text-right">
                                <p class="text-xs font-semibold mb-1" style="color:#6B7280;">Sisa waktu</p>
                                <p class="text-4xl font-extrabold leading-none" :style="{ color: statusConfig.accentColor }">
                                    {{ daysLeft }}<span class="text-base font-semibold ml-1" style="color:#6B7280;">hari</span>
                                </p>
                            </div>
                            <div v-else-if="doc.status === 'expired'" class="text-right">
                                <p class="text-xs font-semibold mb-1" style="color:#6B7280;">Sudah kadaluarsa</p>
                                <p class="text-4xl font-extrabold leading-none" style="color:#ED1B2F;">
                                    {{ Math.abs(daysLeft) }}<span class="text-base font-semibold ml-1" style="color:#6B7280;">hari lalu</span>
                                </p>
                            </div>
                        </div>

                        <!-- Alasan ditolak -->
                        <div v-if="doc.status === 'ditolak' && doc.rejection_reason" class="mt-4 pt-4 border-t" style="border-color:#FCE7F3;">
                            <p class="text-xs font-bold mb-1" style="color:#DB2777;">Alasan Penolakan:</p>
                            <p class="text-sm font-medium" style="color:#9D174D;">{{ doc.rejection_reason }}</p>
                        </div>
                    </div>
                </div>

                <!-- Info sertifikasi -->
                <div class="card-elevated rounded-2xl px-6 py-5">
                    <div class="flex items-center gap-2.5 mb-5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#EFF6FF;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#006CB8;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold" style="color:#111827;">Informasi Sertifikasi</h3>
                    </div>

                    <dl class="grid grid-cols-2 gap-x-6 gap-y-5">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Kategori</dt>
                            <dd class="text-sm font-bold" style="color:#111827;">{{ doc.certification_type?.category?.name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Jenis Sertifikasi</dt>
                            <dd class="text-sm font-bold" style="color:#111827;">{{ doc.certification_type?.name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Nomor Sertifikat</dt>
                            <dd class="text-sm font-semibold" style="color:#374151;">{{ doc.certificate_number ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Tgl. Pelaksanaan</dt>
                            <dd class="text-sm font-semibold" style="color:#374151;">{{ formatDate(doc.implementation_date) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Tgl. Terbit</dt>
                            <dd class="text-sm font-semibold" style="color:#374151;">{{ formatDate(doc.issued_date) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Tgl. Kadaluarsa</dt>
                            <dd class="text-sm font-bold" :style="{ color: statusConfig.accentColor }">{{ formatDate(doc.expiry_date) }}</dd>
                        </div>
                        <div v-if="doc.approver">
                            <dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Disetujui Oleh</dt>
                            <dd class="text-sm font-semibold" style="color:#374151;">{{ doc.approver }}</dd>
                        </div>
                        <div v-if="doc.approved_at">
                            <dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Tgl. Approval</dt>
                            <dd class="text-sm font-semibold" style="color:#374151;">{{ doc.approved_at }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Riwayat file -->
                <div v-if="doc.versions?.length > 0" class="card-elevated rounded-2xl px-6 py-5">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#F3F4F6;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#6B7280;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold" style="color:#111827;">Riwayat File</h3>
                    </div>
                    <ul class="space-y-2">
                        <li
                            v-for="v in doc.versions"
                            :key="v.id"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl"
                            style="background:#F9FAFB; border:1px solid #F3F4F6;"
                        >
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#9CA3AF;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-xs font-semibold truncate flex-1" style="color:#374151;">{{ v.file_name }}</span>
                            <span class="text-xs font-medium shrink-0" style="color:#9CA3AF;">{{ v.replaced_at }}</span>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Kolom kanan: Preview file (2/5) -->
            <div class="lg:col-span-2">
                <div class="card-elevated rounded-2xl overflow-hidden sticky top-5">
                    <!-- Header preview -->
                    <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #F3F4F6;">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#F7FEE7;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ACC42A;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold" style="color:#111827;">Preview Sertifikat</h3>
                        </div>
                        <button
                            v-if="doc.has_file"
                            @click="downloadFile"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors"
                            style="background:#EFF6FF; color:#006CB8;"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download
                        </button>
                    </div>

                    <!-- Preview area -->
                    <div class="p-4">
                        <!-- Ada file -->
                        <template v-if="doc.has_file">
                            <!-- Image preview (jpg/jpeg/png/webp) -->
                            <div v-if="isImage" class="rounded-xl overflow-hidden" style="background:#F9FAFB;">
                                <img
                                    :src="fileUrl"
                                    :alt="doc.file_name"
                                    class="w-full object-contain"
                                    style="max-height:480px;"
                                    @error="previewError = true"
                                />
                            </div>

                            <!-- PDF preview -->
                            <div v-else-if="isPdf" class="rounded-xl overflow-hidden" style="background:#F9FAFB;">
                                <embed
                                    v-if="pdfBlobUrl"
                                    :src="pdfBlobUrl + '#toolbar=0&navpanes=0&scrollbar=0&view=FitH'"
                                    type="application/pdf"
                                    class="w-full rounded-xl"
                                    style="height:480px; border:none;"
                                />
                                <div v-else class="flex items-center justify-center py-16">
                                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" style="color:#9CA3AF;">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Format tidak bisa di-preview -->
                            <div v-else class="flex flex-col items-center justify-center py-12 rounded-xl" style="background:#F9FAFB;">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-3" style="background:#F3F4F6;">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#9CA3AF;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold" style="color:#374151;">Preview tidak tersedia</p>
                                <p class="text-xs mt-1 font-medium" style="color:#9CA3AF;">Format: {{ doc.file_mime }}</p>
                            </div>

                            <!-- Info file -->
                            <div class="mt-3 flex items-center gap-3 px-4 py-3 rounded-xl" style="background:#F9FAFB; border:1px solid #F3F4F6;">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#6B7280;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold truncate" style="color:#374151;">{{ doc.file_name }}</p>
                                    <p class="text-xs font-medium" style="color:#9CA3AF;">{{ formatFileSize(doc.file_size) }}</p>
                                </div>
                            </div>
                        </template>

                        <!-- Belum ada file -->
                        <div v-else class="flex flex-col items-center justify-center py-16 rounded-xl" style="background:#F9FAFB;">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-3" style="background:#F3F4F6;">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#D1D5DB;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold" style="color:#374151;">Belum ada file</p>
                            <p class="text-xs mt-1 font-medium" style="color:#9CA3AF;">Upload file di halaman edit</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Not found -->
        <div v-else class="card-elevated rounded-2xl py-16 text-center">
            <p class="text-sm font-semibold" style="color:#374151;">Dokumen tidak ditemukan.</p>
        </div>

        <!-- ── Modal Hapus ─────────────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40);" @click.self="showDeleteModal = false">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#FEE2E2;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Hapus Dokumen?</h4>
                    <p class="text-sm text-center mt-2" style="color:#6B7280;">Dokumen ini akan dihapus permanen beserta file-nya.</p>
                    <div class="flex gap-3 mt-6">
                        <button @click="showDeleteModal = false" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
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
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useDocumentHelpers } from '@/composables/useDocumentHelpers.js';

const route  = useRoute();
const router = useRouter();
const { getStatusConfig, formatDate, formatFileSize, daysUntilExpiry } = useDocumentHelpers();

const isLoading       = ref(true);
const doc        = ref(null);
const showDeleteModal = ref(false);
const isDeleting      = ref(false);
const previewError    = ref(false);
const pdfBlobUrl      = ref(null);

const statusConfig = computed(() => getStatusConfig(doc.value?.status));
const daysLeft     = computed(() => daysUntilExpiry(doc.value?.expiry_date));

const isImage = computed(() => {
    const mime = doc.value?.file_mime ?? '';
    const name = doc.value?.file_name ?? '';
    const ext  = name.split('.').pop().toLowerCase();
    const imageMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    const imageExts  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    return imageMimes.includes(mime) || imageExts.includes(ext);
});

const isPdf = computed(() => {
    const mime = doc.value?.file_mime ?? '';
    const name = doc.value?.file_name ?? '';
    return mime === 'application/pdf' || name.toLowerCase().endsWith('.pdf');
});

// URL file — image pakai storage public URL langsung (tidak butuh auth)
const fileUrl = computed(() => {
    if (!doc.value?.has_file || !doc.value?.file_path) return null;
    if (isImage.value) {
        return `/storage/${doc.value.file_path}`;
    }
    // PDF: pakai blob via axios dengan auth header
    return null;
});

async function downloadFile() {
    if (!doc.value?.has_file) return;
    try {
        const response = await axios.get(`/api/pegawai/documents/${doc.value.id}/download`, {
            responseType: 'blob',
        });
        const mime = doc.value.file_mime || 'application/octet-stream';
        const blob = new Blob([response.data], { type: mime });
        const url  = URL.createObjectURL(blob);
        const link = window.document.createElement('a');
        link.href     = url;
        link.download = doc.value.file_name;
        window.document.body.appendChild(link);
        link.click();
        window.document.body.removeChild(link);
        URL.revokeObjectURL(url);
    } catch { /**/ }
}

async function loadPdfPreview() {
    if (!isPdf.value || !doc.value?.has_file) return;
    try {
        const response = await axios.get(`/api/pegawai/documents/${doc.value.id}/download`, {
            responseType: 'blob',
        });
        const blob = new Blob([response.data], { type: 'application/pdf' });
        pdfBlobUrl.value = URL.createObjectURL(blob);
    } catch { /**/ }
}

async function fetchDocument() {
    isLoading.value = true;
    try {
        const { data } = await axios.get(`/api/pegawai/documents/${route.params.id}`);
        doc.value = data;
        // Load PDF blob jika file adalah PDF
        if (data.file_mime === 'application/pdf' && data.has_file) {
            await loadPdfPreview();
        }
    } catch {
        doc.value = null;
    } finally {
        isLoading.value = false;
    }
}

async function deleteDocument() {
    isDeleting.value = true;
    try {
        await axios.delete(`/api/pegawai/documents/${doc.value.id}`);
        router.push({ name: 'pegawai.dokumen' });
    } catch {
        //
    } finally {
        isDeleting.value = false;
    }
}

onMounted(fetchDocument);
</script>
