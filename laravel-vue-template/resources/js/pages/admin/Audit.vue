<template>
    <div class="space-y-5">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold" style="color:#111827;">Audit Trail</h2>
                <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">Log seluruh aktivitas sistem · {{ meta.total ?? 0 }} entri</p>
            </div>
        </div>

        <!-- ── Filter ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-5 py-4 space-y-3">
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/></svg>
                    </span>
                    <input v-model="filters.search" type="text" placeholder="Cari nama pengguna atau deskripsi aktivitas..."
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                        style="background:#F9FAFB; border:1.5px solid #ED1B2F; color:#111827;"
                        @focus="e => e.target.style.borderColor='#c8102e'" @blur="e => e.target.style.borderColor='#ED1B2F'"
                        @keyup.enter="applyFilters"/>
                </div>
                <button @click="applyFilters" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white"
                    style="background:#ED1B2F;" onmouseover="this.style.background='#c8102e'" onmouseout="this.style.background='#ED1B2F'">Cari</button>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <select v-model="filters.activity_type" @change="applyFilters"
                    class="px-3 py-2 rounded-xl text-xs font-semibold outline-none cursor-pointer"
                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'">
                    <option value="">Semua Aktivitas</option>
                    <option v-for="t in activityTypes" :key="t" :value="t">{{ formatActivityType(t) }}</option>
                </select>
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold shrink-0" style="color:#6B7280;">Dari:</span>
                    <input v-model="filters.date_from" type="date" @change="applyFilters"
                        class="px-3 py-2 rounded-xl text-xs font-semibold outline-none"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"/>
                    <span class="text-xs" style="color:#9CA3AF;">—</span>
                    <input v-model="filters.date_to" type="date" @change="applyFilters"
                        class="px-3 py-2 rounded-xl text-xs font-semibold outline-none"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"/>
                </div>
                <button v-if="hasFilter" @click="resetFilters"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold"
                    style="background:#FEE2E2; color:#ED1B2F;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset
                </button>
            </div>
        </div>

        <!-- ── Loading ────────────────────────────────────── -->
        <div v-if="isLoading" class="space-y-2">
            <div v-for="i in 8" :key="i" class="card-elevated rounded-xl px-5 py-3.5 animate-pulse flex items-center gap-4">
                <div class="w-32 h-3 bg-gray-200 rounded shrink-0"></div>
                <div class="flex-1 h-3 bg-gray-100 rounded"></div>
                <div class="w-20 h-3 bg-gray-100 rounded shrink-0"></div>
            </div>
        </div>

        <!-- ── Kosong ─────────────────────────────────────── -->
        <div v-else-if="logs.length === 0" class="card-elevated rounded-xl py-16 text-center">
            <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#F3F4F6;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#D1D5DB;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <p class="text-sm font-bold" style="color:#374151;">Tidak ada log ditemukan</p>
        </div>

        <!-- ── Tabel Log ──────────────────────────────────── -->
        <div v-else class="card-elevated rounded-xl overflow-hidden">
            <div v-for="log in logs" :key="log.id" class="border-b" style="border-color:#F3F4F6;">
                <!-- Row utama -->
                <div class="px-4 py-2.5 flex items-start gap-4 cursor-pointer hover:bg-gray-50 transition-colors"
                    @click="toggleExpand(log.id)">
                    <div class="shrink-0 w-32">
                        <p class="text-[10px] font-semibold" style="color:#9CA3AF;">{{ formatDate(log.created_at) }}</p>
                        <p class="text-[10px]" style="color:#9CA3AF;">{{ formatTime(log.created_at) }}</p>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-0.5">
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded" :style="activityBadge(log.activity_type)">
                                {{ formatActivityType(log.activity_type) }}
                            </span>
                            <span class="text-xs font-semibold" style="color:#374151;">{{ log.user?.name ?? 'System' }}</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded" style="background:#F3F4F6; color:#9CA3AF;">{{ log.user?.role }}</span>
                        </div>
                        <p class="text-xs font-medium truncate" style="color:#6B7280;">{{ log.description }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-[10px] font-medium" style="color:#9CA3AF;">{{ log.ip_address }}</span>
                        <svg v-if="log.old_values || log.new_values"
                            class="w-4 h-4 transition-transform" :class="expanded.has(log.id) ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#9CA3AF;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Expand: old vs new values -->
                <div v-if="expanded.has(log.id) && (log.old_values || log.new_values)"
                    class="px-5 pb-4" style="background:#FAFAFA; border-top:1px solid #F3F4F6;">
                    <div class="grid grid-cols-2 gap-4 mt-3">
                        <div v-if="log.old_values">
                            <p class="text-[10px] font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Sebelum</p>
                            <div class="rounded-lg p-3 text-xs font-mono" style="background:#FEE2E2; color:#991B1B;">
                                <div v-for="(val, key) in log.old_values" :key="key" class="flex gap-2 mb-0.5">
                                    <span class="font-bold shrink-0">{{ key }}:</span>
                                    <span class="truncate">{{ val }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-if="log.new_values">
                            <p class="text-[10px] font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Sesudah</p>
                            <div class="rounded-lg p-3 text-xs font-mono" style="background:#F7FEE7; color:#5a6e0f;">
                                <div v-for="(val, key) in log.new_values" :key="key" class="flex gap-2 mb-0.5">
                                    <span class="font-bold shrink-0">{{ key }}:</span>
                                    <span class="truncate">{{ val }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Pagination ─────────────────────────────────── -->
        <div v-if="meta.last_page > 1" class="flex items-center justify-between">
            <p class="text-xs font-medium" style="color:#6B7280;">
                Halaman {{ meta.current_page }} dari {{ meta.last_page }} · {{ meta.total }} entri
            </p>
            <div class="flex items-center gap-1.5">
                <button @click="changePage(meta.current_page - 1)" :disabled="meta.current_page === 1"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-semibold disabled:opacity-40 transition-colors"
                    style="background:#F3F4F6; color:#374151;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <template v-for="p in pageNumbers" :key="p">
                    <button v-if="p !== '...'" @click="changePage(p)"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-semibold transition-colors"
                        :style="p === meta.current_page ? 'background:#ED1B2F; color:#fff;' : 'background:#F3F4F6; color:#374151;'">
                        {{ p }}
                    </button>
                    <span v-else class="w-8 text-center text-xs" style="color:#9CA3AF;">...</span>
                </template>
                <button @click="changePage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-semibold disabled:opacity-40 transition-colors"
                    style="background:#F3F4F6; color:#374151;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useFaq } from '@/composables/useFaq.js';
import axios from 'axios';

const isLoading = ref(true);
const logs      = ref([]);
const meta      = ref({});
const expanded  = ref(new Set());
const activityTypes = ref([]);

const filters = ref({ search: '', activity_type: '', date_from: '', date_to: '' });
const currentPage = ref(1);

const hasFilter = computed(() => !!(filters.value.search || filters.value.activity_type || filters.value.date_from || filters.value.date_to));

const pageNumbers = computed(() => {
    const total = meta.value.last_page ?? 1;
    const cur   = meta.value.current_page ?? 1;
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    const pages = [1];
    if (cur > 3) pages.push('...');
    for (let p = Math.max(2, cur - 1); p <= Math.min(total - 1, cur + 1); p++) pages.push(p);
    if (cur < total - 2) pages.push('...');
    pages.push(total);
    return pages;
});

function toggleExpand(id) {
    if (expanded.value.has(id)) expanded.value.delete(id);
    else expanded.value.add(id);
    expanded.value = new Set(expanded.value);
}

function formatDate(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
function formatTime(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

const activityLabels = {
    dokumen_upload: 'Upload Dokumen', dokumen_edit: 'Edit Dokumen', dokumen_hapus: 'Hapus Dokumen',
    dokumen_approve: 'Approve', dokumen_reject: 'Tolak',
    pegawai_tambah: 'Tambah Pegawai', pegawai_edit: 'Edit Pegawai', pegawai_hapus: 'Hapus Pegawai',
    pegawai_toggle_active: 'Toggle Aktif', pegawai_reset_password: 'Reset Password',
    admin_tambah: 'Tambah Admin', admin_hapus: 'Hapus Admin',
};

function formatActivityType(type) {
    return activityLabels[type] ?? type?.replace(/_/g, ' ');
}

function activityBadge(type) {
    if (type?.includes('hapus') || type?.includes('reject')) return 'background:#FEE2E2; color:#991B1B;';
    if (type?.includes('tambah') || type?.includes('upload') || type?.includes('approve')) return 'background:#F7FEE7; color:#5a6e0f;';
    if (type?.includes('edit') || type?.includes('toggle')) return 'background:#FEF3C7; color:#92400E;';
    return 'background:#F3F4F6; color:#6B7280;';
}

async function fetchLogs() {
    isLoading.value = true;
    try {
        const params = { page: currentPage.value, per_page: 20, ...filters.value };
        Object.keys(params).forEach(k => { if (!params[k]) delete params[k]; });
        const { data } = await axios.get('/api/admin/audit', { params });
        logs.value = data.data;
        meta.value = data.meta;
    } catch {
        logs.value = [];
    } finally {
        isLoading.value = false;
    }
}

async function fetchTypes() {
    try {
        const { data } = await axios.get('/api/admin/audit/types');
        activityTypes.value = data.data;
    } catch { /**/ }
}

function applyFilters() { currentPage.value = 1; fetchLogs(); }
function changePage(p) { currentPage.value = p; fetchLogs(); }
function resetFilters() {
    filters.value = { search: '', activity_type: '', date_from: '', date_to: '' };
    currentPage.value = 1;
    fetchLogs();
}

onMounted(() => { fetchLogs(); fetchTypes(); });

useFaq([
    { q: 'Apa saja yang dicatat di Audit Trail?', a: 'Semua aktivitas penting: upload/edit/hapus/approve/tolak dokumen, tambah/edit/hapus pegawai, login/logout, dan perubahan pengaturan.', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
    { q: 'Bagaimana cara melihat perubahan data?', a: 'Klik baris log yang punya tanda panah di kanan. Akan muncul perbandingan data sebelum dan sesudah perubahan.', icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4' },
    { q: 'Apakah log bisa dihapus?', a: 'Tidak. Audit trail bersifat permanen untuk keperluan compliance dan keamanan. Data log tidak bisa dihapus melalui UI.', icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' },
]);
</script>
