<template>
    <div class="space-y-5">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center gap-4">
            <RouterLink
                :to="isEdit ? { name: 'admin.pegawai' } : { name: 'admin.pegawai' }"
                class="inline-flex items-center justify-center w-9 h-9 rounded-xl transition-colors shrink-0"
                style="background:#F3F4F6; color:#374151;"
                onmouseover="this.style.background='#E5E7EB'" onmouseout="this.style.background='#F3F4F6'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </RouterLink>
            <div>
                <h2 class="text-2xl font-bold" style="color:#111827;">{{ isEdit ? 'Edit Pegawai' : 'Tambah Pegawai' }}</h2>
                <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">
                    {{ isEdit ? 'Perbarui data akun pegawai' : 'Buat akun pegawai baru' }}
                </p>
            </div>
        </div>

        <!-- ── Loading ────────────────────────────────────── -->
        <div v-if="isLoading" class="card-elevated rounded-2xl p-6 animate-pulse space-y-4">
            <div v-for="i in 6" :key="i" class="h-12 bg-gray-100 rounded-xl"></div>
        </div>

        <!-- ── Form ───────────────────────────────────────── -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <form @submit.prevent="submitForm" class="card-elevated rounded-2xl px-6 py-5 space-y-5">

                <!-- Nama -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Nama Lengkap <span style="color:#ED1B2F;">*</span></label>
                    <input v-model="form.name" type="text" placeholder="Nama lengkap pegawai" required
                        class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                        @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                    <p v-if="errors.name" class="text-xs mt-1" style="color:#ED1B2F;">{{ errors.name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Email <span style="color:#ED1B2F;">*</span></label>
                    <input v-model="form.email" type="email" placeholder="email@perusahaan.com" required
                        class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                        @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                    <p v-if="errors.email" class="text-xs mt-1" style="color:#ED1B2F;">{{ errors.email }}</p>
                </div>

                <!-- NIP + HP -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">No. Pegawai / NIP <span style="color:#ED1B2F;">*</span></label>
                        <input v-model="form.employee_number" type="text" placeholder="EMP-001" required
                            class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                        <p v-if="errors.employee_number" class="text-xs mt-1" style="color:#ED1B2F;">{{ errors.employee_number }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">No. HP</label>
                        <input v-model="form.phone" type="text" placeholder="08xxxxxxxxxx"
                            class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                    </div>
                </div>

                <!-- Departemen -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Departemen <span style="color:#ED1B2F;">*</span></label>
                    <select v-model="form.department_id" required
                        class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                        @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'">
                        <option value="">-- Pilih Departemen --</option>
                        <option v-for="d in departemenList" :key="d.id" :value="d.id">{{ d.name }}</option>
                    </select>
                    <p v-if="errors.department_id" class="text-xs mt-1" style="color:#ED1B2F;">{{ errors.department_id }}</p>
                </div>

                <!-- Password (hanya saat tambah baru) -->
                <div v-if="!isEdit">
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Password <span style="color:#ED1B2F;">*</span></label>
                    <input v-model="form.password" type="password" placeholder="Minimal 8 karakter" required
                        class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                        @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                    <p v-if="errors.password" class="text-xs mt-1" style="color:#ED1B2F;">{{ errors.password }}</p>
                </div>

                <!-- Status Aktif -->
                <div class="flex items-center justify-between px-4 py-3 rounded-xl" style="background:#F9FAFB; border:1.5px solid #E2E8F0;">
                    <div>
                        <p class="text-sm font-semibold" style="color:#111827;">Status Akun</p>
                        <p class="text-xs font-medium" style="color:#6B7280;">{{ form.is_active ? 'Akun aktif dan bisa login' : 'Akun dinonaktifkan' }}</p>
                    </div>
                    <button type="button" @click="form.is_active = !form.is_active"
                        class="w-11 h-6 rounded-full transition-colors relative"
                        :style="form.is_active ? 'background:#ACC42A;' : 'background:#E2E8F0;'">
                        <span class="absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all"
                            :style="form.is_active ? 'left:calc(100% - 1.375rem);' : 'left:0.125rem;'"></span>
                    </button>
                </div>

                <!-- Error umum -->
                <p v-if="errors.general" class="text-xs px-4 py-3 rounded-xl" style="background:#FEE2E2; color:#ED1B2F;">{{ errors.general }}</p>

                <!-- Submit -->
                <div class="flex gap-3">
                    <button type="submit" :disabled="isSaving"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                        style="background:#ED1B2F;" onmouseover="if(!this.disabled) this.style.background='#c8102e'" onmouseout="this.style.background='#ED1B2F'">
                        {{ isSaving ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Buat Akun') }}
                    </button>
                    <RouterLink :to="{ name: 'admin.pegawai' }" class="px-6 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">
                        Batal
                    </RouterLink>
                </div>
            </form>

            <!-- Info card kanan -->
            <div class="card-elevated rounded-2xl px-6 py-5 h-fit">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#EFF6FF;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#006CB8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold" style="color:#111827;">Panduan</h3>
                </div>
                <ul class="space-y-3">
                    <li class="flex items-start gap-2.5">
                        <span class="w-1.5 h-1.5 rounded-full mt-1.5 shrink-0" style="background:#ED1B2F;"></span>
                        <p class="text-xs font-medium leading-relaxed" style="color:#6B7280;">Email digunakan untuk login dan pengiriman notifikasi reminder dokumen.</p>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-1.5 h-1.5 rounded-full mt-1.5 shrink-0" style="background:#ED1B2F;"></span>
                        <p class="text-xs font-medium leading-relaxed" style="color:#6B7280;">No. Pegawai (NIP) harus unik dan tidak bisa diubah setelah disimpan.</p>
                    </li>
                    <li v-if="!isEdit" class="flex items-start gap-2.5">
                        <span class="w-1.5 h-1.5 rounded-full mt-1.5 shrink-0" style="background:#ED1B2F;"></span>
                        <p class="text-xs font-medium leading-relaxed" style="color:#6B7280;">Password awal minimal 8 karakter. Sampaikan ke pegawai untuk login pertama kali.</p>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-1.5 h-1.5 rounded-full mt-1.5 shrink-0" style="background:#ED1B2F;"></span>
                        <p class="text-xs font-medium leading-relaxed" style="color:#6B7280;">Akun nonaktif tidak bisa login namun data dan dokumennya tetap tersimpan.</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useFaq } from '@/composables/useFaq.js';
import axios from 'axios';

const route  = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);

const isLoading = ref(true);
const isSaving  = ref(false);
const errors    = ref({});
const departemenList = ref([]);

const form = ref({
    name:            '',
    email:           '',
    employee_number: '',
    phone:           '',
    department_id:   '',
    password:        '',
    is_active:       true,
});

async function fetchDepartemen() {
    try {
        const { data } = await axios.get('/api/admin/departemen');
        departemenList.value = data.data;
    } catch { /**/ }
}

async function fetchPegawai() {
    if (!isEdit.value) return;
    try {
        const { data } = await axios.get(`/api/admin/pegawai/${route.params.id}`);
        form.value.name            = data.name;
        form.value.email           = data.email;
        form.value.employee_number = data.employee_number;
        form.value.phone           = data.phone ?? '';
        form.value.department_id   = data.department?.id ?? '';
        form.value.is_active       = data.is_active;
    } catch { /**/ }
}

async function submitForm() {
    errors.value = {};
    isSaving.value = true;
    const payload = { ...form.value };
    if (isEdit.value) delete payload.password;

    try {
        if (isEdit.value) {
            await axios.post(`/api/admin/pegawai/${route.params.id}`, payload);
        } else {
            await axios.post('/api/admin/pegawai', payload);
        }
        router.push({ name: 'admin.pegawai' });
    } catch (e) {
        const errData = e.response?.data;
        if (errData?.errors) {
            Object.keys(errData.errors).forEach(k => { errors.value[k] = errData.errors[k][0]; });
        } else {
            errors.value.general = errData?.message ?? 'Gagal menyimpan data pegawai.';
        }
    } finally {
        isSaving.value = false;
    }
}

onMounted(async () => {
    await Promise.all([fetchDepartemen(), fetchPegawai()]);
    isLoading.value = false;
});

useFaq([
    { q: 'NIP bisa diubah setelah disimpan?', a: 'Tidak. Nomor Pegawai (NIP) bersifat unik dan permanen. Pastikan NIP sudah benar sebelum menyimpan.', icon: 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0' },
    { q: 'Cara mengubah password pegawai?', a: 'Password hanya bisa direset dari halaman daftar Pegawai, bukan dari form edit ini. Klik tombol "Reset Password" di baris pegawai.', icon: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z' },
    { q: 'Email tidak bisa diubah?', a: 'Email bisa diubah melalui form edit ini selama belum dipakai oleh pegawai lain. Email digunakan untuk login dan notifikasi.', icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
]);
</script>
