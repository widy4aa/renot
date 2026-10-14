<template>
    <div class="space-y-5">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold" style="color:#111827;">Pendaftaran Akun</h2>
                <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">
                    <span v-if="!isLoading">{{ allRegistrations.length }} pendaftaran menunggu persetujuan</span>
                    <span v-else>Memuat...</span>
                </p>
            </div>
            <span v-if="allRegistrations.length > 0" class="text-sm font-bold px-3 py-1.5 rounded-full" style="background:#FEF3C7; color:#92400E;">
                {{ filteredRegistrations.length }} pending
            </span>
        </div>

        <!-- ── Filter ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/></svg>
                    </span>
                    <input v-model="search" type="text" placeholder="Cari nama, email, atau NIP..."
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                        style="background:#F9FAFB; border:1.5px solid #ED1B2F; color:#111827;"
                        @focus="e => e.target.style.borderColor='#c8102e'" @blur="e => e.target.style.borderColor='#ED1B2F'"/>
                </div>
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
        <div v-else-if="filteredRegistrations.length === 0" class="card-elevated rounded-xl py-20 text-center">
            <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#F7FEE7;">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ACC42A;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm font-bold" style="color:#374151;">
                {{ search ? 'Tidak ada pendaftaran ditemukan' : 'Tidak ada pendaftaran yang menunggu persetujuan' }}
            </p>
            <p class="text-xs mt-1 font-medium" style="color:#9CA3AF;">
                {{ search ? 'Coba ubah kata kunci pencarian' : 'Semua pendaftaran sudah diproses' }}
            </p>
        </div>

        <!-- ── List Pendaftaran ────────────────────────────── -->
        <div v-else class="space-y-2">
            <div v-for="reg in filteredRegistrations" :key="reg.id" class="card-elevated rounded-xl overflow-hidden">
                <div class="flex">
                    <div class="w-1 shrink-0" style="background:#D97706;"></div>

                    <!-- KIRI: Avatar + info -->
                    <div class="flex-1 px-4 py-3 flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-white text-sm font-bold" style="background:#D97706;">
                            {{ reg.name?.charAt(0)?.toUpperCase() }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold" style="color:#111827;">{{ reg.name }}</p>
                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                <span class="text-xs" style="color:#6B7280;">{{ reg.email }}</span>
                                <span class="text-xs" style="color:#D1D5DB;">·</span>
                                <span class="text-xs" style="color:#9CA3AF;">{{ reg.employee_number }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- TENGAH: Departemen + tanggal -->
                    <div class="w-48 shrink-0 border-l flex flex-col justify-center px-4 py-3" style="border-color:#F3F4F6;">
                        <p class="text-xs font-bold truncate" style="color:#374151;">{{ reg.department?.name ?? '-' }}</p>
                        <p class="text-xs mt-0.5" style="color:#9CA3AF;">{{ reg.phone ?? '-' }}</p>
                        <p class="text-xs mt-0.5" style="color:#9CA3AF;">{{ timeAgo(reg.registered_at) }}</p>
                    </div>

                    <!-- KANAN: Tombol -->
                    <div class="w-44 shrink-0 border-l flex flex-col justify-center gap-1.5 px-3 py-3" style="border-color:#F3F4F6;">
                        <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full self-center mb-0.5" style="background:#FEF3C7; color:#92400E;">Menunggu</span>
                        <div class="grid grid-cols-2 gap-1">
                            <button @click="openApprove(reg)"
                                class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold"
                                style="background:#F7FEE7; color:#5a6e0f;">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Setujui
                            </button>
                            <button @click="openReject(reg)"
                                class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold"
                                style="background:#FCE7F3; color:#DB2777;">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Tolak
                            </button>
                        </div>
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
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Setujui Pendaftaran?</h4>
                    <p class="text-sm text-center mt-2" style="color:#6B7280;">
                        Akun <span class="font-bold" style="color:#111827;">{{ approveTarget?.name }}</span> ({{ approveTarget?.email }}) akan diaktifkan. Pegawai akan menerima email konfirmasi.
                    </p>
                    <div class="flex gap-3 mt-6">
                        <button @click="approveTarget = null" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="approveReg" :disabled="isProcessing" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
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
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Tolak Pendaftaran</h4>
                    <p class="text-sm text-center mt-1 mb-4" style="color:#6B7280;">Alasan penolakan wajib diisi dan akan dikirim ke <span class="font-bold">{{ rejectTarget?.name }}</span>.</p>
                    <textarea v-model="rejectionReason" rows="3" placeholder="Tuliskan alasan penolakan..."
                        class="w-full px-4 py-3 rounded-xl text-sm font-medium outline-none resize-none"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                        @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'">
                    </textarea>
                    <p v-if="rejectError" class="text-xs mt-1" style="color:#ED1B2F;">{{ rejectError }}</p>
                    <div class="flex gap-3 mt-4">
                        <button @click="rejectTarget = null; rejectionReason = ''; rejectError = ''" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="rejectReg" :disabled="isProcessing" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
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

const isLoading        = ref(true);
const isProcessing     = ref(false);
const allRegistrations = ref([]);
const search           = ref('');
const approveTarget    = ref(null);
const rejectTarget     = ref(null);
const rejectionReason  = ref('');
const rejectError      = ref('');

const filteredRegistrations = computed(() => {
    if (!search.value) return allRegistrations.value;
    const q = search.value.toLowerCase();
    return allRegistrations.value.filter(r =>
        r.name?.toLowerCase().includes(q) ||
        r.email?.toLowerCase().includes(q) ||
        r.employee_number?.toLowerCase().includes(q)
    );
});

function timeAgo(iso) {
    if (!iso) return '';
    const diff = Math.floor((Date.now() - new Date(iso)) / 1000);
    if (diff < 60)    return `${diff}d lalu`;
    if (diff < 3600)  return `${Math.floor(diff / 60)}m lalu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}j lalu`;
    return `${Math.floor(diff / 86400)} hari lalu`;
}

async function fetchRegistrations() {
    isLoading.value = true;
    try {
        const { data } = await axios.get('/api/admin/registrations');
        allRegistrations.value = data.data;
    } catch {
        allRegistrations.value = [];
    } finally {
        isLoading.value = false;
    }
}

function openApprove(reg) { approveTarget.value = reg; }
function openReject(reg)  { rejectTarget.value = reg; rejectionReason.value = ''; rejectError.value = ''; }

async function approveReg() {
    if (!approveTarget.value) return;
    isProcessing.value = true;
    try {
        await axios.post(`/api/admin/registrations/${approveTarget.value.id}/approve`);
        allRegistrations.value = allRegistrations.value.filter(r => r.id !== approveTarget.value.id);
        approveTarget.value = null;
    } catch { /**/ } finally {
        isProcessing.value = false;
    }
}

async function rejectReg() {
    rejectError.value = '';
    if (!rejectionReason.value.trim() || rejectionReason.value.trim().length < 5) {
        rejectError.value = 'Alasan penolakan minimal 5 karakter.';
        return;
    }
    isProcessing.value = true;
    try {
        await axios.post(`/api/admin/registrations/${rejectTarget.value.id}/reject`, {
            rejection_reason: rejectionReason.value,
        });
        allRegistrations.value = allRegistrations.value.filter(r => r.id !== rejectTarget.value.id);
        rejectTarget.value = null;
        rejectionReason.value = '';
    } catch (e) {
        rejectError.value = e.response?.data?.message ?? 'Gagal menolak pendaftaran.';
    } finally {
        isProcessing.value = false;
    }
}

onMounted(fetchRegistrations);

useFaq([
    { q: 'Bagaimana cara menyetujui pendaftaran?', a: 'Klik tombol "Setujui" pada baris pendaftaran. Akun pegawai akan diaktifkan dan pegawai menerima email konfirmasi.', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { q: 'Apa yang terjadi setelah pendaftaran ditolak?', a: 'Pegawai menerima email dengan alasan penolakan. Pegawai bisa mendaftar ulang dengan email yang sama.', icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { q: 'Apakah admin masih bisa menambah pegawai manual?', a: 'Ya. Fitur pendaftaran mandiri ini bersifat tambahan. Admin tetap bisa menambah pegawai dari menu Pegawai seperti biasa.', icon: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z' },
]);
</script>
