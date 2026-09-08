<template>
    <div class="max-w-2xl space-y-5">

        <!-- Back + Header -->
        <div>
            <RouterLink :to="{ name: 'pegawai.dokumen' }" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </RouterLink>
            <h2 class="text-2xl font-bold text-gray-900">Detail Dokumen</h2>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="bg-white rounded-xl border border-gray-200 p-6 animate-pulse space-y-4">
            <div class="h-5 bg-gray-200 rounded w-1/2"></div>
            <div class="h-4 bg-gray-200 rounded w-1/3"></div>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div v-for="i in 6" :key="i" class="h-10 bg-gray-200 rounded"></div>
            </div>
        </div>

        <template v-else-if="document">

            <!-- Status card -->
            <div :class="['rounded-xl border p-5', statusBorderClass]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status Dokumen</p>
                        <span :class="['mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold', statusConfig.badge]">
                            <span :class="['w-2 h-2 rounded-full mr-2', statusConfig.dot]"></span>
                            {{ statusConfig.label }}
                        </span>
                    </div>
                    <div v-if="daysLeft !== null && ['aktif','segera_expired'].includes(document.status)" class="text-right">
                        <p class="text-xs text-gray-500">Sisa waktu</p>
                        <p :class="['text-2xl font-bold', daysLeft <= 10 ? 'text-red-600' : daysLeft <= 60 ? 'text-amber-600' : 'text-gray-900']">
                            {{ daysLeft }}<span class="text-sm font-normal ml-1">hari</span>
                        </p>
                    </div>
                </div>

                <!-- Alasan ditolak -->
                <div v-if="document.status === 'ditolak' && document.rejection_reason" class="mt-3 pt-3 border-t border-pink-200">
                    <p class="text-xs font-medium text-pink-700">Alasan Penolakan:</p>
                    <p class="text-sm text-pink-800 mt-1">{{ document.rejection_reason }}</p>
                </div>
            </div>

            <!-- Info dokumen -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Informasi Sertifikasi</h3>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs text-gray-500">Jenis Dokumen</dt>
                        <dd class="text-sm font-medium text-gray-900 mt-0.5">{{ document.certification_type?.category?.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Kategori</dt>
                        <dd class="text-sm font-medium text-gray-900 mt-0.5">{{ document.certification_type?.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Nomor Sertifikat</dt>
                        <dd class="text-sm font-medium text-gray-900 mt-0.5">{{ document.certificate_number ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Tanggal Pelaksanaan</dt>
                        <dd class="text-sm font-medium text-gray-900 mt-0.5">{{ formatDate(document.implementation_date) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Tanggal Terbit</dt>
                        <dd class="text-sm font-medium text-gray-900 mt-0.5">{{ formatDate(document.issued_date) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Tanggal Kadaluarsa</dt>
                        <dd :class="['text-sm font-semibold mt-0.5', document.status === 'expired' ? 'text-red-600' : document.status === 'segera_expired' ? 'text-amber-600' : 'text-gray-900']">
                            {{ formatDate(document.expiry_date) }}
                        </dd>
                    </div>
                    <div v-if="document.approved_at">
                        <dt class="text-xs text-gray-500">Disetujui oleh</dt>
                        <dd class="text-sm font-medium text-gray-900 mt-0.5">{{ document.approver ?? '—' }}</dd>
                    </div>
                    <div v-if="document.approved_at">
                        <dt class="text-xs text-gray-500">Tanggal Approval</dt>
                        <dd class="text-sm font-medium text-gray-900 mt-0.5">{{ document.approved_at }}</dd>
                    </div>
                </dl>
            </div>

            <!-- File -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">File Sertifikat</h3>
                <div v-if="document.has_file" class="flex items-center justify-between gap-4 bg-gray-50 rounded-lg px-4 py-3 border border-gray-200">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="w-8 h-8 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ document.file_name }}</p>
                            <p class="text-xs text-gray-400">{{ formatFileSize(document.file_size) }}</p>
                        </div>
                    </div>
                    <a
                        :href="`/api/pegawai/documents/${document.id}/download`"
                        target="_blank"
                        class="shrink-0 text-sm text-blue-600 hover:underline font-medium"
                    >
                        Download
                    </a>
                </div>
                <p v-else class="text-sm text-gray-400">Belum ada file yang diupload</p>
            </div>

            <!-- Riwayat versi file -->
            <div v-if="document.versions?.length > 0" class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Riwayat File</h3>
                <ul class="space-y-3">
                    <li
                        v-for="v in document.versions"
                        :key="v.id"
                        class="flex items-center gap-3 text-sm text-gray-500"
                    >
                        <svg class="w-4 h-4 shrink-0 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="truncate">{{ v.file_name }}</span>
                        <span class="shrink-0 text-xs text-gray-400">{{ v.replaced_at }}</span>
                    </li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <RouterLink
                    :to="{ name: 'pegawai.dokumen.edit', params: { id: document.id } }"
                    class="px-5 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
                >
                    Edit Dokumen
                </RouterLink>
                <button
                    @click="showDeleteModal = true"
                    class="px-5 py-2 border border-red-300 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors"
                >
                    Hapus
                </button>
            </div>
        </template>

        <!-- Not found -->
        <div v-else class="bg-white rounded-xl border border-gray-200 py-16 text-center">
            <p class="text-sm text-gray-400">Dokumen tidak ditemukan.</p>
        </div>

        <!-- Modal hapus -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
            @click.self="showDeleteModal = false"
        >
            <div class="bg-white rounded-xl shadow-lg p-6 max-w-sm w-full mx-4">
                <h4 class="text-base font-semibold text-gray-900">Hapus Dokumen?</h4>
                <p class="text-sm text-gray-500 mt-2">Dokumen ini akan dihapus permanen beserta file-nya.</p>
                <div class="flex justify-end gap-3 mt-5">
                    <button @click="showDeleteModal = false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Batal</button>
                    <button @click="deleteDocument" :disabled="isDeleting" class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">
                        {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}
                    </button>
                </div>
            </div>
        </div>

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
const document        = ref(null);
const showDeleteModal = ref(false);
const isDeleting      = ref(false);

const statusConfig = computed(() => getStatusConfig(document.value?.status));
const daysLeft     = computed(() => daysUntilExpiry(document.value?.expiry_date));

const statusBorderClass = computed(() => {
    const map = {
        aktif: 'border-green-200 bg-green-50',
        segera_expired: 'border-amber-200 bg-amber-50',
        expired: 'border-red-200 bg-red-50',
        pending_approval: 'border-gray-200 bg-gray-50',
        ditolak: 'border-pink-200 bg-pink-50',
    };
    return map[document.value?.status] ?? 'border-gray-200 bg-gray-50';
});

async function fetchDocument() {
    isLoading.value = true;
    try {
        const { data } = await axios.get(`/api/pegawai/documents/${route.params.id}`);
        document.value = data;
    } catch {
        document.value = null;
    } finally {
        isLoading.value = false;
    }
}

async function deleteDocument() {
    isDeleting.value = true;
    try {
        await axios.delete(`/api/pegawai/documents/${document.value.id}`);
        router.push({ name: 'pegawai.dokumen' });
    } catch {
        //
    } finally {
        isDeleting.value = false;
    }
}

onMounted(fetchDocument);
</script>
