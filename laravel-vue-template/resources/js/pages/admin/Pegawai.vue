<template>
    <div class="space-y-5">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold" style="color:#111827;">Pegawai</h2>
                <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">Kelola data akun dan profil seluruh pegawai</p>
            </div>
            <RouterLink
                :to="{ name: 'admin.pegawai.tambah' }"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors"
                style="background:#ED1B2F;"
                onmouseover="this.style.background='#c8102e'"
                onmouseout="this.style.background='#ED1B2F'"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Pegawai
            </RouterLink>
        </div>

        <!-- ── Filter Bar ─────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-5 py-4 space-y-3">
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
                        placeholder="Cari nama, email, atau nomor pegawai..."
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                        style="background:#F9FAFB; border:1.5px solid #ED1B2F; color:#111827;"
                        @focus="e => e.target.style.borderColor='#c8102e'"
                        @blur="e => e.target.style.borderColor='#ED1B2F'"
                    />
                </div>
                <span class="text-xs font-semibold shrink-0" style="color:#6B7280;">{{ filteredList.length }} pegawai</span>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <select v-model="filterDepartemen" class="px-3 py-2 rounded-xl text-xs font-semibold outline-none cursor-pointer"
                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'">
                    <option value="">Semua Departemen</option>
                    <option v-for="d in departemenOptions" :key="d" :value="d">{{ d }}</option>
                </select>
                <select v-model="filterStatus" class="px-3 py-2 rounded-xl text-xs font-semibold outline-none cursor-pointer"
                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;"
                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
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
            <div v-for="i in 5" :key="i" class="card-elevated rounded-xl p-5 animate-pulse flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-gray-200 shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                    <div class="h-3 bg-gray-100 rounded w-1/3"></div>
                </div>
                <div class="h-6 bg-gray-100 rounded-full w-16 shrink-0"></div>
            </div>
        </div>

        <!-- ── Kosong ─────────────────────────────────────── -->
        <div v-else-if="filteredList.length === 0" class="card-elevated rounded-xl py-16 text-center">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#F3F4F6;">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#D1D5DB;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-sm font-bold" style="color:#374151;">Tidak ada pegawai ditemukan</p>
            <p class="text-xs mt-1 font-medium" style="color:#9CA3AF;">{{ hasFilter ? 'Coba ubah filter' : 'Belum ada pegawai terdaftar' }}</p>
        </div>

        <!-- ── List Pegawai ───────────────────────────────── -->
        <div v-else class="space-y-2">
            <div v-for="p in filteredList" :key="p.id" class="card-elevated rounded-xl overflow-hidden">
                <div class="flex">
                    <!-- Accent bar kiri -->
                    <div class="w-1 shrink-0" :style="{ background: p.is_active ? '#ACC42A' : '#9CA3AF' }"></div>

                    <!-- KIRI: Avatar + identitas -->
                    <div class="flex-1 px-4 py-3 flex items-center gap-3 min-w-0">
                        <div class="shrink-0">
                            <img v-if="p.avatar" :src="p.avatar" class="w-9 h-9 rounded-xl object-cover" style="border:1.5px solid #E2E8F0;"/>
                            <div v-else class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold" style="background:#ED1B2F;">
                                {{ p.name?.charAt(0)?.toUpperCase() }}
                            </div>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-bold" style="color:#111827;">{{ p.name }}</p>
                                <span v-if="!p.is_active" class="text-[10px] font-bold px-1.5 py-0.5 rounded-full" style="background:#F3F4F6; color:#9CA3AF;">Nonaktif</span>
                            </div>
                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                <span class="text-xs" style="color:#6B7280;">{{ p.email }}</span>
                                <span class="text-xs" style="color:#D1D5DB;">·</span>
                                <span class="text-xs font-semibold" style="color:#374151;">{{ p.employee_number }}</span>
                                <span class="text-xs" style="color:#D1D5DB;">·</span>
                                <span class="text-xs" style="color:#9CA3AF;">{{ p.department?.name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- TENGAH: Badge dokumen -->
                    <div class="w-36 shrink-0 border-l flex flex-col justify-center gap-1 px-4 py-3" style="border-color:#F3F4F6;">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full self-start" style="background:#F3F4F6; color:#374151;">{{ p.documents_count }} dokumen</span>
                        <span v-if="p.expired_count > 0" class="text-xs font-bold px-2 py-0.5 rounded-full self-start" style="background:#FEE2E2; color:#991B1B;">{{ p.expired_count }} expired</span>
                        <span v-if="p.pending_count > 0" class="text-xs font-bold px-2 py-0.5 rounded-full self-start" style="background:#FEF3C7; color:#92400E;">{{ p.pending_count }} pending</span>
                    </div>

                    <!-- KANAN: Tombol aksi 2x2 -->
                    <div class="w-44 shrink-0 border-l flex flex-col justify-center gap-1.5 px-3 py-3" style="border-color:#F3F4F6;">
                        <!-- Baris 1: Edit + Aktifkan/Nonaktifkan -->
                        <div class="grid grid-cols-2 gap-1">
                            <RouterLink :to="{ name: 'admin.pegawai.edit', params: { id: p.id } }"
                                class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold"
                                style="background:#F3F4F6; color:#374151;">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </RouterLink>
                            <button @click="toggleActive(p)"
                                class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold"
                                :style="p.is_active ? 'background:#FEF3C7; color:#92400E;' : 'background:#F7FEE7; color:#5a6e0f;'">
                                {{ p.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </div>
                        <!-- Baris 2: Reset Password + Hapus -->
                        <div class="grid grid-cols-2 gap-1">
                            <button @click="openResetPwd(p)"
                                class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold"
                                style="background:#EFF6FF; color:#006CB8;">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                Reset
                            </button>
                            <button @click="openDelete(p)"
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

        <!-- ── Modal Reset Password ───────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="resetTarget" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="resetTarget = null">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <h4 class="text-base font-bold mb-1" style="color:#111827;">Reset Password</h4>
                    <p class="text-sm mb-4" style="color:#6B7280;">Untuk pegawai <span class="font-bold" style="color:#111827;">{{ resetTarget?.name }}</span></p>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Password Baru <span style="color:#ED1B2F;">*</span></label>
                    <input v-model="newPassword" type="password" placeholder="Minimal 8 karakter"
                        class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                        @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                    <p v-if="resetError" class="text-xs mt-1" style="color:#ED1B2F;">{{ resetError }}</p>
                    <div class="flex gap-3 mt-5">
                        <button @click="resetTarget = null; newPassword = ''; resetError = ''" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="submitResetPwd" :disabled="isProcessing" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                            style="background:#006CB8;" onmouseover="this.style.background='#005a9e'" onmouseout="this.style.background='#006CB8'">
                            {{ isProcessing ? 'Menyimpan...' : 'Reset' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ── Modal Hapus ────────────────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="deleteTarget = null">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#FEE2E2;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Hapus Pegawai?</h4>
                    <p class="text-sm text-center mt-2" style="color:#6B7280;">
                        Akun <span class="font-bold" style="color:#111827;">{{ deleteTarget?.name }}</span> beserta seluruh dokumennya akan dihapus permanen.
                    </p>
                    <div class="flex gap-3 mt-6">
                        <button @click="deleteTarget = null" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="deletePegawai" :disabled="isProcessing" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
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
import { useFaq } from '@/composables/useFaq.js';
import axios from 'axios';

const isLoading   = ref(true);
const isProcessing = ref(false);
const pegawaiList  = ref([]);
const search          = ref('');
const filterDepartemen = ref('');
const filterStatus     = ref('');
const resetTarget  = ref(null);
const deleteTarget = ref(null);
const newPassword  = ref('');
const resetError   = ref('');

const departemenOptions = computed(() =>
    [...new Set(pegawaiList.value.map(p => p.department?.name).filter(Boolean))].sort()
);

const hasFilter = computed(() => !!(search.value || filterDepartemen.value || filterStatus.value));

const filteredList = computed(() => {
    let list = pegawaiList.value;
    if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(p =>
            p.name?.toLowerCase().includes(q) ||
            p.email?.toLowerCase().includes(q) ||
            p.employee_number?.toLowerCase().includes(q)
        );
    }
    if (filterDepartemen.value) list = list.filter(p => p.department?.name === filterDepartemen.value);
    if (filterStatus.value === 'aktif')    list = list.filter(p => p.is_active);
    if (filterStatus.value === 'nonaktif') list = list.filter(p => !p.is_active);
    return list;
});

function resetFilters() { search.value = filterDepartemen.value = filterStatus.value = ''; }

async function fetchPegawai() {
    isLoading.value = true;
    try {
        const { data } = await axios.get('/api/admin/pegawai');
        pegawaiList.value = data.data;
    } catch {
        pegawaiList.value = [];
    } finally {
        isLoading.value = false;
    }
}

async function toggleActive(p) {
    try {
        const { data } = await axios.post(`/api/admin/pegawai/${p.id}/toggle-active`);
        const idx = pegawaiList.value.findIndex(x => x.id === p.id);
        if (idx !== -1) pegawaiList.value[idx].is_active = data.is_active;
    } catch { /**/ }
}

function openResetPwd(p) { resetTarget.value = p; newPassword.value = ''; resetError.value = ''; }
function openDelete(p) { deleteTarget.value = p; }

async function submitResetPwd() {
    resetError.value = '';
    if (!newPassword.value || newPassword.value.length < 8) {
        resetError.value = 'Password minimal 8 karakter.';
        return;
    }
    isProcessing.value = true;
    try {
        await axios.post(`/api/admin/pegawai/${resetTarget.value.id}/reset-password`, { password: newPassword.value });
        resetTarget.value = null;
        newPassword.value = '';
    } catch (e) {
        resetError.value = e.response?.data?.message ?? 'Gagal mereset password.';
    } finally {
        isProcessing.value = false;
    }
}

async function deletePegawai() {
    isProcessing.value = true;
    try {
        await axios.delete(`/api/admin/pegawai/${deleteTarget.value.id}`);
        pegawaiList.value = pegawaiList.value.filter(p => p.id !== deleteTarget.value.id);
        deleteTarget.value = null;
    } catch { /**/ } finally {
        isProcessing.value = false;
    }
}

onMounted(fetchPegawai);

useFaq([
    { q: 'Cara menambah akun pegawai baru?', a: 'Klik tombol "Tambah Pegawai" di kanan atas, isi data lengkap termasuk email, NIP, departemen, dan password awal.', icon: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z' },
    { q: 'Apa bedanya nonaktifkan vs hapus?', a: 'Nonaktifkan hanya menonaktifkan akses login, data dan dokumen tetap tersimpan. Hapus menghapus akun dan SEMUA dokumen pegawai secara permanen.', icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { q: 'Cara reset password pegawai?', a: 'Klik tombol "Reset Password" pada baris pegawai, lalu masukkan password baru (minimal 8 karakter). Sampaikan password baru ke pegawai bersangkutan.', icon: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z' },
]);
</script>
