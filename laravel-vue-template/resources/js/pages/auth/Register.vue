<template>
    <div class="min-h-screen flex">

        <!-- ── Panel Kiri 70% — Gambar ─────────────────────────── -->
        <div class="hidden lg:flex lg:w-[70%] relative overflow-hidden">
            <img
                src="/og.jpg"
                alt="ReNot background"
                class="absolute inset-0 w-full h-full object-cover object-center"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-black/10"></div>
            <div class="absolute bottom-0 left-0 right-0 p-10">
                <div class="flex items-center gap-3 mb-4">
                    <img src="/icon.png" alt="ReNot" class="h-10 w-10 object-contain brightness-0 invert" />
                    <span class="text-white text-2xl font-bold tracking-tight">ReNot</span>
                </div>
                <h2 class="text-white text-3xl font-bold leading-snug max-w-md">
                    Daftar Akun Baru<br/>Sistem ReNot
                </h2>
                <p class="text-white/70 text-sm mt-3 max-w-sm leading-relaxed">
                    Isi formulir pendaftaran untuk mendapatkan akses ke sistem
                    Reminder &amp; Notifikasi Sertifikasi Pegawai.
                </p>
            </div>
        </div>

        <!-- ── Panel Kanan 30% — Form Register ────────────────── -->
        <div class="w-full lg:w-[30%] flex flex-col min-h-screen bg-white relative">

            <div class="flex-1 flex flex-col justify-center px-8 xl:px-12 py-12">

                <!-- Logo + Nama Aplikasi -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-6">
                        <img src="/icon.png" alt="ReNot" class="h-12 w-12 object-contain" />
                        <div>
                            <p class="text-[11px] font-semibold tracking-widest uppercase" style="color: #006CB8;">
                                Pertamina
                            </p>
                            <h1 class="text-2xl font-bold leading-none" style="color: #111827;">ReNot</h1>
                        </div>
                    </div>

                    <h2 class="text-xl font-semibold" style="color: #111827;">Pendaftaran Akun</h2>
                    <p class="text-sm mt-1" style="color: #6B7280;">Isi data diri Anda untuk mendaftar</p>
                </div>

                <!-- Success message -->
                <div
                    v-if="successMessage"
                    class="mb-6 rounded-xl px-4 py-4"
                    style="background: #F7FEE7; border: 1px solid #d9f099;"
                >
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #5a6e0f;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium" style="color: #5a6e0f;">{{ successMessage }}</p>
                            <p class="text-xs mt-1" style="color: #6B7280;">
                                Silakan cek email Anda secara berkala untuk informasi persetujuan.
                            </p>
                        </div>
                    </div>
                    <RouterLink
                        :to="{ name: 'login' }"
                        class="inline-flex items-center gap-1.5 text-sm font-semibold mt-4 transition-colors hover:underline"
                        style="color: #006CB8;"
                    >
                        Kembali ke halaman login
                    </RouterLink>
                </div>

                <!-- Error message -->
                <div
                    v-if="errorMessage"
                    class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200 rounded-lg px-4 py-3"
                >
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ED1B2F;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-sm" style="color: #ED1B2F;">{{ errorMessage }}</p>
                </div>

                <!-- Form -->
                <form v-if="!successMessage" @submit.prevent="handleRegister" class="space-y-4">

                    <!-- Nama Lengkap -->
                    <div>
                        <label for="name" class="block text-sm font-medium mb-1.5" style="color: #374151;">
                            Nama Lengkap <span style="color: #ED1B2F;">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Nama lengkap Anda"
                            class="w-full rounded-lg border px-3.5 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:border-transparent"
                            style="border-color: #E5E7EB; background: #F8F8F7; color: #111827; --tw-ring-color: #006CB8;"
                        />
                        <p v-if="errors.name" class="text-xs mt-1" style="color: #ED1B2F;">{{ errors.name[0] }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium mb-1.5" style="color: #374151;">
                            Email <span style="color: #ED1B2F;">*</span>
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="nama@perusahaan.com"
                            class="w-full rounded-lg border px-3.5 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:border-transparent"
                            style="border-color: #E5E7EB; background: #F8F8F7; color: #111827; --tw-ring-color: #006CB8;"
                        />
                        <p v-if="errors.email" class="text-xs mt-1" style="color: #ED1B2F;">{{ errors.email[0] }}</p>
                    </div>

                    <!-- NIP -->
                    <div>
                        <label for="employee_number" class="block text-sm font-medium mb-1.5" style="color: #374151;">
                            Nomor Pegawai (NIP) <span style="color: #ED1B2F;">*</span>
                        </label>
                        <input
                            id="employee_number"
                            v-model="form.employee_number"
                            type="text"
                            required
                            placeholder="EMP-XXX"
                            class="w-full rounded-lg border px-3.5 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:border-transparent"
                            style="border-color: #E5E7EB; background: #F8F8F7; color: #111827; --tw-ring-color: #006CB8;"
                        />
                        <p v-if="errors.employee_number" class="text-xs mt-1" style="color: #ED1B2F;">{{ errors.employee_number[0] }}</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium mb-1.5" style="color: #374151;">
                            Nomor HP
                        </label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="text"
                            placeholder="08xxxxxxxxxx"
                            class="w-full rounded-lg border px-3.5 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:border-transparent"
                            style="border-color: #E5E7EB; background: #F8F8F7; color: #111827; --tw-ring-color: #006CB8;"
                        />
                    </div>

                    <!-- Departemen -->
                    <div>
                        <label for="department_id" class="block text-sm font-medium mb-1.5" style="color: #374151;">
                            Departemen <span style="color: #ED1B2F;">*</span>
                        </label>
                        <select
                            id="department_id"
                            v-model="form.department_id"
                            required
                            class="w-full rounded-lg border px-3.5 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:border-transparent"
                            style="border-color: #E5E7EB; background: #F8F8F7; color: #111827; --tw-ring-color: #006CB8;"
                        >
                            <option value="" disabled>Pilih departemen</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </select>
                        <p v-if="errors.department_id" class="text-xs mt-1" style="color: #ED1B2F;">{{ errors.department_id[0] }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium mb-1.5" style="color: #374151;">
                            Kata Sandi <span style="color: #ED1B2F;">*</span>
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="Minimal 8 karakter"
                                class="w-full rounded-lg border px-3.5 py-2.5 pr-10 text-sm transition-colors focus:outline-none focus:ring-2 focus:border-transparent"
                                style="border-color: #E5E7EB; background: #F8F8F7; color: #111827; --tw-ring-color: #006CB8;"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors"
                                style="color: #9CA3AF;"
                                tabindex="-1"
                            >
                                <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="errors.password" class="text-xs mt-1" style="color: #ED1B2F;">{{ errors.password[0] }}</p>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium mb-1.5" style="color: #374151;">
                            Konfirmasi Kata Sandi <span style="color: #ED1B2F;">*</span>
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            placeholder="Ulangi kata sandi"
                            class="w-full rounded-lg border px-3.5 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:border-transparent"
                            style="border-color: #E5E7EB; background: #F8F8F7; color: #111827; --tw-ring-color: #006CB8;"
                        />
                    </div>

                    <!-- Tombol Daftar -->
                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="w-full flex items-center justify-center gap-2 text-white py-2.5 px-4 rounded-lg text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150 mt-2"
                        style="background-color: #006CB8;"
                        @mouseover="$event.currentTarget.style.backgroundColor = '#005a9e'"
                        @mouseleave="$event.currentTarget.style.backgroundColor = '#006CB8'"
                    >
                        <svg v-if="isLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ isLoading ? 'Mendaftarkan...' : 'Daftar' }}
                    </button>

                </form>

                <!-- Link ke Login -->
                <p v-if="!successMessage" class="text-center text-sm mt-5" style="color: #6B7280;">
                    Sudah punya akun?
                    <RouterLink
                        :to="{ name: 'login' }"
                        class="font-semibold transition-colors hover:underline"
                        style="color: #006CB8;"
                    >
                        Masuk di sini
                    </RouterLink>
                </p>
            </div>

            <!-- Footer -->
            <div class="px-8 xl:px-12 py-5 border-t" style="border-color: #E5E7EB;">
                <p class="text-xs" style="color: #9CA3AF;">
                    &copy; {{ currentYear }} ReNot &mdash; PT Pertamina (Persero)
                </p>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const auth = useAuthStore();

const form = ref({
    name: '',
    email: '',
    employee_number: '',
    phone: '',
    department_id: '',
    password: '',
    password_confirmation: '',
});

const departments    = ref([]);
const isLoading      = ref(false);
const errorMessage   = ref('');
const successMessage = ref('');
const errors         = ref({});
const showPassword   = ref(false);
const currentYear    = new Date().getFullYear();

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/departments');
        departments.value = data.data;
    } catch {
        // Daftar departemen gagal dimuat
    }
});

async function handleRegister() {
    isLoading.value      = true;
    errorMessage.value   = '';
    successMessage.value = '';
    errors.value         = {};

    try {
        const data = await auth.register(form.value);
        successMessage.value = data.message;
    } catch (error) {
        const resp = error.response?.data;
        if (resp?.errors) {
            errors.value = resp.errors;
            // Ambil error pertama sebagai pesan utama
            const firstKey = Object.keys(resp.errors)[0];
            errorMessage.value = resp.errors[firstKey][0];
        } else {
            errorMessage.value = resp?.message ?? 'Pendaftaran gagal. Coba lagi.';
        }
    } finally {
        isLoading.value = false;
    }
}
</script>
