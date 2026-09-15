<template>
    <div class="space-y-5">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <RouterLink :to="{ name: 'admin.dokumen' }"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-xl transition-colors shrink-0"
                    style="background:#F3F4F6; color:#374151;"
                    onmouseover="this.style.background='#E5E7EB'" onmouseout="this.style.background='#F3F4F6'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
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
                <!-- Approve -->
                <button v-if="doc.status === 'pending_approval'" @click="showApproveModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors"
                    style="background:#F7FEE7; color:#5a6e0f;"
                    onmouseover="this.style.background='#ECFCCB'" onmouseout="this.style.background='#F7FEE7'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Setujui
                </button>
                <!-- Tolak -->
                <button v-if="doc.status === 'pending_approval'" @click="showRejectModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors"
                    style="background:#FCE7F3; color:#DB2777;"
                    onmouseover="this.style.background='#FBCFE8'" onmouseout="this.style.background='#FCE7F3'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Tolak
                </button>
                <!-- Edit -->
                <RouterLink :to="{ name: 'admin.dokumen.edit', params: { id: doc.id } }"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors"
                    style="background:#F3F4F6; color:#374151;"
                    onmouseover="this.style.background='#E5E7EB'" onmouseout="this.style.background='#F3F4F6'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </RouterLink>
                <!-- Hapus -->
                <button @click="showDeleteModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors"
                    style="background:#FEE2E2; color:#ED1B2F;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus
                </button>
            </div>
        </div>

        <!-- ── Loading ────────────────────────────────────── -->
        <div v-if="isLoading" class="grid grid-cols-1 lg:grid-cols-5 gap-5">
            <div class="lg:col-span-3 space-y-5">
                <div class="card-elevated rounded-2xl p-6 animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div v-for="i in 8" :key="i" class="h-12 bg-gray-100 rounded-xl"></div>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-2 card-elevated rounded-2xl animate-pulse" style="min-height:320px;"></div>
        </div>

        <!-- ── Konten ──────────────────────────────────────── -->
        <div v-else-if="doc" class="grid grid-cols-1 lg:grid-cols-5 gap-5">

            <!-- Kolom kiri (3/5) -->
            <div class="lg:col-span-3 space-y-5">

                <!-- Info Pegawai -->
                <div class="card-elevated rounded-2xl px-6 py-5">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#EFF6FF;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#006CB8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold" style="color:#111827;">Informasi Pegawai</h3>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Nama</dt>
                            <dd class="text-sm font-bold" style="color:#111827;">{{ doc.user?.name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">No. Pegawai</dt>
                            <dd class="text-sm font-semibold" style="color:#374151;">{{ doc.user?.employee_number ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Departemen</dt>
                            <dd class="text-sm font-semibold" style="color:#374151;">{{ doc.user?.department?.name ?? '—' }}</dd>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="card-elevated rounded-2xl overflow-hidden">
                    <div class="h-1 w-full" :style="{ background: statusConfig.accentColor }"></div>
                    <div class="px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest mb-2" style="color:#6B7280;">Status Dokumen</p>
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-bold" :style="statusConfig.badgeStyle">
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
                        <div v-if="doc.status === 'ditolak' && doc.rejection_reason" class="mt-4 pt-4 border-t" style="border-color:#FCE7F3;">
                            <p class="text-xs font-bold mb-1" style="color:#DB2777;">Alasan Penolakan:</p>
                            <p class="text-sm font-medium" style="color:#9D174D;">{{ doc.rejection_reason }}</p>
                        </div>
                    </div>
                </div>

                <!-- Info Sertifikasi -->
                <div class="card-elevated rounded-2xl px-6 py-5">
                    <div class="flex items-center gap-2.5 mb-5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#EFF6FF;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#006CB8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold" style="color:#111827;">Informasi Sertifikasi</h3>
                    </div>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-5">
                        <div><dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Kategori</dt><dd class="text-sm font-bold" style="color:#111827;">{{ doc.certification_type?.category?.name ?? '—' }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Jenis Sertifikasi</dt><dd class="text-sm font-bold" style="color:#111827;">{{ doc.certification_type?.name ?? '—' }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Nomor Sertifikat</dt><dd class="text-sm font-semibold" style="color:#374151;">{{ doc.certificate_number ?? '—' }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Tgl. Pelaksanaan</dt><dd class="text-sm font-semibold" style="color:#374151;">{{ formatDate(doc.implementation_date) }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Tgl. Terbit</dt><dd class="text-sm font-semibold" style="color:#374151;">{{ formatDate(doc.issued_date) }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Tgl. Kadaluarsa</dt><dd class="text-sm font-bold" :style="{ color: statusConfig.accentColor }">{{ formatDate(doc.expiry_date) }}</dd></div>
                        <div v-if="doc.approver"><dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Disetujui Oleh</dt><dd class="text-sm font-semibold" style="color:#374151;">{{ doc.approver }}</dd></div>
                        <div v-if="doc.approved_at"><dt class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Tgl. Approval</dt><dd class="text-sm font-semibold" style="color:#374151;">{{ doc.approved_at }}</dd></div>
                    </dl>
                </div>
            </div>

            <!-- Kolom kanan: Preview file (2/5) -->
            <div class="lg:col-span-2">
                <div class="card-elevated rounded-2xl overflow-hidden sticky top-5">
                    <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #F3F4F6;">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#F7FEE7;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ACC42A;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </div>
                            <h3 class="text-sm font-bold" style="color:#111827;">Preview Sertifikat</h3>
                        </div>
                        <button v-if="doc.has_file" @click="downloadFile"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors"
                            style="background:#EFF6FF; color:#006CB8;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download
                        </button>
                    </div>
                    <div class="p-4">
                        <template v-if="doc.has_file">
                            <div v-if="isImage" class="rounded-xl overflow-hidden" style="background:#F9FAFB;">
                                <img :src="fileUrl" :alt="doc.file_name" class="w-full object-contain" style="max-height:480px;"/>
                            </div>
                            <div v-else-if="isPdf" class="rounded-xl overflow-hidden" style="background:#F9FAFB;">
                                <embed v-if="pdfBlobUrl" :src="pdfBlobUrl + '#toolbar=0&navpanes=0'" type="application/pdf" class="w-full rounded-xl" style="height:480px; border:none;"/>
                                <div v-else class="flex items-center justify-center py-16">
                                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" style="color:#9CA3AF;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                </div>
                            </div>
                            <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                                <svg class="w-10 h-10 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#9CA3AF;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <p class="text-sm font-semibold" style="color:#374151;">{{ doc.file_name }}</p>
                            </div>
                        </template>
                        <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-3" style="background:#F3F4F6;">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#D1D5DB;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm font-medium" style="color:#6B7280;">Tidak ada file</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Modal Approve ──────────────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showApproveModal" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="showApproveModal = false">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#F7FEE7;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#5a6e0f;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Setujui Dokumen?</h4>
                    <p class="text-sm text-center mt-2" style="color:#6B7280;">Status dokumen akan dihitung otomatis dari tanggal kadaluarsa. Pegawai akan menerima notifikasi.</p>
                    <div class="flex gap-3 mt-6">
                        <button @click="showApproveModal = false" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="approveDoc" :disabled="isProcessing" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                            style="background:#ACC42A;" onmouseover="this.style.background='#94a81d'" onmouseout="this.style.background='#ACC42A'">
                            {{ isProcessing ? 'Memproses...' : 'Ya, Setujui' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ── Modal Tolak ────────────────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="showRejectModal = false">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#FCE7F3;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#DB2777;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Tolak Dokumen</h4>
                    <p class="text-sm text-center mt-1 mb-4" style="color:#6B7280;">Alasan penolakan wajib diisi dan akan dikirim ke pegawai.</p>
                    <textarea
                        v-model="rejectionReason"
                        rows="3"
                        placeholder="Tuliskan alasan penolakan..."
                        class="w-full px-4 py-3 rounded-xl text-sm font-medium outline-none resize-none"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                        @focus="e => e.target.style.borderColor='#ED1B2F'"
                        @blur="e => e.target.style.borderColor='#E2E8F0'"
                    ></textarea>
                    <p v-if="rejectError" class="text-xs mt-1" style="color:#ED1B2F;">{{ rejectError }}</p>
                    <div class="flex gap-3 mt-4">
                        <button @click="showRejectModal = false; rejectionReason = ''; rejectError = ''" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="rejectDoc" :disabled="isProcessing" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                            style="background:#DB2777;" onmouseover="this.style.background='#be185d'" onmouseout="this.style.background='#DB2777'">
                            {{ isProcessing ? 'Memproses...' : 'Ya, Tolak' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ── Modal Hapus ────────────────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="showDeleteModal = false">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#FEE2E2;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Hapus Dokumen?</h4>
                    <p class="text-sm text-center mt-2" style="color:#6B7280;">Dokumen beserta filenya akan dihapus permanen dan tidak bisa dikembalikan.</p>
                    <div class="flex gap-3 mt-6">
                        <button @click="showDeleteModal = false" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
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
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useDocumentHelpers } from '@/composables/useDocumentHelpers.js';
import { useFaq } from '@/composables/useFaq.js';
import axios from 'axios';

const route  = useRoute();
const router = useRouter();
const { getStatusConfig, formatDate } = useDocumentHelpers();

const isLoading     = ref(true);
const isProcessing  = ref(false);
const doc           = ref(null);
const pdfBlobUrl    = ref(null);

const showApproveModal = ref(false);
const showRejectModal  = ref(false);
const showDeleteModal  = ref(false);
const rejectionReason  = ref('');
const rejectError      = ref('');

const statusConfig = computed(() => doc.value ? getStatusConfig(doc.value.status) : {});
const daysLeft     = computed(() => {
    if (!doc.value?.expiry_date) return null;
    return Math.floor((new Date(doc.value.expiry_date) - new Date()) / 86400000);
});

const isImage  = computed(() => ['image/jpeg','image/jpg','image/png','image/webp'].includes(doc.value?.file_mime));
const isPdf    = computed(() => doc.value?.file_mime === 'application/pdf');
const fileUrl  = computed(() => doc.value?.file_path ? `/storage/${doc.value.file_path}` : null);

async function fetchDocument() {
    isLoading.value = true;
    try {
        const { data } = await axios.get(`/api/admin/documents/${route.params.id}`);
        doc.value = data;
        if (isPdf.value && data.file_path) {
            loadPdf();
        }
    } catch {
        doc.value = null;
    } finally {
        isLoading.value = false;
    }
}

async function loadPdf() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await axios.get(`/api/admin/documents/${route.params.id}/download`, {
            responseType: 'blob',
            headers: { Authorization: `Bearer ${token}` },
        });
        pdfBlobUrl.value = URL.createObjectURL(response.data);
    } catch { /**/ }
}

async function downloadFile() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await axios.get(`/api/admin/documents/${route.params.id}/download`, {
            responseType: 'blob',
            headers: { Authorization: `Bearer ${token}` },
        });
        const blob = new Blob([response.data]);
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = doc.value?.file_name ?? 'sertifikat';
        link.click();
        URL.revokeObjectURL(link.href);
    } catch { /**/ }
}

async function approveDoc() {
    isProcessing.value = true;
    try {
        const { data } = await axios.post(`/api/admin/documents/${route.params.id}/approve`);
        doc.value = data.document;
        showApproveModal.value = false;
    } catch { /**/ } finally {
        isProcessing.value = false;
    }
}

async function rejectDoc() {
    rejectError.value = '';
    if (!rejectionReason.value.trim() || rejectionReason.value.trim().length < 5) {
        rejectError.value = 'Alasan penolakan minimal 5 karakter.';
        return;
    }
    isProcessing.value = true;
    try {
        const { data } = await axios.post(`/api/admin/documents/${route.params.id}/reject`, {
            rejection_reason: rejectionReason.value,
        });
        doc.value = data.document;
        showRejectModal.value = false;
        rejectionReason.value = '';
    } catch (e) {
        rejectError.value = e.response?.data?.message ?? 'Gagal menolak dokumen.';
    } finally {
        isProcessing.value = false;
    }
}

async function deleteDoc() {
    isProcessing.value = true;
    try {
        await axios.delete(`/api/admin/documents/${route.params.id}`);
        router.push({ name: 'admin.dokumen' });
    } catch { /**/ } finally {
        isProcessing.value = false;
    }
}

onMounted(fetchDocument);

useFaq([
    { q: 'Cara menyetujui dokumen?', a: 'Klik tombol "Setujui" di kanan atas. Status dokumen akan dihitung otomatis dari tanggal kadaluarsa dan pegawai akan menerima notifikasi.', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { q: 'Cara menolak dokumen?', a: 'Klik tombol "Tolak", lalu isi alasan penolakan (wajib). Alasan akan dikirimkan ke pegawai via notifikasi agar bisa memperbaiki.', icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { q: 'Cara download file sertifikat?', a: 'Klik tombol "Download" di panel preview kanan. File akan terunduh langsung ke perangkat kamu.', icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4' },
]);
</script>
