<template>
    <div class="min-h-screen flex">

        <!-- ── Panel Kiri 70% — Gambar ─────────────────────────── -->
        <div class="hidden lg:flex lg:w-[70%] relative overflow-hidden">
            <!-- Background image -->
            <img
                src="/og.jpg"
                alt="ReNot background"
                class="absolute inset-0 w-full h-full object-cover object-center"
            />

            <!-- Overlay gradient dari bawah -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-black/10"></div>

            <!-- Konten pojok kiri bawah -->
            <div class="absolute bottom-0 left-0 right-0 p-10">
                <div class="flex items-center gap-3 mb-4">
                    <img src="/icon.png" alt="ReNot" class="h-10 w-10 object-contain brightness-0 invert" />
                    <span class="text-white text-2xl font-bold tracking-tight">ReNot</span>
                </div>
                <h2 class="text-white text-3xl font-bold leading-snug max-w-md">
                    Sistem Reminder &amp; Notifikasi<br/>Sertifikasi Pegawai
                </h2>
                <p class="text-white/70 text-sm mt-3 max-w-sm leading-relaxed">
                    Pantau masa berlaku dokumen sertifikasi secara terpusat.
                    Reminder otomatis sebelum dokumen kadaluarsa.
                </p>
            </div>
        </div>

        <!-- ── Panel Kanan 30% — Form Login ────────────────────── -->
        <div class="w-full lg:w-[30%] flex flex-col min-h-screen bg-white">

            <!-- Konten utama — vertikal center -->
            <div class="flex-1 flex flex-col justify-center px-8 xl:px-12 py-12">

                <!-- Logo + Nama Aplikasi -->
                <div class="mb-10">
                    <div class="flex items-center gap-3 mb-6">
                        <img src="/icon.png" alt="ReNot" class="h-12 w-12 object-contain" />
                        <div>
                            <p class="text-[11px] font-semibold tracking-widest uppercase" style="color: #006CB8;">
                                Pertamina
                            </p>
                            <h1 class="text-2xl font-bold leading-none" style="color: #111827;">ReNot</h1>
                        </div>
                    </div>

                    <h2 class="text-xl font-semibold" style="color: #111827;">Selamat datang</h2>
                    <p class="text-sm mt-1" style="color: #6B7280;">Masuk untuk melanjutkan</p>
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
                <form @submit.prevent="handleLogin" class="space-y-5">

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium mb-1.5" style="color: #374151;">
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            required
                            placeholder="nama@perusahaan.com"
                            class="w-full rounded-lg border px-3.5 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:border-transparent"
                            style="border-color: #E5E7EB; background: #F8F8F7; color: #111827; --tw-ring-color: #006CB8;"
                        />
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-sm font-medium" style="color: #374151;">
                                Kata Sandi
                            </label>
                            <RouterLink
                                :to="{ name: 'forgot-password' }"
                                class="text-xs font-medium transition-colors hover:underline"
                                style="color: #006CB8;"
                            >
                                Lupa password?
                            </RouterLink>
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                placeholder="••••••••"
                                class="w-full rounded-lg border px-3.5 py-2.5 pr-10 text-sm transition-colors focus:outline-none focus:ring-2 focus:border-transparent"
                                style="border-color: #E5E7EB; background: #F8F8F7; color: #111827;"
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
                    </div>

                    <!-- Tombol Masuk -->
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
                        {{ isLoading ? 'Memproses...' : 'Masuk' }}
                    </button>

                </form>
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
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth   = useAuthStore();
const router = useRouter();

const form         = ref({ email: '', password: '' });
const isLoading    = ref(false);
const errorMessage = ref('');
const showPassword = ref(false);
const currentYear  = new Date().getFullYear();

async function handleLogin() {
    isLoading.value    = true;
    errorMessage.value = '';

    try {
        const user = await auth.login(form.value);
        if (user.role === 'admin') {
            router.push({ name: 'admin.dashboard' });
        } else {
            router.push({ name: 'pegawai.dashboard' });
        }
    } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors?.email) {
            errorMessage.value = errors.email[0];
        } else {
            errorMessage.value = error.response?.data?.message ?? 'Login gagal. Coba lagi.';
        }
    } finally {
        isLoading.value = false;
    }
}
</script>
