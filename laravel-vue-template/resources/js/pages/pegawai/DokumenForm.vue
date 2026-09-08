<template>
    <div class="max-w-2xl space-y-5">

        <!-- Back + Header -->
        <div>
            <RouterLink :to="{ name: 'pegawai.dokumen' }" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </RouterLink>
            <h2 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Edit Dokumen' : 'Upload Dokumen Baru' }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ isEdit ? 'Perubahan akan memerlukan persetujuan ulang dari admin.' : 'Dokumen akan masuk status pending dan perlu disetujui admin.' }}
            </p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">

            <!-- Error -->
            <div v-if="errorMessage" class="mb-5 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                {{ errorMessage }}
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-5">

                <!-- Jenis & Kategori -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Jenis Dokumen <span class="text-red-500">*</span></label>
                        <select
                            v-model="form.category_id"
                            @change="form.certification_type_id = ''"
                            :disabled="isEdit"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 disabled:bg-gray-50 disabled:text-gray-400"
                        >
                            <option value="">Pilih jenis</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Kategori Sertifikasi <span class="text-red-500">*</span></label>
                        <select
                            v-model="form.certification_type_id"
                            :disabled="!form.category_id || isEdit"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 disabled:bg-gray-50 disabled:text-gray-400"
                        >
                            <option value="">Pilih kategori</option>
                            <option v-for="type in filteredTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                        </select>
                    </div>
                </div>

                <!-- Nomor Sertifikat -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Sertifikat</label>
                    <input
                        v-model="form.certificate_number"
                        type="text"
                        maxlength="100"
                        placeholder="Contoh: GSI/2026/0123"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                    />
                </div>

                <!-- Tanggal -->
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tgl. Pelaksanaan</label>
                        <input
                            v-model="form.implementation_date"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tgl. Terbit</label>
                        <input
                            v-model="form.issued_date"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tgl. Kadaluarsa <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.expiry_date"
                            type="date"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </div>
                </div>

                <!-- Upload file -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        File Sertifikat
                        <span class="text-gray-400 font-normal ml-1">(PDF, JPG, PNG — maks 5 MB)</span>
                    </label>

                    <!-- File sudah ada (mode edit) -->
                    <div v-if="isEdit && existingFileName && !newFile" class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 mb-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm text-gray-700 flex-1 truncate">{{ existingFileName }}</span>
                        <button @click="existingFileName = ''" type="button" class="text-xs text-red-500 hover:underline shrink-0">Ganti file</button>
                    </div>

                    <!-- Dropzone / file picker -->
                    <div
                        v-if="!isEdit || !existingFileName"
                        @click="$refs.fileInput.click()"
                        @dragover.prevent
                        @drop.prevent="onFileDrop"
                        class="border-2 border-dashed border-gray-300 rounded-lg px-4 py-8 text-center cursor-pointer hover:border-green-400 hover:bg-green-50 transition-colors"
                    >
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-sm text-gray-500">
                            <span class="text-green-600 font-medium">Klik untuk pilih file</span> atau drag & drop
                        </p>
                        <p v-if="newFile" class="mt-2 text-xs text-gray-600 font-medium">{{ newFile.name }}</p>
                    </div>
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="hidden"
                        @change="onFileChange"
                    />
                </div>

                <!-- Submit -->
                <div class="flex justify-end gap-3 pt-2">
                    <RouterLink
                        :to="{ name: 'pegawai.dokumen' }"
                        class="px-5 py-2 text-sm border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Batal
                    </RouterLink>
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="px-5 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ isSubmitting ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Upload Dokumen') }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route  = useRoute();
const router = useRouter();

const isEdit = computed(() => !!route.params.id);

const categories     = ref([]);
const allTypes       = ref([]);
const isSubmitting   = ref(false);
const errorMessage   = ref('');
const existingFileName = ref('');
const newFile        = ref(null);

const form = ref({
    category_id:            '',
    certification_type_id:  '',
    certificate_number:     '',
    implementation_date:    '',
    issued_date:            '',
    expiry_date:            '',
});

const filteredTypes = computed(() =>
    allTypes.value.filter(t => t.category_id === form.value.category_id)
);

async function fetchCategories() {
    try {
        const { data } = await axios.get('/api/certification-categories');
        categories.value = data.data;
        allTypes.value   = data.types;
    } catch {
        categories.value = [];
    }
}

async function fetchDocument() {
    if (!isEdit.value) return;
    try {
        const { data } = await axios.get(`/api/pegawai/documents/${route.params.id}`);
        form.value.certification_type_id  = data.certification_type?.id ?? '';
        form.value.category_id            = data.certification_type?.category?.id ?? '';
        form.value.certificate_number     = data.certificate_number ?? '';
        form.value.implementation_date    = data.implementation_date ?? '';
        form.value.issued_date            = data.issued_date ?? '';
        form.value.expiry_date            = data.expiry_date ?? '';
        existingFileName.value            = data.file_name ?? '';
    } catch {
        errorMessage.value = 'Gagal memuat data dokumen.';
    }
}

function onFileChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
        errorMessage.value = 'Ukuran file maksimal 5 MB.';
        return;
    }
    newFile.value      = file;
    errorMessage.value = '';
}

function onFileDrop(e) {
    const file = e.dataTransfer.files[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
        errorMessage.value = 'Ukuran file maksimal 5 MB.';
        return;
    }
    newFile.value = file;
}

async function handleSubmit() {
    if (!form.value.certification_type_id) {
        errorMessage.value = 'Pilih jenis dan kategori sertifikasi.';
        return;
    }
    if (!form.value.expiry_date) {
        errorMessage.value = 'Tanggal kadaluarsa wajib diisi.';
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = '';

    try {
        const formData = new FormData();
        formData.append('certification_type_id', form.value.certification_type_id);
        formData.append('certificate_number',    form.value.certificate_number ?? '');
        formData.append('implementation_date',   form.value.implementation_date ?? '');
        formData.append('issued_date',           form.value.issued_date ?? '');
        formData.append('expiry_date',           form.value.expiry_date);
        if (newFile.value) {
            formData.append('file', newFile.value);
        }

        if (isEdit.value) {
            await axios.post(`/api/pegawai/documents/${route.params.id}`, formData);
        } else {
            await axios.post('/api/pegawai/documents', formData);
        }

        router.push({ name: 'pegawai.dokumen' });
    } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors) {
            errorMessage.value = Object.values(errors).flat()[0];
        } else {
            errorMessage.value = error.response?.data?.message ?? 'Gagal menyimpan dokumen.';
        }
    } finally {
        isSubmitting.value = false;
    }
}

onMounted(async () => {
    await fetchCategories();
    await fetchDocument();
});
</script>
