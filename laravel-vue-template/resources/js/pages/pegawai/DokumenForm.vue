<template>
    <div class="space-y-5">

        <!-- ── Header card ─────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <RouterLink
                    :to="isEdit ? { name: 'pegawai.dokumen.detail', params: { id: route.params.id } } : { name: 'pegawai.dokumen' }"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-xl transition-colors shrink-0"
                    style="background:#F3F4F6; color:#374151;"
                    onmouseover="this.style.background='#E5E7EB'"
                    onmouseout="this.style.background='#F3F4F6'"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </RouterLink>
                <div>
                    <h2 class="text-2xl font-bold" style="color:#111827;">
                        {{ isEdit ? 'Edit Dokumen' : 'Upload Dokumen Baru' }}
                    </h2>
                    <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">
                        {{ isEdit ? 'Perubahan akan memerlukan persetujuan ulang dari admin.' : 'Dokumen akan masuk status pending dan perlu disetujui admin.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- ── Form + Preview ──────────────────────────── -->
        <div :class="isEdit ? 'grid grid-cols-1 lg:grid-cols-5 gap-5' : ''">

        <!-- Form card (full width saat tambah, 3/5 saat edit) -->
        <div :class="isEdit ? 'lg:col-span-3' : ''" class="card-elevated rounded-2xl px-6 py-6">

            <!-- Error -->
            <div v-if="errorMessage" class="mb-5 flex items-start gap-3 px-4 py-3 rounded-xl text-sm font-medium" style="background:#FEE2E2; color:#991B1B;">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                {{ errorMessage }}
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">

                <!-- Jenis & Kategori -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#6B7280;">
                            Jenis Dokumen <span style="color:#ED1B2F;">*</span>
                        </label>
                        <select
                            v-model="form.category_id"
                            @change="form.certification_type_id = ''"
                            :disabled="isEdit"
                            class="w-full rounded-xl text-sm font-medium outline-none transition-all disabled:cursor-not-allowed"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151; padding:10px 12px;"
                            @focus="e => !isEdit && (e.target.style.borderColor='#ED1B2F')"
                            @blur="e => e.target.style.borderColor='#E2E8F0'"
                        >
                            <option value="">Pilih jenis</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#6B7280;">
                            Kategori Sertifikasi <span style="color:#ED1B2F;">*</span>
                        </label>
                        <select
                            v-model="form.certification_type_id"
                            :disabled="!form.category_id || isEdit"
                            class="w-full rounded-xl text-sm font-medium outline-none transition-all disabled:cursor-not-allowed"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151; padding:10px 12px;"
                            @focus="e => (!isEdit && form.category_id) && (e.target.style.borderColor='#ED1B2F')"
                            @blur="e => e.target.style.borderColor='#E2E8F0'"
                        >
                            <option value="">Pilih kategori</option>
                            <option v-for="type in filteredTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                        </select>
                    </div>
                </div>

                <!-- Nomor Sertifikat -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#6B7280;">Nomor Sertifikat</label>
                    <input
                        v-model="form.certificate_number"
                        type="text"
                        maxlength="100"
                        placeholder="Contoh: GSI/2026/0123"
                        class="w-full rounded-xl text-sm font-medium outline-none transition-all"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827; padding:10px 12px;"
                        @focus="e => e.target.style.borderColor='#ED1B2F'"
                        @blur="e => e.target.style.borderColor='#E2E8F0'"
                    />
                </div>

                <!-- Tanggal -->
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#6B7280;">Tgl. Pelaksanaan</label>
                        <input
                            v-model="form.implementation_date"
                            type="date"
                            class="w-full rounded-xl text-sm font-medium outline-none transition-all"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151; padding:10px 12px;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'"
                            @blur="e => e.target.style.borderColor='#E2E8F0'"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#6B7280;">Tgl. Terbit</label>
                        <input
                            v-model="form.issued_date"
                            type="date"
                            class="w-full rounded-xl text-sm font-medium outline-none transition-all"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151; padding:10px 12px;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'"
                            @blur="e => e.target.style.borderColor='#E2E8F0'"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#6B7280;">
                            Tgl. Kadaluarsa <span style="color:#ED1B2F;">*</span>
                        </label>
                        <input
                            v-model="form.expiry_date"
                            type="date"
                            required
                            class="w-full rounded-xl text-sm font-medium outline-none transition-all"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#374151; padding:10px 12px;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'"
                            @blur="e => e.target.style.borderColor='#E2E8F0'"
                        />
                    </div>
                </div>

                <!-- Upload file -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#6B7280;">
                        File Sertifikat
                        <span class="normal-case font-medium ml-1" style="color:#9CA3AF;">(PDF, JPG, PNG — maks 5 MB)</span>
                    </label>

                    <!-- File sudah ada (mode edit) -->
                    <div
                        v-if="isEdit && existingFileName && !newFile"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl mb-3"
                        style="background:#F9FAFB; border:1.5px solid #E2E8F0;"
                    >
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:#EFF6FF;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#006CB8;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold flex-1 truncate" style="color:#374151;">{{ existingFileName }}</span>
                        <button
                            @click="existingFileName = ''"
                            type="button"
                            class="text-xs font-bold px-2.5 py-1 rounded-lg transition-colors shrink-0"
                            style="background:#FEE2E2; color:#ED1B2F;"
                        >Ganti File</button>
                    </div>

                    <!-- Dropzone -->
                    <div
                        v-if="!isEdit || !existingFileName"
                        @click="$refs.fileInput.click()"
                        @dragover.prevent
                        @dragenter="isDragging = true"
                        @dragleave="isDragging = false"
                        @drop.prevent="onFileDrop"
                        class="rounded-xl px-4 py-8 text-center cursor-pointer transition-all"
                        :style="isDragging
                            ? 'border:2px dashed #ED1B2F; background:#FFF5F5;'
                            : newFile
                                ? 'border:2px dashed #ACC42A; background:#F7FEE7;'
                                : 'border:2px dashed #E2E8F0; background:#F9FAFB;'"
                    >
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-3"
                            :style="newFile ? 'background:#F7FEE7;' : 'background:#F3F4F6;'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                :style="newFile ? 'color:#ACC42A;' : 'color:#9CA3AF;'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <template v-if="newFile">
                            <p class="text-sm font-bold" style="color:#5a6e0f;">{{ newFile.name }}</p>
                            <p class="text-xs font-medium mt-1" style="color:#6B7280;">{{ (newFile.size / 1024 / 1024).toFixed(2) }} MB · Klik untuk ganti</p>
                        </template>
                        <template v-else>
                            <p class="text-sm font-semibold" style="color:#374151;">
                                <span style="color:#ED1B2F;">Klik untuk pilih file</span> atau drag & drop
                            </p>
                            <p class="text-xs font-medium mt-1" style="color:#9CA3AF;">PDF, JPG, PNG hingga 5 MB</p>
                        </template>
                    </div>
                    <input ref="fileInput" type="file" accept=".pdf,.jpg,.jpeg,.png" class="hidden" @change="onFileChange"/>
                </div>

                <!-- Divider -->
                <div class="h-px" style="background:#F3F4F6;"></div>

                <!-- Submit -->
                <div class="flex justify-end gap-3">
                    <RouterLink
                        :to="isEdit ? { name: 'pegawai.dokumen.detail', params: { id: route.params.id } } : { name: 'pegawai.dokumen' }"
                        class="inline-flex items-center px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors card-elevated"
                        style="color:#374151;"
                    >Batal</RouterLink>
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        style="background:#ED1B2F;"
                        onmouseover="this.style.background='#c8102e'"
                        onmouseout="this.style.background='#ED1B2F'"
                    >
                        <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ isSubmitting ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Upload Dokumen') }}
                    </button>
                </div>

            </form>
        </div>

        <!-- Preview card (hanya saat edit, 2/5) -->
        <div v-if="isEdit" class="lg:col-span-2">
            <div class="card-elevated rounded-2xl overflow-hidden sticky top-5">
                <!-- Header -->
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #F3F4F6;">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#F7FEE7;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ACC42A;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold" style="color:#111827;">Preview Sertifikat</h3>
                    </div>
                    <span v-if="newFile" class="text-xs font-bold px-2 py-1 rounded-lg" style="background:#FEF3C7; color:#92400E;">File baru</span>
                </div>

                <div class="p-4">
                    <!-- Preview file baru yang dipilih -->
                    <template v-if="newFile">
                        <div v-if="newFileIsImage" class="rounded-xl overflow-hidden" style="background:#F9FAFB;">
                            <img :src="newFileUrl" class="w-full object-contain" style="max-height:420px;"/>
                        </div>
                        <div v-else-if="newFileIsPdf" class="rounded-xl overflow-hidden" style="background:#F9FAFB;">
                            <embed :src="newFileUrl + '#toolbar=0&navpanes=0&scrollbar=0&view=FitH'" type="application/pdf" class="w-full rounded-xl" style="height:420px; border:none;"/>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-10 rounded-xl" style="background:#F9FAFB;">
                            <p class="text-sm font-semibold" style="color:#374151;">Preview tidak tersedia</p>
                        </div>
                        <!-- Info file baru -->
                        <div class="mt-3 flex items-center gap-3 px-4 py-3 rounded-xl" style="background:#F7FEE7; border:1px solid #ACC42A30;">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ACC42A;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold truncate" style="color:#374151;">{{ newFile.name }}</p>
                                <p class="text-xs font-medium" style="color:#6B7280;">{{ (newFile.size / 1024 / 1024).toFixed(2) }} MB · File baru</p>
                            </div>
                        </div>
                    </template>

                    <!-- Preview file existing -->
                    <template v-else-if="existingFileName">
                        <div v-if="existingIsImage" class="rounded-xl overflow-hidden" style="background:#F9FAFB;">
                            <img :src="existingFileUrl" class="w-full object-contain" style="max-height:420px;" @error="existingPreviewError = true"/>
                        </div>
                        <div v-else-if="existingIsPdf && existingBlobUrl" class="rounded-xl overflow-hidden" style="background:#F9FAFB;">
                            <embed :src="existingBlobUrl + '#toolbar=0&navpanes=0&scrollbar=0&view=FitH'" type="application/pdf" class="w-full rounded-xl" style="height:420px; border:none;"/>
                        </div>
                        <div v-else-if="existingIsPdf && !existingBlobUrl" class="flex items-center justify-center py-16 rounded-xl" style="background:#F9FAFB;">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" style="color:#9CA3AF;">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-10 rounded-xl" style="background:#F9FAFB;">
                            <p class="text-sm font-semibold" style="color:#374151;">Preview tidak tersedia</p>
                        </div>
                        <!-- Info file existing -->
                        <div class="mt-3 flex items-center gap-3 px-4 py-3 rounded-xl" style="background:#F9FAFB; border:1px solid #F3F4F6;">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#6B7280;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold truncate" style="color:#374151;">{{ existingFileName }}</p>
                                <p class="text-xs font-medium" style="color:#9CA3AF;">File saat ini</p>
                            </div>
                        </div>
                    </template>

                    <!-- Belum ada file -->
                    <div v-else class="flex flex-col items-center justify-center py-16 rounded-xl" style="background:#F9FAFB;">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-3" style="background:#F3F4F6;">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#D1D5DB;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold" style="color:#374151;">Belum ada file</p>
                        <p class="text-xs mt-1 font-medium" style="color:#9CA3AF;">Upload file di form kiri</p>
                    </div>
                </div>
            </div>
        </div>

        </div><!-- end grid -->

    </div><!-- end space-y-5 -->
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route  = useRoute();
const router = useRouter();

const isEdit = computed(() => !!route.params.id);

const categories       = ref([]);
const allTypes         = ref([]);
const isSubmitting     = ref(false);
const errorMessage     = ref('');
const existingFileName    = ref('');
const existingFilePath    = ref('');
const existingFileMime    = ref('');
const existingBlobUrl     = ref(null);
const existingPreviewError = ref(false);
const newFile             = ref(null);
const newFileUrl          = ref(null);
const isDragging          = ref(false);

// ── Preview computed ───────────────────────────────────
const newFileIsImage = computed(() => {
    if (!newFile.value) return false;
    const ext = newFile.value.name.split('.').pop().toLowerCase();
    return ['jpg','jpeg','png','webp','gif'].includes(ext);
});
const newFileIsPdf = computed(() => {
    if (!newFile.value) return false;
    return newFile.value.name.toLowerCase().endsWith('.pdf');
});

const existingIsImage = computed(() => {
    const mime = existingFileMime.value;
    const name = existingFileName.value;
    const ext  = name.split('.').pop().toLowerCase();
    return ['image/jpeg','image/jpg','image/png','image/webp','image/gif'].includes(mime) ||
           ['jpg','jpeg','png','webp','gif'].includes(ext);
});
const existingIsPdf = computed(() => {
    return existingFileMime.value === 'application/pdf' ||
           existingFileName.value.toLowerCase().endsWith('.pdf');
});
const existingFileUrl = computed(() => {
    if (!existingFilePath.value) return null;
    return `/storage/${existingFilePath.value}`;
});

const form = ref({
    category_id:           '',
    certification_type_id: '',
    certificate_number:    '',
    implementation_date:   '',
    issued_date:           '',
    expiry_date:           '',
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
        form.value.certification_type_id = data.certification_type?.id ?? '';
        form.value.category_id           = data.certification_type?.category?.id ?? '';
        form.value.certificate_number    = data.certificate_number ?? '';
        form.value.implementation_date   = data.implementation_date ?? '';
        form.value.issued_date           = data.issued_date ?? '';
        form.value.expiry_date           = data.expiry_date ?? '';
        existingFileName.value           = data.file_name ?? '';
        existingFilePath.value           = data.file_path ?? '';
        existingFileMime.value           = data.file_mime ?? '';

        // Load PDF blob untuk preview
        if (existingIsPdf.value && data.has_file) {
            try {
                const res = await axios.get(`/api/pegawai/documents/${route.params.id}/download`, { responseType: 'blob' });
                const blob = new Blob([res.data], { type: 'application/pdf' });
                existingBlobUrl.value = URL.createObjectURL(blob);
            } catch { /**/ }
        }
    } catch {
        errorMessage.value = 'Gagal memuat data dokumen.';
    }
}

function setNewFile(file) {
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
        errorMessage.value = 'Ukuran file maksimal 5 MB.';
        return;
    }
    newFile.value      = file;
    newFileUrl.value   = URL.createObjectURL(file);
    errorMessage.value = '';
}

function onFileChange(e) {
    setNewFile(e.target.files[0]);
}

function onFileDrop(e) {
    isDragging.value = false;
    setNewFile(e.dataTransfer.files[0]);
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
            router.push({ name: 'pegawai.dokumen.detail', params: { id: route.params.id } });
        } else {
            await axios.post('/api/pegawai/documents', formData);
            router.push({ name: 'pegawai.dokumen' });
        }
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
