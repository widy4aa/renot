<template>
    <div class="space-y-5">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold" style="color:#111827;">Departemen</h2>
                <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">Kelola master data departemen dan divisi</p>
            </div>
            <button
                @click="openAdd"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors"
                style="background:#ED1B2F;"
                onmouseover="this.style.background='#c8102e'"
                onmouseout="this.style.background='#ED1B2F'"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Departemen
            </button>
        </div>

        <!-- ── Loading ────────────────────────────────────── -->
        <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="i in 6" :key="i" class="card-elevated rounded-xl p-5 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-2/3 mb-3"></div>
                <div class="h-3 bg-gray-100 rounded w-1/2"></div>
            </div>
        </div>

        <!-- ── Kosong ─────────────────────────────────────── -->
        <div v-else-if="departemenList.length === 0" class="card-elevated rounded-xl py-16 text-center">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#F3F4F6;">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#D1D5DB;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <p class="text-sm font-bold" style="color:#374151;">Belum ada departemen</p>
            <p class="text-xs mt-1 font-medium" style="color:#9CA3AF;">Klik "Tambah Departemen" untuk memulai</p>
        </div>

        <!-- ── Grid Departemen ─────────────────────────────── -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="dep in departemenList"
                :key="dep.id"
                class="card-elevated rounded-xl p-5 flex items-start justify-between gap-3"
            >
                <div class="flex items-start gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:#EFF6FF;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#006CB8;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold truncate" style="color:#111827;">{{ dep.name }}</p>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span class="text-xs font-semibold" style="color:#6B7280;">
                                {{ dep.pegawai_aktif_count }} pegawai aktif
                            </span>
                            <span v-if="dep.pegawai_count > dep.pegawai_aktif_count" class="text-xs" style="color:#9CA3AF;">
                                ({{ dep.pegawai_count }} total)
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <button
                        @click="openEdit(dep)"
                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                        style="color:#6B7280;"
                        onmouseover="this.style.background='#F3F4F6'"
                        onmouseout="this.style.background='transparent'"
                        title="Edit"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <button
                        @click="openDelete(dep)"
                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                        :style="dep.pegawai_aktif_count > 0 ? 'color:#D1D5DB; cursor:not-allowed;' : 'color:#ED1B2F;'"
                        :disabled="dep.pegawai_aktif_count > 0"
                        :title="dep.pegawai_aktif_count > 0 ? 'Tidak bisa dihapus — masih ada pegawai aktif' : 'Hapus'"
                        onmouseover="if(!this.disabled) this.style.background='#FEE2E2'"
                        onmouseout="this.style.background='transparent'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Modal Tambah / Edit ────────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="closeModal">
                <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4">
                    <h3 class="text-base font-bold mb-4" style="color:#111827;">
                        {{ editTarget ? 'Edit Departemen' : 'Tambah Departemen' }}
                    </h3>
                    <form @submit.prevent="submitForm">
                        <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">
                            Nama Departemen <span style="color:#ED1B2F;">*</span>
                        </label>
                        <input
                            v-model="formName"
                            type="text"
                            placeholder="cth. HSSE, Aviasi, Operasional..."
                            class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none transition-colors"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'"
                            @blur="e => e.target.style.borderColor='#E2E8F0'"
                            ref="nameInput"
                            required
                        />
                        <p v-if="formError" class="text-xs mt-1.5" style="color:#ED1B2F;">{{ formError }}</p>
                        <div class="flex gap-3 mt-5">
                            <button type="button" @click="closeModal" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                            <button type="submit" :disabled="isSaving" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                                style="background:#ED1B2F;" onmouseover="this.style.background='#c8102e'" onmouseout="this.style.background='#ED1B2F'">
                                {{ isSaving ? 'Menyimpan...' : (editTarget ? 'Simpan' : 'Tambah') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- ── Modal Hapus ────────────────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="deleteTarget = null">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#FEE2E2;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Hapus Departemen?</h4>
                    <p class="text-sm text-center mt-2" style="color:#6B7280;">
                        Departemen <span class="font-bold" style="color:#111827;">{{ deleteTarget?.name }}</span> akan dihapus permanen.
                    </p>
                    <div class="flex gap-3 mt-6">
                        <button @click="deleteTarget = null" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="deleteDep" :disabled="isDeleting" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                            style="background:#ED1B2F;" onmouseover="this.style.background='#c8102e'" onmouseout="this.style.background='#ED1B2F'">
                            {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useFaq } from '@/composables/useFaq.js';
import axios from 'axios';

const isLoading    = ref(true);
const isSaving     = ref(false);
const isDeleting   = ref(false);
const departemenList = ref([]);
const showFormModal = ref(false);
const editTarget    = ref(null);
const deleteTarget  = ref(null);
const formName      = ref('');
const formError     = ref('');
const nameInput     = ref(null);

async function fetchDepartemen() {
    isLoading.value = true;
    try {
        const { data } = await axios.get('/api/admin/departemen');
        departemenList.value = data.data;
    } catch {
        departemenList.value = [];
    } finally {
        isLoading.value = false;
    }
}

function openAdd() {
    editTarget.value = null;
    formName.value   = '';
    formError.value  = '';
    showFormModal.value = true;
    nextTick(() => nameInput.value?.focus());
}

function openEdit(dep) {
    editTarget.value = dep;
    formName.value   = dep.name;
    formError.value  = '';
    showFormModal.value = true;
    nextTick(() => nameInput.value?.focus());
}

function openDelete(dep) {
    deleteTarget.value = dep;
}

function closeModal() {
    showFormModal.value = false;
    editTarget.value    = null;
    formName.value      = '';
    formError.value     = '';
}

async function submitForm() {
    formError.value = '';
    if (!formName.value.trim()) {
        formError.value = 'Nama departemen wajib diisi.';
        return;
    }
    isSaving.value = true;
    try {
        if (editTarget.value) {
            const { data } = await axios.put(`/api/admin/departemen/${editTarget.value.id}`, { name: formName.value.trim() });
            const idx = departemenList.value.findIndex(d => d.id === editTarget.value.id);
            if (idx !== -1) departemenList.value[idx] = data.departemen;
        } else {
            const { data } = await axios.post('/api/admin/departemen', { name: formName.value.trim() });
            departemenList.value.push(data.departemen);
            departemenList.value.sort((a, b) => a.name.localeCompare(b.name));
        }
        closeModal();
    } catch (e) {
        formError.value = e.response?.data?.errors?.name?.[0]
            ?? e.response?.data?.message
            ?? 'Gagal menyimpan departemen.';
    } finally {
        isSaving.value = false;
    }
}

async function deleteDep() {
    if (!deleteTarget.value) return;
    isDeleting.value = true;
    try {
        await axios.delete(`/api/admin/departemen/${deleteTarget.value.id}`);
        departemenList.value = departemenList.value.filter(d => d.id !== deleteTarget.value.id);
        deleteTarget.value = null;
    } catch (e) {
        alert(e.response?.data?.message ?? 'Gagal menghapus departemen.');
    } finally {
        isDeleting.value = false;
    }
}

onMounted(fetchDepartemen);

useFaq([
    { q: 'Kapan departemen bisa dihapus?', a: 'Departemen hanya bisa dihapus jika tidak ada pegawai aktif di dalamnya. Nonaktifkan semua pegawai terlebih dahulu sebelum menghapus departemen.', icon: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16' },
    { q: 'Cara mengganti departemen pegawai?', a: 'Edit data pegawai melalui menu Pegawai di sidebar, lalu ubah dropdown departemen pada form edit pegawai.', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { q: 'Apakah departemen mempengaruhi filter dokumen?', a: 'Ya. Departemen dipakai sebagai filter di halaman Dokumen dan Approval untuk membantu admin menyaring dokumen berdasarkan divisi.', icon: 'M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z' },
]);
</script>
