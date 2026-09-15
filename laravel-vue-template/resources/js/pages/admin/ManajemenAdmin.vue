<template>
    <div class="space-y-5">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold" style="color:#111827;">Manajemen Admin</h2>
                <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">Kelola akun admin yang memiliki akses ke sistem</p>
            </div>
            <button @click="openAdd"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white"
                style="background:#ED1B2F;" onmouseover="this.style.background='#c8102e'" onmouseout="this.style.background='#ED1B2F'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Admin
            </button>
        </div>

        <!-- ── Loading ────────────────────────────────────── -->
        <div v-if="isLoading" class="space-y-3">
            <div v-for="i in 3" :key="i" class="card-elevated rounded-xl p-5 animate-pulse flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-gray-200 shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                    <div class="h-3 bg-gray-100 rounded w-1/3"></div>
                </div>
            </div>
        </div>

        <!-- ── List Admin ─────────────────────────────────── -->
        <div v-else class="space-y-2">
            <div v-for="admin in adminList" :key="admin.id" class="card-elevated rounded-xl overflow-hidden">
                <div class="flex">
                    <!-- Accent bar kiri -->
                    <div class="w-1 shrink-0" style="background:#006CB8;"></div>

                    <!-- KIRI: Avatar + identitas -->
                    <div class="flex-1 px-4 py-3 flex items-center gap-3 min-w-0">
                        <img
                            v-if="admin.avatar"
                            :src="admin.avatar"
                            :alt="admin.name"
                            class="w-9 h-9 rounded-xl object-cover shrink-0"
                            style="border:1.5px solid #E2E8F0;"
                        />
                        <div v-else class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0" style="background:#006CB8;">
                            {{ admin.name?.charAt(0)?.toUpperCase() }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-bold" style="color:#111827;">{{ admin.name }}</p>
                                <span v-if="admin.id === authUser?.id" class="text-[10px] font-bold px-1.5 py-0.5 rounded-full" style="background:#EFF6FF; color:#006CB8;">Anda</span>
                            </div>
                            <p class="text-xs mt-0.5" style="color:#6B7280;">{{ admin.email }}</p>
                            <p class="text-[10px] mt-0.5" style="color:#9CA3AF;">Bergabung: {{ formatDate(admin.created_at) }}</p>
                        </div>
                    </div>

                    <!-- KANAN: Tombol aksi 2x2 -->
                    <div class="w-44 shrink-0 border-l flex flex-col justify-center gap-1.5 px-3 py-3" style="border-color:#F3F4F6;">
                        <!-- Baris 1: Edit + Reset Password -->
                        <div class="grid grid-cols-2 gap-1">
                            <button @click="openEdit(admin)"
                                class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold"
                                style="background:#F3F4F6; color:#374151;">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </button>
                            <button @click="openResetPwd(admin)"
                                class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold"
                                style="background:#EFF6FF; color:#006CB8;">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                Reset
                            </button>
                        </div>
                        <!-- Baris 2: Hapus full-width -->
                        <button @click="openDelete(admin)"
                            :disabled="admin.id === authUser?.id"
                            class="inline-flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold w-full disabled:opacity-40 disabled:cursor-not-allowed"
                            :title="admin.id === authUser?.id ? 'Tidak bisa menghapus akun sendiri' : 'Hapus'"
                            style="background:#FEE2E2; color:#ED1B2F;">
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Modal Tambah / Edit ────────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="closeModal">
                <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4">
                    <h3 class="text-base font-bold mb-4" style="color:#111827;">{{ editTarget ? 'Edit Admin' : 'Tambah Admin Baru' }}</h3>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Nama <span style="color:#ED1B2F;">*</span></label>
                            <input v-model="form.name" type="text" placeholder="Nama lengkap" required
                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                                style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Email <span style="color:#ED1B2F;">*</span></label>
                            <input v-model="form.email" type="email" placeholder="email@perusahaan.com" required
                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                                style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                        </div>
                        <div v-if="!editTarget">
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Password <span style="color:#ED1B2F;">*</span></label>
                            <input v-model="form.password" type="password" placeholder="Minimal 8 karakter" required
                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                                style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                        </div>
                        <p v-if="formError" class="text-xs" style="color:#ED1B2F;">{{ formError }}</p>
                        <div class="flex gap-3 pt-1">
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

        <!-- ── Modal Reset Password ───────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="resetTarget" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="resetTarget = null">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <h4 class="text-base font-bold mb-1" style="color:#111827;">Reset Password</h4>
                    <p class="text-sm mb-4" style="color:#6B7280;">Untuk admin <span class="font-bold" style="color:#111827;">{{ resetTarget?.name }}</span></p>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Password Baru <span style="color:#ED1B2F;">*</span></label>
                    <input v-model="newPassword" type="password" placeholder="Minimal 8 karakter"
                        class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                        @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                    <p v-if="resetError" class="text-xs mt-1" style="color:#ED1B2F;">{{ resetError }}</p>
                    <div class="flex gap-3 mt-5">
                        <button @click="resetTarget = null; newPassword = ''; resetError = ''" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="submitReset" :disabled="isSaving" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                            style="background:#006CB8;" onmouseover="this.style.background='#005a9e'" onmouseout="this.style.background='#006CB8'">
                            {{ isSaving ? '...' : 'Reset' }}
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
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Hapus Admin?</h4>
                    <p class="text-sm text-center mt-2" style="color:#6B7280;">Akun <span class="font-bold" style="color:#111827;">{{ deleteTarget?.name }}</span> akan dihapus permanen.</p>
                    <div class="flex gap-3 mt-6">
                        <button @click="deleteTarget = null" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="deleteAdmin" :disabled="isProcessing" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
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
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useFaq } from '@/composables/useFaq.js';
import axios from 'axios';

const auth = useAuthStore();
const authUser = auth.user;

const isLoading   = ref(true);
const isSaving    = ref(false);
const isProcessing = ref(false);
const adminList   = ref([]);
const showFormModal = ref(false);
const editTarget    = ref(null);
const deleteTarget  = ref(null);
const resetTarget   = ref(null);
const form      = ref({ name: '', email: '', password: '' });
const formError = ref('');
const newPassword = ref('');
const resetError  = ref('');

function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

async function fetchAdmins() {
    isLoading.value = true;
    try {
        const { data } = await axios.get('/api/admin/admins');
        adminList.value = data.data;
    } catch { adminList.value = []; } finally { isLoading.value = false; }
}

function openAdd() { editTarget.value = null; form.value = { name: '', email: '', password: '' }; formError.value = ''; showFormModal.value = true; }
function openEdit(a) { editTarget.value = a; form.value = { name: a.name, email: a.email, password: '' }; formError.value = ''; showFormModal.value = true; }
function closeModal() { showFormModal.value = false; }
function openResetPwd(a) { resetTarget.value = a; newPassword.value = ''; resetError.value = ''; }
function openDelete(a) { deleteTarget.value = a; }

async function submitForm() {
    formError.value = '';
    isSaving.value = true;
    try {
        if (editTarget.value) {
            const { data } = await axios.put(`/api/admin/admins/${editTarget.value.id}`, { name: form.value.name, email: form.value.email });
            const idx = adminList.value.findIndex(a => a.id === editTarget.value.id);
            if (idx !== -1) adminList.value[idx] = data.admin;
        } else {
            const { data } = await axios.post('/api/admin/admins', form.value);
            adminList.value.push(data.admin);
        }
        closeModal();
    } catch (e) {
        const err = e.response?.data;
        if (err?.errors) formError.value = Object.values(err.errors)[0][0];
        else formError.value = err?.message ?? 'Gagal menyimpan.';
    } finally { isSaving.value = false; }
}

async function submitReset() {
    resetError.value = '';
    if (!newPassword.value || newPassword.value.length < 8) { resetError.value = 'Password minimal 8 karakter.'; return; }
    isSaving.value = true;
    try {
        await axios.post(`/api/admin/admins/${resetTarget.value.id}/reset-password`, { password: newPassword.value });
        resetTarget.value = null;
    } catch (e) { resetError.value = e.response?.data?.message ?? 'Gagal.'; } finally { isSaving.value = false; }
}

async function deleteAdmin() {
    isProcessing.value = true;
    try {
        await axios.delete(`/api/admin/admins/${deleteTarget.value.id}`);
        adminList.value = adminList.value.filter(a => a.id !== deleteTarget.value.id);
        deleteTarget.value = null;
    } catch { /**/ } finally { isProcessing.value = false; }
}

onMounted(fetchAdmins);

useFaq([
    { q: 'Apakah bisa menghapus akun sendiri?', a: 'Tidak. Tombol hapus dinonaktifkan untuk akun yang sedang login untuk mencegah sistem tanpa admin.', icon: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' },
    { q: 'Berapa jumlah admin yang bisa dibuat?', a: 'Tidak ada batasan jumlah akun admin. Buat sebanyak yang diperlukan sesuai kebutuhan organisasi.', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
    { q: 'Apa perbedaan admin dengan pegawai?', a: 'Admin bisa melihat dan mengelola semua data pegawai, melakukan approval, dan mengkonfigurasi sistem. Pegawai hanya bisa mengelola dokumen milik sendiri.', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
]);
</script>
