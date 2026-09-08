<template>
    <div class="space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Dokumen Saya</h2>
                <p class="text-sm text-gray-500 mt-1">Kelola sertifikasi dan dokumen Anda</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Export Excel -->
                <button
                    @click="exportExcel"
                    :disabled="isExporting"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    :title="activeFilter ? `Export dokumen status: ${activeFilter}` : 'Export semua dokumen'"
                >
                    <svg v-if="!isExporting" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <svg v-else class="w-4 h-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    {{ isExporting ? 'Mengexport...' : 'Export Excel' }}
                </button>

                <RouterLink
                    :to="{ name: 'pegawai.dokumen.tambah' }"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Upload Dokumen
                </RouterLink>
            </div>
        </div>

        <!-- Filter status -->
        <div class="flex gap-2 flex-wrap">
            <button
                v-for="f in filters"
                :key="f.value"
                @click="setFilter(f.value)"
                :class="[
                    'px-3 py-1.5 text-xs font-medium rounded-full border transition-colors',
                    activeFilter === f.value
                        ? 'bg-gray-900 text-white border-gray-900'
                        : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400'
                ]"
            >
                {{ f.label }}
                <span v-if="f.value && counts[f.value]" class="ml-1 opacity-70">({{ counts[f.value] }})</span>
            </button>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="space-y-3">
            <div v-for="i in 4" :key="i" class="bg-white rounded-xl border border-gray-200 p-5 animate-pulse">
                <div class="flex justify-between">
                    <div class="space-y-2 flex-1">
                        <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/4"></div>
                    </div>
                    <div class="h-6 w-24 bg-gray-200 rounded-full"></div>
                </div>
                <div class="mt-4 flex gap-4">
                    <div class="h-3 bg-gray-200 rounded w-1/4"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/4"></div>
                </div>
            </div>
        </div>

        <!-- Kosong -->
        <div
            v-else-if="documents.length === 0"
            class="bg-white rounded-xl border border-gray-200 py-16 text-center"
        >
            <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm text-gray-400">Belum ada dokumen{{ activeFilter ? ' dengan status ini' : '' }}</p>
            <RouterLink
                :to="{ name: 'pegawai.dokumen.tambah' }"
                class="mt-3 inline-block text-sm text-green-600 hover:underline"
            >
                Upload dokumen pertama Anda
            </RouterLink>
        </div>

        <!-- List dokumen -->
        <div v-else class="space-y-3">
            <div
                v-for="doc in documents"
                :key="doc.id"
                class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-sm transition-shadow"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm font-semibold text-gray-900">
                                {{ doc.certification_type?.name }}
                            </h3>
                            <span class="text-xs text-gray-400">·</span>
                            <span class="text-xs text-gray-500">{{ doc.certification_type?.category?.name }}</span>
                        </div>
                        <p v-if="doc.certificate_number" class="text-xs text-gray-400 mt-0.5">
                            No. {{ doc.certificate_number }}
                        </p>
                    </div>

                    <!-- Status badge -->
                    <span :class="['shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusConfig(doc.status).badge]">
                        {{ getStatusConfig(doc.status).label }}
                    </span>
                </div>

                <!-- Info tanggal -->
                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-xs text-gray-500">
                    <span v-if="doc.issued_date">
                        Terbit: <span class="text-gray-700">{{ formatDate(doc.issued_date) }}</span>
                    </span>
                    <span>
                        Kadaluarsa: <span :class="['font-medium', doc.status === 'expired' ? 'text-red-600' : doc.status === 'segera_expired' ? 'text-amber-600' : 'text-gray-700']">
                            {{ formatDate(doc.expiry_date) }}
                        </span>
                    </span>
                    <span v-if="['aktif','segera_expired'].includes(doc.status)">
                        <span :class="daysUntilExpiry(doc.expiry_date) <= 10 ? 'text-red-600 font-semibold' : 'text-gray-500'">
                            {{ daysUntilExpiry(doc.expiry_date) }} hari lagi
                        </span>
                    </span>
                </div>

                <!-- Alasan tolak -->
                <div v-if="doc.status === 'ditolak' && doc.rejection_reason" class="mt-3 text-xs text-pink-700 bg-pink-50 border border-pink-200 rounded-lg px-3 py-2">
                    <span class="font-medium">Alasan ditolak:</span> {{ doc.rejection_reason }}
                </div>

                <!-- Actions -->
                <div class="mt-4 flex items-center gap-3 pt-3 border-t border-gray-50">
                    <RouterLink
                        :to="{ name: 'pegawai.dokumen.detail', params: { id: doc.id } }"
                        class="text-xs text-blue-600 hover:underline"
                    >
                        Lihat Detail
                    </RouterLink>
                    <span class="text-gray-200">|</span>
                    <RouterLink
                        :to="{ name: 'pegawai.dokumen.edit', params: { id: doc.id } }"
                        class="text-xs text-gray-600 hover:underline"
                    >
                        Edit
                    </RouterLink>
                    <span class="text-gray-200">|</span>
                    <a
                        v-if="doc.has_file"
                        :href="`/api/pegawai/documents/${doc.id}/download`"
                        class="text-xs text-gray-600 hover:underline"
                        target="_blank"
                    >
                        Download
                    </a>
                    <span v-if="doc.has_file" class="text-gray-200">|</span>
                    <button
                        @click="confirmDelete(doc)"
                        class="text-xs text-red-500 hover:underline"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal konfirmasi hapus -->
        <div
            v-if="docToDelete"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
            @click.self="docToDelete = null"
        >
            <div class="bg-white rounded-xl shadow-lg p-6 max-w-sm w-full mx-4">
                <h4 class="text-base font-semibold text-gray-900">Hapus Dokumen?</h4>
                <p class="text-sm text-gray-500 mt-2">
                    Dokumen <span class="font-medium text-gray-800">{{ docToDelete.certification_type?.name }}</span>
                    akan dihapus permanen beserta file-nya.
                </p>
                <div class="flex justify-end gap-3 mt-5">
                    <button
                        @click="docToDelete = null"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Batal
                    </button>
                    <button
                        @click="deleteDocument"
                        :disabled="isDeleting"
                        class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50"
                    >
                        {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useDocumentHelpers } from '@/composables/useDocumentHelpers.js';

const route = useRoute();
const router = useRouter();
const { getStatusConfig, formatDate, daysUntilExpiry } = useDocumentHelpers();

const isLoading   = ref(true);
const documents   = ref([]);
const docToDelete = ref(null);
const isDeleting  = ref(false);
const isExporting = ref(false);

const filters = [
    { label: 'Semua', value: '' },
    { label: 'Aktif', value: 'aktif' },
    { label: 'Segera Expired', value: 'segera_expired' },
    { label: 'Expired', value: 'expired' },
    { label: 'Pending', value: 'pending_approval' },
    { label: 'Ditolak', value: 'ditolak' },
];

const activeFilter = ref(route.query.status ?? '');
const counts = ref({});

async function fetchDocuments() {
    isLoading.value = true;
    try {
        const params = activeFilter.value ? { status: activeFilter.value } : {};
        const { data } = await axios.get('/api/pegawai/documents', { params });
        documents.value = data.data;
    } catch {
        documents.value = [];
    } finally {
        isLoading.value = false;
    }
}

async function fetchCounts() {
    // Ambil semua tanpa filter untuk hitung count per status
    try {
        const { data } = await axios.get('/api/pegawai/documents');
        const all = data.data;
        counts.value = all.reduce((acc, doc) => {
            acc[doc.status] = (acc[doc.status] ?? 0) + 1;
            return acc;
        }, {});
    } catch {
        counts.value = {};
    }
}

function setFilter(value) {
    activeFilter.value = value;
    router.replace({ query: value ? { status: value } : {} });
}

async function exportExcel() {
    isExporting.value = true;
    try {
        const params = new URLSearchParams();
        if (activeFilter.value) params.set('status', activeFilter.value);

        const token = localStorage.getItem('auth_token');
        const url   = `/api/pegawai/documents/export${params.toString() ? '?' + params.toString() : ''}`;

        const response = await axios.get(url, {
            responseType: 'blob',
            headers: { Authorization: `Bearer ${token}` },
        });

        // Ambil nama file dari Content-Disposition header
        const disposition = response.headers['content-disposition'] ?? '';
        const match = disposition.match(/filename="?([^";\n]+)"?/);
        const filename = match ? match[1] : 'dokumen.xlsx';

        // Trigger download
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

function confirmDelete(doc) {
    docToDelete.value = doc;
}

async function deleteDocument() {
    if (!docToDelete.value) return;
    isDeleting.value = true;
    try {
        await axios.delete(`/api/pegawai/documents/${docToDelete.value.id}`);
        documents.value = documents.value.filter(d => d.id !== docToDelete.value.id);
        docToDelete.value = null;
        fetchCounts();
    } catch {
        //
    } finally {
        isDeleting.value = false;
    }
}

watch(activeFilter, fetchDocuments);

onMounted(() => {
    fetchDocuments();
    fetchCounts();
});
</script>
