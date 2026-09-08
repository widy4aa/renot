<template>
    <div class="min-h-screen flex items-center justify-center px-4" style="background:#F0F0EE; background-image:radial-gradient(ellipse at 80% 0%, rgba(0,108,184,0.04) 0%, transparent 60%);">
        <div class="w-full max-w-sm">

            <!-- Header -->
            <div class="text-center mb-8">
                <div class="flex items-center justify-center gap-2.5 mb-4">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl" style="background:linear-gradient(135deg,#EFF6FF 0%,#DBEAFE 100%); box-shadow:0 2px 8px rgba(0,108,184,0.15);">
                        <img src="/icon.png" alt="ReNot" class="h-6 w-6 object-contain" />
                    </div>
                    <div class="leading-none text-left">
                        <p class="text-[10px] font-semibold tracking-widest uppercase" style="color:#006CB8;">Pertamina</p>
                        <p class="text-sm font-bold" style="color:#111827;">ReNot</p>
                    </div>
                </div>
                <p class="text-sm" style="color:#6B7280;">Reset kata sandi Anda</p>
            </div>

            <div class="bg-white rounded-2xl p-8" style="border:1px solid #E5E7EB; box-shadow:0 8px 32px rgba(0,0,0,0.10), 0 2px 8px rgba(0,0,0,0.06);">

                <!-- Sukses -->
                <div v-if="successMessage" class="mb-5 text-sm rounded-xl px-4 py-3" style="color:#5a6e0f; background:#F7FEE7; border:1px solid #d9f099;">
                    {{ successMessage }}
                </div>

                <!-- Error -->
                <div v-if="errorMessage" class="mb-5 text-sm rounded-xl px-4 py-3" style="color:#991B1B; background:#FEE2E2; border:1px solid #fca5a5;">
                    {{ errorMessage }}
                </div>

                <template v-if="!successMessage">
                    <p class="text-sm mb-5" style="color:#374151;">
                        Masukkan email Anda dan kami akan mengirimkan link untuk mereset kata sandi.
                    </p>

                    <form @submit.prevent="handleSubmit" class="space-y-5">
                        <div>
                            <label for="email" class="block text-sm font-medium mb-1" style="color:#374151;">Email</label>
                            <input
                                id="email"
                                v-model="email"
                                type="email"
                                autocomplete="email"
                                required
                                placeholder="nama@perusahaan.com"
                                class="w-full rounded-lg px-3 py-2 text-sm transition-all outline-none"
                                style="border:1px solid #D1D5DB; color:#111827;"
                                onfocus="this.style.borderColor='#006CB8'; this.style.boxShadow='0 0 0 3px rgba(0,108,184,0.12)'"
                                onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none'"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="isLoading"
                            class="w-full text-white py-2.5 px-4 rounded-lg text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                            style="background:#006CB8;"
                            onmouseover="if(!this.disabled) this.style.background='#005a9e'"
                            onmouseout="this.style.background='#006CB8'"
                        >
                            {{ isLoading ? 'Mengirim...' : 'Kirim Link Reset' }}
                        </button>
                    </form>
                </template>

                <div class="mt-5 text-center">
                    <RouterLink :to="{ name: 'login' }" class="text-sm font-medium transition-colors" style="color:#006CB8;">
                        ← Kembali ke halaman login
                    </RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();

const email = ref('');
const isLoading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

async function handleSubmit() {
    isLoading.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        const data = await auth.forgotPassword(email.value);
        successMessage.value = data.message;
    } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors?.email) {
            errorMessage.value = errors.email[0];
        } else {
            errorMessage.value = error.response?.data?.message ?? 'Terjadi kesalahan. Coba lagi.';
        }
    } finally {
        isLoading.value = false;
    }
}
</script>
