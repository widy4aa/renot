<template>
    <div class="space-y-5">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5">
            <h2 class="text-2xl font-bold" style="color:#111827;">Profil Saya</h2>
            <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">Kelola informasi akun dan keamanan</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            <!-- ── Data Diri ───────────────────────────────── -->
            <div class="card-elevated rounded-2xl px-6 py-5">
                <h3 class="text-sm font-bold uppercase tracking-wide mb-5" style="color:#9CA3AF;">Data Akun</h3>

                <!-- Avatar -->
                <div class="flex items-center gap-4 mb-6">
                    <div class="relative">
                        <img
                            v-if="auth.user?.avatar"
                            :src="auth.user.avatar"
                            alt="Avatar"
                            class="w-16 h-16 rounded-2xl object-cover"
                            style="border:2px solid #E2E8F0;"
                        />
                        <div
                            v-else
                            class="w-16 h-16 rounded-2xl flex items-center justify-center text-white text-2xl font-bold"
                            style="background:#006CB8;"
                        >
                            {{ auth.user?.name?.charAt(0)?.toUpperCase() ?? 'A' }}
                        </div>
                    </div>
                    <div>
                        <p class="text-base font-bold" style="color:#111827;">{{ auth.user?.name }}</p>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold mt-1" style="background:#EFF6FF; color:#006CB8;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Admin HR
                        </span>
                    </div>
                </div>

                <!-- Fields read-only -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Nama Lengkap</label>
                        <div class="w-full px-4 py-2.5 rounded-xl text-sm font-medium" style="background:#F3F4F6; color:#374151;">
                            {{ auth.user?.name ?? '—' }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Email</label>
                        <div class="w-full px-4 py-2.5 rounded-xl text-sm font-medium" style="background:#F3F4F6; color:#374151;">
                            {{ auth.user?.email ?? '—' }}
                        </div>
                    </div>
                </div>

                <p class="text-xs mt-4" style="color:#9CA3AF;">Nama dan email hanya dapat diubah oleh administrator sistem.</p>
            </div>

            <!-- ── Keamanan Akun ───────────────────────────── -->
            <div class="card-elevated rounded-2xl px-6 py-5">
                <h3 class="text-sm font-bold uppercase tracking-wide mb-5" style="color:#9CA3AF;">Keamanan Akun</h3>

                <form @submit.prevent="changePassword" class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">
                            Password Saat Ini <span style="color:#ED1B2F;">*</span>
                        </label>
                        <input
                            id="current_password"
                            v-model="form.current_password"
                            type="password"
                            placeholder="Masukkan password saat ini"
                            class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none transition-colors"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'"
                            @blur="e => e.target.style.borderColor='#E2E8F0'"
                        />
                    </div>
                    <div>
                        <label for="new_password" class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">
                            Password Baru <span style="color:#ED1B2F;">*</span>
                        </label>
                        <input
                            id="new_password"
                            v-model="form.new_password"
                            type="password"
                            placeholder="Minimal 8 karakter"
                            class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none transition-colors"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'"
                            @blur="e => e.target.style.borderColor='#E2E8F0'"
                        />
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">
                            Konfirmasi Password Baru <span style="color:#ED1B2F;">*</span>
                        </label>
                        <input
                            id="confirm_password"
                            v-model="form.new_password_confirmation"
                            type="password"
                            placeholder="Ulangi password baru"
                            class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none transition-colors"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'"
                            @blur="e => e.target.style.borderColor='#E2E8F0'"
                        />
                    </div>

                    <!-- Error -->
                    <p v-if="error" class="text-xs font-medium" style="color:#ED1B2F;">{{ error }}</p>

                    <!-- Success -->
                    <div v-if="success" class="px-4 py-3 rounded-xl text-xs font-semibold" style="background:#F7FEE7; color:#5a6e0f;">
                        Password berhasil diubah.
                    </div>

                    <button
                        type="submit"
                        :disabled="isSaving"
                        class="w-full py-2.5 rounded-xl text-sm font-semibold text-white transition-colors disabled:opacity-50"
                        style="background:#ED1B2F;"
                        onmouseover="if(!this.disabled) this.style.background='#c8102e'"
                        onmouseout="this.style.background='#ED1B2F'"
                    >
                        {{ isSaving ? 'Menyimpan...' : 'Simpan Password Baru' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useFaq } from '@/composables/useFaq.js';
import axios from 'axios';

const auth    = useAuthStore();
const isSaving = ref(false);
const error   = ref('');
const success = ref(false);

const form = ref({
    current_password:      '',
    new_password:          '',
    new_password_confirmation: '',
});

async function changePassword() {
    error.value   = '';
    success.value = false;

    if (form.value.new_password !== form.value.new_password_confirmation) {
        error.value = 'Konfirmasi password tidak cocok.';
        return;
    }
    if (form.value.new_password.length < 8) {
        error.value = 'Password baru minimal 8 karakter.';
        return;
    }

    isSaving.value = true;
    try {
        await axios.put('/api/pegawai/password', form.value);
        success.value = true;
        form.value = { current_password: '', new_password: '', new_password_confirmation: '' };
    } catch (e) {
        error.value = e.response?.data?.message ?? 'Gagal mengubah password. Periksa password saat ini.';
    } finally {
        isSaving.value = false;
    }
}

onMounted(() => {});

useFaq([
    { q: 'Cara mengganti password?', a: 'Isi form di bagian Keamanan Akun — masukkan password lama, lalu password baru minimal 8 karakter, dan konfirmasi.', icon: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z' },
    { q: 'Data apa yang bisa diedit admin?', a: 'Admin hanya bisa mengubah password. Nama dan email dikelola oleh administrator sistem.', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { q: 'Cara menambah admin baru?', a: 'Buka menu Manajemen Admin di sidebar untuk mengelola akun admin lainnya.', icon: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z' },
]);
</script>
