<template>
    <div class="space-y-5">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5">
            <h2 class="text-2xl font-bold" style="color:#111827;">Kategori Sertifikasi</h2>
            <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">Kelola jenis sertifikasi dalam setiap kategori. Kategori utama tidak bisa dihapus.</p>
        </div>

        <!-- ── Loading ────────────────────────────────────── -->
        <div v-if="isLoading" class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div v-for="i in 2" :key="i" class="card-elevated rounded-xl p-6 animate-pulse space-y-3">
                <div class="h-5 bg-gray-200 rounded w-1/3"></div>
                <div v-for="j in 4" :key="j" class="h-10 bg-gray-100 rounded-xl"></div>
            </div>
        </div>

        <!-- ── 2 Panel Kategori ───────────────────────────── -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div v-for="kat in kategoriList" :key="kat.id" class="card-elevated rounded-xl overflow-hidden flex flex-col">

                <!-- Header kategori -->
                <div class="px-5 py-4 flex items-center justify-between" style="border-bottom:1px solid #F3F4F6; background:#FAFAFA;">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#EFF6FF;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#006CB8;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold" style="color:#111827;">{{ kat.name }}</p>
                            <p class="text-[10px] font-semibold" style="color:#9CA3AF;">{{ kat.types.length }} jenis sertifikasi</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:#F3F4F6; color:#9CA3AF;">Tetap</span>
                </div>

                <!-- List jenis -->
                <div class="flex-1">
                    <div v-if="kat.types.length === 0" class="px-5 py-8 text-center">
                        <p class="text-xs font-medium" style="color:#9CA3AF;">Belum ada jenis sertifikasi</p>
                    </div>
                    <div v-for="type in kat.types" :key="type.id"
                        class="px-5 py-3 flex items-center gap-3"
                        style="border-bottom:1px solid #F3F4F6;">
                        <!-- Edit mode -->
                        <template v-if="editingType?.id === type.id">
                            <input v-model="editForm.name" type="text" placeholder="Nama jenis"
                                class="flex-1 px-3 py-1.5 rounded-lg text-sm font-medium outline-none"
                                style="background:#F9FAFB; border:1.5px solid #ED1B2F; color:#111827;"
                                @keyup.enter="saveEdit(kat)" @keyup.escape="cancelEdit"/>
                            <input v-model="editForm.code" type="text" placeholder="Kode (opsional)"
                                class="w-24 px-3 py-1.5 rounded-lg text-sm font-medium outline-none"
                                style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                @keyup.enter="saveEdit(kat)" @keyup.escape="cancelEdit"/>
                            <button @click="saveEdit(kat)" :disabled="isSaving" class="text-xs font-bold px-2.5 py-1.5 rounded-lg" style="background:#F7FEE7; color:#5a6e0f;">
                                {{ isSaving ? '...' : 'Simpan' }}
                            </button>
                            <button @click="cancelEdit" class="text-xs font-bold px-2.5 py-1.5 rounded-lg" style="background:#F3F4F6; color:#6B7280;">Batal</button>
                        </template>
                        <!-- View mode -->
                        <template v-else>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold truncate" style="color:#111827;">{{ type.name }}</p>
                                <p v-if="type.code" class="text-xs font-medium" style="color:#9CA3AF;">{{ type.code }}</p>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <button @click="startEdit(type)" class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
                                    style="color:#6B7280;" onmouseover="this.style.background='#F3F4F6'" onmouseout="this.style.background='transparent'" title="Edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button @click="openDelete(type, kat)" class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
                                    style="color:#ED1B2F;" onmouseover="this.style.background='#FEE2E2'" onmouseout="this.style.background='transparent'" title="Hapus">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Tambah jenis baru -->
                <div class="px-5 py-3 border-t" style="border-color:#F3F4F6; background:#FAFAFA;">
                    <template v-if="addingTo === kat.id">
                        <div class="flex items-center gap-2">
                            <input v-model="addForm.name" type="text" placeholder="Nama jenis baru *"
                                class="flex-1 px-3 py-1.5 rounded-lg text-sm font-medium outline-none"
                                style="background:#fff; border:1.5px solid #ED1B2F; color:#111827;"
                                @keyup.enter="saveAdd(kat)" @keyup.escape="cancelAdd" ref="addInput"/>
                            <input v-model="addForm.code" type="text" placeholder="Kode"
                                class="w-20 px-3 py-1.5 rounded-lg text-sm font-medium outline-none"
                                style="background:#fff; border:1.5px solid #E2E8F0; color:#111827;"/>
                            <button @click="saveAdd(kat)" :disabled="isSaving" class="text-xs font-bold px-2.5 py-1.5 rounded-lg" style="background:#F7FEE7; color:#5a6e0f;">
                                {{ isSaving ? '...' : 'Tambah' }}
                            </button>
                            <button @click="cancelAdd" class="text-xs font-bold px-2.5 py-1.5 rounded-lg" style="background:#F3F4F6; color:#6B7280;">Batal</button>
                        </div>
                        <p v-if="addError" class="text-xs mt-1" style="color:#ED1B2F;">{{ addError }}</p>
                    </template>
                    <button v-else @click="startAdd(kat.id)"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold transition-colors"
                        style="color:#ED1B2F;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Jenis Sertifikasi
                    </button>
                </div>

            </div>
        </div>

        <!-- ── Modal Hapus ────────────────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="deleteTarget = null">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#FEE2E2;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Hapus Jenis Sertifikasi?</h4>
                    <p class="text-sm text-center mt-2" style="color:#6B7280;">
                        <span class="font-bold" style="color:#111827;">{{ deleteTarget?.type?.name }}</span> akan dihapus permanen.
                    </p>
                    <div class="flex gap-3 mt-6">
                        <button @click="deleteTarget = null" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="deleteType" :disabled="isDeleting" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
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

const isLoading  = ref(true);
const isSaving   = ref(false);
const isDeleting = ref(false);
const kategoriList = ref([]);
const editingType  = ref(null);
const editForm     = ref({ name: '', code: '' });
const addingTo     = ref(null);
const addForm      = ref({ name: '', code: '' });
const addError     = ref('');
const deleteTarget = ref(null); // { type, kat }
const addInput     = ref(null);

async function fetchKategori() {
    isLoading.value = true;
    try {
        const { data } = await axios.get('/api/admin/kategori');
        kategoriList.value = data.data;
    } catch {
        kategoriList.value = [];
    } finally {
        isLoading.value = false;
    }
}

function startEdit(type) {
    cancelAdd();
    editingType.value = type;
    editForm.value = { name: type.name, code: type.code ?? '' };
}
function cancelEdit() { editingType.value = null; }

async function saveEdit(kat) {
    if (!editForm.value.name.trim()) return;
    isSaving.value = true;
    try {
        const { data } = await axios.put(`/api/admin/kategori/${kat.id}/types/${editingType.value.id}`, editForm.value);
        const idx = kat.types.findIndex(t => t.id === editingType.value.id);
        if (idx !== -1) kat.types[idx] = data.type;
        editingType.value = null;
    } catch { /**/ } finally {
        isSaving.value = false;
    }
}

function startAdd(katId) {
    cancelEdit();
    addingTo.value = katId;
    addForm.value = { name: '', code: '' };
    addError.value = '';
    nextTick(() => addInput.value?.focus());
}
function cancelAdd() { addingTo.value = null; addError.value = ''; }

async function saveAdd(kat) {
    addError.value = '';
    if (!addForm.value.name.trim()) { addError.value = 'Nama jenis wajib diisi.'; return; }
    isSaving.value = true;
    try {
        const { data } = await axios.post(`/api/admin/kategori/${kat.id}/types`, addForm.value);
        kat.types.push(data.type);
        cancelAdd();
    } catch (e) {
        addError.value = e.response?.data?.message ?? 'Gagal menambah jenis sertifikasi.';
    } finally {
        isSaving.value = false;
    }
}

function openDelete(type, kat) { deleteTarget.value = { type, kat }; }

async function deleteType() {
    if (!deleteTarget.value) return;
    isDeleting.value = true;
    try {
        await axios.delete(`/api/admin/kategori/${deleteTarget.value.kat.id}/types/${deleteTarget.value.type.id}`);
        deleteTarget.value.kat.types = deleteTarget.value.kat.types.filter(t => t.id !== deleteTarget.value.type.id);
        deleteTarget.value = null;
    } catch (e) {
        alert(e.response?.data?.message ?? 'Gagal menghapus jenis sertifikasi.');
    } finally {
        isDeleting.value = false;
    }
}

onMounted(fetchKategori);

useFaq([
    { q: 'Apakah kategori HSSE dan Aviasi bisa dihapus?', a: 'Tidak. Kategori utama bersifat tetap dan tidak bisa dihapus. Hanya jenis sertifikasi di dalamnya yang bisa ditambah, diubah, atau dihapus.', icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z' },
    { q: 'Kapan jenis sertifikasi tidak bisa dihapus?', a: 'Jenis sertifikasi tidak bisa dihapus jika sudah dipakai oleh satu atau lebih dokumen pegawai. Pastikan tidak ada dokumen yang menggunakan jenis tersebut.', icon: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' },
    { q: 'Apa fungsi kolom Kode?', a: 'Kode adalah singkatan opsional untuk jenis sertifikasi (cth: GSI, RDS, PACE). Berguna untuk referensi internal dan pelaporan.', icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4' },
]);
</script>
