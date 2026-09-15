<template>
    <div class="space-y-5">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold" style="color:#111827;">Approval Dokumen</h2>
                <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">
                    <span v-if="!isLoading">{{ allDocs.length }} dokumen menunggu review</span>
                    <span v-else>Memuat...</span>
                </p>
            </div>
            <span v-if="allDocs.length > 0" class="text-sm font-bold px-3 py-1.5 rounded-full" style="background:#F3F4F6; color:#6B7280;">
                {{ filteredDocs.length }} pending
            </span>
        </div>

        <!-- ── Filter ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-5 py-4 space-y-3">
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/></svg>
                    </span>
                    <input v-model="search" type="text" placeholder="Cari nama pegawai atau jenis sertifikat..."
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                        style="background:#F9FAFB; border:1.5px solid #ED1B2F; color:#111827;"
                        @focus="e => e.target.style.borderColor='#c8102e'" @blur="e => e.target.style.borderColor='#ED1B2F'"/>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <select v-model="filterKategori" class="px-3 py-2 rounded-xl text-xs font-semibold outline-none cursor-pointer"
                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'">
                    <option value="">Semua Kategori</option>
                    <option v-for="k in kategoriOptions" :key="k" :value="k">{{ k }}</option>
                </select>
                <select v-model="filterDepartemen" class="px-3 py-2 rounded-xl text-xs font-semibold outline-none cursor-pointer"
                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'">
                    <option value="">Semua Departemen</option>
                    <option v-for="d in departemenOptions" :key="d" :value="d">{{ d }}</option>
                </select>
                <button v-if="hasFilter" @click="resetFilters"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold"
                    style="background:#FEE2E2; color:#ED1B2F;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset
                </button>
            </div>
        </div>

        <!-- ── Loading ────────────────────────────────────── -->
        <div v-if="isLoading" class="space-y-3">
            <div v-for="i in 4" :key="i" class="card-elevated rounded-xl p-5 animate-pulse flex gap-4">
                <div class="w-10 h-10 rounded-xl bg-gray-200 shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                    <div class="h-3 bg-gray-100 rounded w-1/2"></div>
                    <div class="h-3 bg-gray-100 rounded w-1/4"></div>
                </div>
            </div>
        </div>

        <!-- ── Kosong ─────────────────────────────────────── -->
        <div v-else-if="filteredDocs.length === 0" class="card-elevated rounded-xl py-20 text-center">
            <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#F7FEE7;">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ACC42A;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm font-bold" style="color:#374151;">
                {{ hasFilter ? 'Tidak ada dokumen ditemukan' : 'Tidak ada dokumen yang menunggu approval' }}
            </p>
            <p class="text-xs mt-1 font-medium" style="color:#9CA3AF;">
                {{ hasFilter ? 'Coba ubah filter' : 'Semua dokumen sudah diproses' }}
            </p>
        </div>

        <!-- ── List Dokumen Pending ───────────────────────── -->
        <div v-else class="space-y-2">
            <div v-for="doc in filteredDocs" :key="doc.id" class="card-elevated rounded-xl overflow-hidden">
                <div class="flex">
                    <div class="w-1 shrink-0" style="background:#6B7280;"></div>

                    <!-- KIRI: Avatar + info pegawai -->
                    <div class="flex-1 px-4 py-3 flex items-center gap-3 min-w-0">
                        <img
                            v-if="doc.user?.avatar"
                            :src="doc.user.avatar"
                            :alt="doc.user?.name"
                            class="w-9 h-9 rounded-xl object-cover shrink-0"
                        />
                        <div v-else class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-white text-sm font-bold" style="background:#ED1B2F;">
                            {{ doc.user?.name?.charAt(0)?.toUpperCase() }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold" style="color:#111827;">{{ doc.user?.name }}</p>
                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                <span class="text-xs" style="color:#6B7280;">{{ doc.user?.department?.name }}</span>
                                <span class="text-xs" style="color:#D1D5DB;">·</span>
                                <span class="text-xs" style="color:#9CA3AF;">{{ doc.user?.employee_number }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- TENGAH: Info sertifikat -->
                    <div class="w-48 shrink-0 border-l flex flex-col justify-center px-4 py-3" style="border-color:#F3F4F6;">
                        <p class="text-xs font-bold truncate" style="color:#374151;">{{ doc.certification_type?.name }}</p>
                        <p class="text-xs mt-0.5" style="color:#9CA3AF;">{{ doc.certification_type?.category?.name }}</p>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span v-if="doc.certificate_number" class="text-xs" style="color:#9CA3AF;">No. {{ doc.certificate_number }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-xs" style="color:#9CA3AF;">{{ timeAgo(doc.created_at) }}</span>
                            <span v-if="doc.expiry_date" class="text-xs" style="color:#9CA3AF;">· Exp: {{ formatDate(doc.expiry_date) }}</span>
                        </div>
                    </div>

                    <!-- KANAN: Badge + tombol 2+1 -->
                    <div class="w-44 shrink-0 border-l flex flex-col justify-center gap-1.5 px-3 py-3" style="border-color:#F3F4F6;">
                        <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full self-center mb-0.5" style="background:#F3F4F6; color:#6B7280;">Pending</span>
                        <!-- Baris 1: Detail + Setujui -->
                        <div class="grid grid-cols-2 gap-1">
                            <RouterLink :to="{ name: 'admin.dokumen.detail', params: { id: doc.id } }"
                                class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold"
                                style="background:#EFF6FF; color:#006CB8;">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Detail
                            </RouterLink>
                            <button @click="openApprove(doc)"
                                class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold"
                                style="background:#F7FEE7; color:#5a6e0f;">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Setujui
                            </button>
                        </div>
                        <!-- Baris 2: Tolak full-width -->
                        <button @click="openReject(doc)"
                            class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold w-full"
                            style="background:#FCE7F3; color:#DB2777;">
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Tolak
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Modal Approve ──────────────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="approveTarget" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="approveTarget = null">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#F7FEE7;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#5a6e0f;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Setujui Dokumen?</h4>
                    <p class="text-sm text-center mt-2" style="color:#6B7280;">
                        <span class="font-bold" style="color:#111827;">{{ approveTarget?.certification_type?.name }}</span> milik <span class="font-bold" style="color:#111827;">{{ approveTarget?.user?.name }}</span> akan disetujui. Status dihitung dari tanggal kadaluarsa.
                    </p>
                    <div class="flex gap-3 mt-6">
                        <button @click="approveTarget = null" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
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
            <div v-if="rejectTarget" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="rejectTarget = null">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#FCE7F3;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#DB2777;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Tolak Dokumen</h4>
                    <p class="text-sm text-center mt-1 mb-4" style="color:#6B7280;">Alasan penolakan wajib diisi dan akan dikirim ke pegawai.</p>
                    <textarea v-model="rejectionReason" rows="3" placeholder="Tuliskan alasan penolakan secara jelas..."
                        class="w-full px-4 py-3 rounded-xl text-sm font-medium outline-none resize-none"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                        @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'">
                    </textarea>
                    <p v-if="rejectError" class="text-xs mt-1" style="color:#ED1B2F;">{{ rejectError }}</p>
                    <div class="flex gap-3 mt-4">
                        <button @click="rejectTarget = null; rejectionReason = ''; rejectError = ''" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="rejectDoc" :disabled="isProcessing" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                            style="background:#DB2777;" onmouseover="this.style.background='#be185d'" onmouseout="this.style.background='#DB2777'">
                            {{ isProcessing ? 'Memproses...' : 'Ya, Tolak' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useFaq } from '@/composables/useFaq.js';
import axios from 'axios';

const isLoading   = ref(true);
const isProcessing = ref(false);
const allDocs      = ref([]);
const search          = ref('');
const filterKategori  = ref('');
const filterDepartemen = ref('');
const approveTarget   = ref(null);
const rejectTarget    = ref(null);
const rejectionReason = ref('');
const rejectError     = ref('');

const kategoriOptions  = computed(() => [...new Set(allDocs.value.map(d => d.certification_type?.category?.name).filter(Boolean))].sort());
const departemenOptions = computed(() => [...new Set(allDocs.value.map(d => d.user?.department?.name).filter(Boolean))].sort());
const hasFilter = computed(() => !!(search.value || filterKategori.value || filterDepartemen.value));

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
    if (filterKategori.value)   docs = docs.filter(d => d.certification_type?.category?.name === filterKategori.value);
    if (filterDepartemen.value) docs = docs.filter(d => d.user?.department?.name === filterDepartemen.value);
    return docs;
});

function resetFilters() { search.value = filterKategori.value = filterDepartemen.value = ''; }

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function timeAgo(iso) {
    if (!iso) return '';
    const diff = Math.floor((Date.now() - new Date(iso)) / 1000);
    if (diff < 60)    return `${diff}d lalu`;
    if (diff < 3600)  return `${Math.floor(diff / 60)}m lalu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}j lalu`;
    return `${Math.floor(diff / 86400)} hari lalu`;
}

async function fetchDocs() {
    isLoading.value = true;
    try {
        const { data } = await axios.get('/api/admin/approval');
        allDocs.value = data.data;
    } catch {
        allDocs.value = [];
    } finally {
        isLoading.value = false;
    }
}

function openApprove(doc) { approveTarget.value = doc; }
function openReject(doc)  { rejectTarget.value = doc; rejectionReason.value = ''; rejectError.value = ''; }

async function approveDoc() {
    if (!approveTarget.value) return;
    isProcessing.value = true;
    try {
        await axios.post(`/api/admin/documents/${approveTarget.value.id}/approve`);
        allDocs.value = allDocs.value.filter(d => d.id !== approveTarget.value.id);
        approveTarget.value = null;
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
        await axios.post(`/api/admin/documents/${rejectTarget.value.id}/reject`, { rejection_reason: rejectionReason.value });
        allDocs.value = allDocs.value.filter(d => d.id !== rejectTarget.value.id);
        rejectTarget.value = null;
        rejectionReason.value = '';
    } catch (e) {
        rejectError.value = e.response?.data?.message ?? 'Gagal menolak dokumen.';
    } finally {
        isProcessing.value = false;
    }
}

onMounted(fetchDocs);

useFaq([
    { q: 'Bagaimana cara menyetujui dokumen?', a: 'Klik tombol "Setujui" pada baris dokumen. Status akan dihitung otomatis dari tanggal kadaluarsa dan pegawai menerima notifikasi.', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { q: 'Apa yang terjadi setelah dokumen ditolak?', a: 'Pegawai menerima notifikasi dengan alasan penolakan. Pegawai bisa memperbaiki dan mengupload ulang dokumen untuk diajukan kembali.', icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { q: 'Bisa lihat file sebelum approve?', a: 'Ya. Klik tombol "Lihat Detail" untuk membuka halaman detail dokumen beserta preview file sertifikat sebelum membuat keputusan.', icon: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z' },
]);
</script>
