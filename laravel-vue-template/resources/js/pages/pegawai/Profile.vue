<template>
    <div class="space-y-6 max-w-2xl">

        <!-- Header -->
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Profil Saya</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola informasi pribadi dan keamanan akun Anda</p>
        </div>

        <!-- ── CARD: DATA DIRI ── -->
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Data Diri</h3>
            </div>

            <div class="p-6">
                <!-- Avatar -->
                <div class="flex items-center gap-5 mb-6">
                    <!-- Preview avatar -->
                    <div class="relative shrink-0">
                        <img
                            v-if="avatarPreview || profile.avatar"
                            :src="avatarPreview || profile.avatar"
                            alt="Foto Profil"
                            class="w-20 h-20 rounded-full object-cover border-2 border-gray-200"
                        />
                        <div
                            v-else
                            class="w-20 h-20 rounded-full bg-green-600 flex items-center justify-center text-white text-2xl font-bold border-2 border-gray-200"
                        >
                            {{ profile.name?.charAt(0) ?? '?' }}
                        </div>

                        <!-- Tombol kamera overlay -->
                        <button
                            @click="$refs.avatarInput.click()"
                            class="absolute bottom-0 right-0 w-7 h-7 bg-white border border-gray-300 rounded-full flex items-center justify-center hover:bg-gray-50 shadow-sm transition-colors"
                            title="Ganti foto"
                        >
                            <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>

                    <div>
                        <p class="text-base font-semibold text-gray-900">{{ profile.name }}</p>
                        <p class="text-sm text-gray-500">{{ profile.employee_number }} · {{ profile.department?.name }}</p>
                        <button
                            @click="$refs.avatarInput.click()"
                            class="mt-2 text-xs text-blue-600 hover:underline"
                        >
                            Ganti foto profil
                        </button>
                        <p v-if="avatarFile" class="text-xs text-gray-400 mt-0.5">
                            {{ avatarFile.name }}
                            <button @click="cancelAvatar" class="text-red-500 ml-1 hover:underline">Batal</button>
                        </p>
                    </div>

                    <!-- Hidden file input -->
                    <input
                        ref="avatarInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                        @change="onAvatarChange"
                    />
                </div>

                <!-- Read-only fields -->
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Nama Lengkap</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2 border border-gray-200">
                                {{ profile.name ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">No. Pegawai</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2 border border-gray-200">
                                {{ profile.employee_number ?? '—' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Email</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2 border border-gray-200">
                                {{ profile.email ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Departemen</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2 border border-gray-200">
                                {{ profile.department?.name ?? '—' }}
                            </p>
                        </div>
                    </div>

                    <!-- Nomor HP — bisa diedit -->
                    <div>
                        <label for="phone" class="block text-xs font-medium text-gray-700 mb-1">
                            Nomor HP
                            <span class="text-gray-400 font-normal ml-1">(dapat diedit)</span>
                        </label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            maxlength="20"
                            placeholder="Contoh: 08123456789"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        />
                    </div>
                </div>

                <!-- Error / Success profil -->
                <div v-if="profileError" class="mt-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                    {{ profileError }}
                </div>
                <div v-if="profileSuccess" class="mt-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                    {{ profileSuccess }}
                </div>

                <div class="mt-5 flex justify-end">
                    <button
                        @click="saveProfile"
                        :disabled="isSavingProfile"
                        class="px-5 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ isSavingProfile ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ── CARD: GANTI PASSWORD ── -->
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Keamanan Akun</h3>
            </div>

            <div class="p-6 space-y-4">
                <!-- Error / Success password -->
                <div v-if="passwordError" class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                    {{ passwordError }}
                </div>
                <div v-if="passwordSuccess" class="text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                    {{ passwordSuccess }}
                </div>

                <div>
                    <label for="current_password" class="block text-xs font-medium text-gray-700 mb-1">Password Lama</label>
                    <input
                        id="current_password"
                        v-model="passwordForm.current_password"
                        type="password"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="new_password" class="block text-xs font-medium text-gray-700 mb-1">Password Baru</label>
                        <input
                            id="new_password"
                            v-model="passwordForm.password"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Min. 8 karakter"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        />
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-xs font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <input
                            id="confirm_password"
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Ulangi password baru"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        />
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button
                        @click="savePassword"
                        :disabled="isSavingPassword"
                        class="px-5 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ isSavingPassword ? 'Menyimpan...' : 'Simpan Password Baru' }}
                    </button>
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

// ── State profil ──
const profile = ref({
    name: '',
    email: '',
    employee_number: '',
    phone: '',
    avatar: null,
    department: null,
});
const form = ref({ phone: '' });
const avatarFile = ref(null);
const avatarPreview = ref(null);
const isSavingProfile = ref(false);
const profileError = ref('');
const profileSuccess = ref('');

// ── State password ──
const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});
const isSavingPassword = ref(false);
const passwordError = ref('');
const passwordSuccess = ref('');

// ── Fetch profil ──
async function fetchProfile() {
    try {
        const { data } = await axios.get('/api/pegawai/profile');
        profile.value = data;
        form.value.phone = data.phone ?? '';
    } catch {
        //
    }
}

// ── Avatar ──
function onAvatarChange(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validasi ukuran < 2MB
    if (file.size > 2 * 1024 * 1024) {
        profileError.value = 'Ukuran foto maksimal 2 MB.';
        return;
    }

    avatarFile.value = file;
    avatarPreview.value = URL.createObjectURL(file);
    profileError.value = '';
}

function cancelAvatar() {
    avatarFile.value = null;
    avatarPreview.value = null;
}

// ── Simpan profil ──
async function saveProfile() {
    isSavingProfile.value = true;
    profileError.value = '';
    profileSuccess.value = '';

    try {
        const formData = new FormData();
        formData.append('phone', form.value.phone ?? '');
        if (avatarFile.value) {
            formData.append('avatar', avatarFile.value);
        }

        await auth.updateProfile(formData);

        // Refresh data profil lokal
        profile.value.phone = form.value.phone;
        if (avatarPreview.value) {
            profile.value.avatar = avatarPreview.value;
        }

        avatarFile.value = null;
        avatarPreview.value = null;
        profileSuccess.value = 'Profil berhasil diperbarui.';

        setTimeout(() => { profileSuccess.value = ''; }, 3000);
    } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors?.avatar) {
            profileError.value = errors.avatar[0];
        } else if (errors?.phone) {
            profileError.value = errors.phone[0];
        } else {
            profileError.value = error.response?.data?.message ?? 'Gagal menyimpan profil.';
        }
    } finally {
        isSavingProfile.value = false;
    }
}

// ── Simpan password ──
async function savePassword() {
    isSavingPassword.value = true;
    passwordError.value = '';
    passwordSuccess.value = '';

    try {
        const { data } = await axios.put('/api/pegawai/password', passwordForm.value);
        passwordSuccess.value = data.message;
        passwordForm.value = { current_password: '', password: '', password_confirmation: '' };
        setTimeout(() => { passwordSuccess.value = ''; }, 4000);
    } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors?.current_password) {
            passwordError.value = errors.current_password[0];
        } else if (errors?.password) {
            passwordError.value = errors.password[0];
        } else {
            passwordError.value = error.response?.data?.message ?? 'Gagal mengubah password.';
        }
    } finally {
        isSavingPassword.value = false;
    }
}

onMounted(fetchProfile);
</script>
