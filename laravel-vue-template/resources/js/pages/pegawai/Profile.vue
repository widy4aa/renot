<template>
    <div class="space-y-5">

        <!-- ── Header card ─────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold" style="color:#111827;">Profil Saya</h2>
                <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">Kelola informasi pribadi dan keamanan akun Anda</p>
            </div>
        </div>

        <!-- ── 2 Kolom: Data Diri (kiri) + Keamanan (kanan) ── -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            <!-- ── Kiri: Data Diri ───────────────────────── -->
            <div class="card-elevated rounded-2xl overflow-hidden">
                <!-- Header -->
                <div class="flex items-center gap-2.5 px-6 py-4" style="border-bottom:1px solid #F3F4F6;">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#EFF6FF;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#006CB8;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold" style="color:#111827;">Data Diri</h3>
                </div>

                <div class="px-6 py-5 space-y-5">
                    <!-- Avatar -->
                    <div class="flex items-center gap-4">
                        <div class="relative shrink-0">
                            <img
                                v-if="avatarPreview || profile.avatar"
                                :src="avatarPreview || profile.avatar"
                                alt="Foto Profil"
                                class="w-20 h-20 rounded-2xl object-cover"
                                style="border:2px solid #E2E8F0;"
                            />
                            <div
                                v-else
                                class="w-20 h-20 rounded-2xl flex items-center justify-center text-white text-2xl font-bold"
                                style="background:#ED1B2F; border:2px solid #E2E8F0;"
                            >
                                {{ profile.name?.charAt(0) ?? '?' }}
                            </div>
                            <!-- Tombol kamera -->
                            <button
                                @click="$refs.avatarInput.click()"
                                class="absolute -bottom-1.5 -right-1.5 w-8 h-8 rounded-xl flex items-center justify-center shadow-md transition-colors"
                                style="background:#ED1B2F;"
                                title="Ganti foto"
                            >
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                        <div>
                            <p class="text-base font-bold" style="color:#111827;">{{ profile.name }}</p>
                            <p class="text-xs font-semibold mt-0.5" style="color:#6B7280;">{{ profile.employee_number }} · {{ profile.department?.name }}</p>
                            <div class="flex items-center gap-2 mt-2">
                                <button @click="$refs.avatarInput.click()" class="text-xs font-bold" style="color:#ED1B2F;">Ganti foto</button>
                                <template v-if="avatarFile">
                                    <span style="color:#E2E8F0;">·</span>
                                    <span class="text-xs font-medium truncate max-w-[100px]" style="color:#6B7280;">{{ avatarFile.name }}</span>
                                    <button @click="cancelAvatar" class="text-xs font-bold" style="color:#9CA3AF;">Batal</button>
                                </template>
                            </div>
                        </div>
                        <input ref="avatarInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onAvatarChange"/>
                    </div>

                    <!-- Field read-only -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Nama Lengkap</label>
                            <p class="text-sm font-semibold px-3 py-2.5 rounded-xl" style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;">
                                {{ profile.name ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">No. Pegawai</label>
                            <p class="text-sm font-semibold px-3 py-2.5 rounded-xl" style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;">
                                {{ profile.employee_number ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Email</label>
                            <p class="text-sm font-semibold px-3 py-2.5 rounded-xl" style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;">
                                {{ profile.email ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Departemen</label>
                            <p class="text-sm font-semibold px-3 py-2.5 rounded-xl" style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151;">
                                {{ profile.department?.name ?? '—' }}
                            </p>
                        </div>
                    </div>

                    <!-- Nomor HP editable -->
                    <div>
                        <label for="phone" class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">
                            Nomor HP <span class="normal-case font-medium" style="color:#9CA3AF;">(dapat diedit)</span>
                        </label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            maxlength="20"
                            placeholder="Contoh: 08123456789"
                            class="w-full rounded-xl text-sm font-medium outline-none transition-all"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827; padding:10px 12px;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'"
                            @blur="e => e.target.style.borderColor='#E2E8F0'"
                        />
                    </div>

                    <!-- Alert profil -->
                    <div v-if="profileError" class="flex items-start gap-2 px-4 py-3 rounded-xl text-sm font-medium" style="background:#FEE2E2; color:#991B1B;">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        {{ profileError }}
                    </div>
                    <div v-if="profileSuccess" class="flex items-start gap-2 px-4 py-3 rounded-xl text-sm font-medium" style="background:#F7FEE7; color:#5a6e0f;">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ profileSuccess }}
                    </div>

                    <!-- Simpan profil -->
                    <div class="flex justify-end pt-1">
                        <button
                            @click="saveProfile"
                            :disabled="isSavingProfile"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            style="background:#ED1B2F;"
                            onmouseover="this.style.background='#c8102e'"
                            onmouseout="this.style.background='#ED1B2F'"
                        >
                            <svg v-if="isSavingProfile" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ isSavingProfile ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Kanan: Keamanan Akun ───────────────────── -->
            <div class="card-elevated rounded-2xl overflow-hidden">
                <!-- Header -->
                <div class="flex items-center gap-2.5 px-6 py-4" style="border-bottom:1px solid #F3F4F6;">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#FEE2E2;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold" style="color:#111827;">Keamanan Akun</h3>
                </div>

                <div class="px-6 py-5 space-y-5">
                    <!-- Info -->
                    <div class="flex items-start gap-3 px-4 py-3 rounded-xl" style="background:#F9FAFB; border:1.5px solid #E2E8F0;">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#6B7280;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs font-medium" style="color:#6B7280;">Gunakan password yang kuat — minimal 8 karakter, kombinasi huruf dan angka.</p>
                    </div>

                    <!-- Alert password -->
                    <div v-if="passwordError" class="flex items-start gap-2 px-4 py-3 rounded-xl text-sm font-medium" style="background:#FEE2E2; color:#991B1B;">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        {{ passwordError }}
                    </div>
                    <div v-if="passwordSuccess" class="flex items-start gap-2 px-4 py-3 rounded-xl text-sm font-medium" style="background:#F7FEE7; color:#5a6e0f;">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ passwordSuccess }}
                    </div>

                    <!-- Password lama -->
                    <div>
                        <label for="current_password" class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Password Lama</label>
                        <input
                            id="current_password"
                            v-model="passwordForm.current_password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full rounded-xl text-sm font-medium outline-none transition-all"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827; padding:10px 12px;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'"
                            @blur="e => e.target.style.borderColor='#E2E8F0'"
                        />
                    </div>

                    <!-- Password baru -->
                    <div>
                        <label for="new_password" class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Password Baru</label>
                        <input
                            id="new_password"
                            v-model="passwordForm.password"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Min. 8 karakter"
                            class="w-full rounded-xl text-sm font-medium outline-none transition-all"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827; padding:10px 12px;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'"
                            @blur="e => e.target.style.borderColor='#E2E8F0'"
                        />
                    </div>

                    <!-- Konfirmasi password -->
                    <div>
                        <label for="confirm_password" class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Konfirmasi Password Baru</label>
                        <input
                            id="confirm_password"
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Ulangi password baru"
                            class="w-full rounded-xl text-sm font-medium outline-none transition-all"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827; padding:10px 12px;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'"
                            @blur="e => e.target.style.borderColor='#E2E8F0'"
                        />
                    </div>

                    <!-- Simpan password -->
                    <div class="flex justify-end pt-1">
                        <button
                            @click="savePassword"
                            :disabled="isSavingPassword"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            style="background:#111827;"
                            onmouseover="this.style.background='#374151'"
                            onmouseout="this.style.background='#111827'"
                        >
                            <svg v-if="isSavingPassword" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            {{ isSavingPassword ? 'Menyimpan...' : 'Simpan Password Baru' }}
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const auth = useAuthStore();

const profile = ref({ name: '', email: '', employee_number: '', phone: '', avatar: null, department: null });
const form = ref({ phone: '' });
const avatarFile    = ref(null);
const avatarPreview = ref(null);
const isSavingProfile = ref(false);
const profileError    = ref('');
const profileSuccess  = ref('');

const passwordForm = ref({ current_password: '', password: '', password_confirmation: '' });
const isSavingPassword = ref(false);
const passwordError    = ref('');
const passwordSuccess  = ref('');

async function fetchProfile() {
    try {
        const { data } = await axios.get('/api/pegawai/profile');
        profile.value    = data;
        form.value.phone = data.phone ?? '';
    } catch { /**/ }
}

function onAvatarChange(event) {
    const file = event.target.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        profileError.value = 'Ukuran foto maksimal 2 MB.';
        return;
    }
    avatarFile.value    = file;
    avatarPreview.value = URL.createObjectURL(file);
    profileError.value  = '';
}

function cancelAvatar() {
    avatarFile.value    = null;
    avatarPreview.value = null;
}

async function saveProfile() {
    isSavingProfile.value = true;
    profileError.value    = '';
    profileSuccess.value  = '';
    try {
        const formData = new FormData();
        formData.append('phone', form.value.phone ?? '');
        if (avatarFile.value) formData.append('avatar', avatarFile.value);

        await auth.updateProfile(formData);

        profile.value.phone = form.value.phone;
        if (avatarPreview.value) profile.value.avatar = avatarPreview.value;
        avatarFile.value    = null;
        avatarPreview.value = null;
        profileSuccess.value = 'Profil berhasil diperbarui.';
        setTimeout(() => { profileSuccess.value = ''; }, 3000);
    } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors?.avatar) profileError.value = errors.avatar[0];
        else if (errors?.phone) profileError.value = errors.phone[0];
        else profileError.value = error.response?.data?.message ?? 'Gagal menyimpan profil.';
    } finally {
        isSavingProfile.value = false;
    }
}

async function savePassword() {
    isSavingPassword.value = true;
    passwordError.value    = '';
    passwordSuccess.value  = '';
    try {
        const { data } = await axios.put('/api/pegawai/password', passwordForm.value);
        passwordSuccess.value = data.message;
        passwordForm.value = { current_password: '', password: '', password_confirmation: '' };
        setTimeout(() => { passwordSuccess.value = ''; }, 4000);
    } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors?.current_password) passwordError.value = errors.current_password[0];
        else if (errors?.password) passwordError.value = errors.password[0];
        else passwordError.value = error.response?.data?.message ?? 'Gagal mengubah password.';
    } finally {
        isSavingPassword.value = false;
    }
}

onMounted(fetchProfile);
</script>
