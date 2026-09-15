<template>
    <div class="space-y-5">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <RouterLink
                    :to="isEdit ? { name: 'admin.dokumen.detail', params: { id: route.params.id } } : { name: 'admin.dokumen' }"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-xl transition-colors shrink-0"
                    style="background:#F3F4F6; color:#374151;"
                    onmouseover="this.style.background='#E5E7EB'" onmouseout="this.style.background='#F3F4F6'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </RouterLink>
                <div>
                    <h2 class="text-2xl font-bold" style="color:#111827;">{{ isEdit ? 'Edit Dokumen' : 'Tambah Dokumen' }}</h2>
                    <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">
                        {{ isEdit ? 'Perbarui data sertifikasi' : 'Tambah dokumen sertifikasi untuk pegawai' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- ── Loading skeleton ───────────────────────────── -->
        <div v-if="isLoading" class="grid grid-cols-1 lg:grid-cols-5 gap-5">
            <div class="lg:col-span-3 card-elevated rounded-2xl p-6 animate-pulse space-y-4">
                <div v-for="i in 6" :key="i" class="h-12 bg-gray-100 rounded-xl"></div>
            </div>
            <div class="lg:col-span-2 card-elevated rounded-2xl animate-pulse" style="min-height:300px;"></div>
        </div>

        <!-- ── Form + Preview ──────────────────────────────── -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-5 gap-5">

            <!-- Kolom kiri: Form (3/5) -->
            <div class="lg:col-span-3">
                <form @submit.prevent="submitForm" class="card-elevated rounded-2xl px-6 py-5 space-y-5">

                    <!-- Pilih Pegawai (hanya saat tambah baru) -->
                    <div v-if="!isEdit">
                        <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">
                            Pegawai <span style="color:#ED1B2F;">*</span>
                        </label>
                        <select
                            v-model="form.user_id"
                            class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none transition-colors"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'"
                            @blur="e => e.target.style.borderColor='#E2E8F0'"
                            required
                        >
                            <option value="">-- Pilih Pegawai --</option>
                            <option v-for="p in pegawaiList" :key="p.id" :value="p.id">
                                {{ p.name }} ({{ p.employee_number }}) — {{ p.department?.name }}
                            </option>
                        </select>
                        <p v-if="errors.user_id" class="text-xs mt-1" style="color:#ED1B2F;">{{ errors.user_id }}</p>
                    </div>

                    <!-- Kategori & Jenis -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">
                                Kategori Sertifikasi <span style="color:#ED1B2F;">*</span>
                            </label>
                            <select
                                v-model="selectedCategory"
                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none transition-colors"
                                style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                @focus="e => e.target.style.borderColor='#ED1B2F'"
                                @blur="e => e.target.style.borderColor='#E2E8F0'"
                                required
                            >
                                <option value="">-- Pilih Kategori --</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">
                                Jenis Sertifikasi <span style="color:#ED1B2F;">*</span>
                            </label>
                            <select
                                v-model="form.certification_type_id"
                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none transition-colors"
                                style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                @focus="e => e.target.style.borderColor='#ED1B2F'"
                                @blur="e => e.target.style.borderColor='#E2E8F0'"
                                :disabled="!selectedCategory"
                                required
                            >
                                <option value="">-- Pilih Jenis --</option>
                                <option v-for="type in filteredTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                            </select>
                            <p v-if="errors.certification_type_id" class="text-xs mt-1" style="color:#ED1B2F;">{{ errors.certification_type_id }}</p>
                        </div>
                    </div>

                    <!-- Nomor Sertifikat -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Nomor Sertifikat</label>
                        <input v-model="form.certificate_number" type="text" placeholder="Masukkan nomor sertifikat"
                            class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none transition-colors"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                    </div>

                    <!-- Tanggal -->
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Tgl. Pelaksanaan</label>
                            <input v-model="form.implementation_date" type="date"
                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none transition-colors"
                                style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Tgl. Terbit</label>
                            <input v-model="form.issued_date" type="date"
                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none transition-colors"
                                style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">
                                Tgl. Kadaluarsa <span style="color:#ED1B2F;">*</span>
                            </label>
                            <input v-model="form.expiry_date" type="date"
                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none transition-colors"
                                style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"
                                required/>
                            <p v-if="errors.expiry_date" class="text-xs mt-1" style="color:#ED1B2F;">{{ errors.expiry_date }}</p>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">
                            File Sertifikat <span class="font-normal normal-case" style="color:#9CA3AF;">(PDF, JPG, PNG — maks. 5MB)</span>
                        </label>
                        <div
                            class="relative w-full rounded-xl border-2 border-dashed transition-all"
                            :style="isDragging ? 'border-color:#ED1B2F; background:#FFF5F5;' : 'border-color:#E2E8F0; background:#F9FAFB;'"
                            @dragover.prevent="isDragging = true"
                            @dragleave="isDragging = false"
                            @drop.prevent="handleDrop"
                        >
                            <input type="file" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="handleFile"/>
                            <div class="py-8 text-center pointer-events-none">
                                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#9CA3AF;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                <p class="text-sm font-semibold" style="color:#374151;">Klik atau seret file ke sini</p>
                                <p class="text-xs mt-1" style="color:#9CA3AF;">PDF, JPG, PNG — maksimal 5MB</p>
                            </div>
                        </div>
                        <div v-if="selectedFile" class="mt-2 px-4 py-2.5 rounded-xl flex items-center gap-3" style="background:#F7FEE7; border:1px solid #ACC42A30;">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#5a6e0f;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span class="text-xs font-semibold truncate flex-1" style="color:#5a6e0f;">{{ selectedFile.name }}</span>
                            <span class="text-xs shrink-0" style="color:#5a6e0f;">{{ (selectedFile.size / 1024 / 1024).toFixed(2) }} MB</span>
                            <button type="button" @click="selectedFile = null; previewUrl = null" class="shrink-0" style="color:#ED1B2F;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <p v-if="errors.file" class="text-xs mt-1" style="color:#ED1B2F;">{{ errors.file }}</p>
                    </div>

                    <!-- Error global -->
                    <p v-if="errors.general" class="text-xs font-medium px-4 py-3 rounded-xl" style="background:#FEE2E2; color:#ED1B2F;">{{ errors.general }}</p>

                    <!-- Submit -->
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" :disabled="isSaving"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors disabled:opacity-50"
                            style="background:#ED1B2F;"
                            onmouseover="if(!this.disabled) this.style.background='#c8102e'" onmouseout="this.style.background='#ED1B2F'">
                            <span v-if="isSaving" class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Menyimpan...
                            </span>
                            <span v-else>{{ isEdit ? 'Simpan Perubahan' : 'Tambah Dokumen' }}</span>
                        </button>
                        <RouterLink
                            :to="isEdit ? { name: 'admin.dokumen.detail', params: { id: route.params.id } } : { name: 'admin.dokumen' }"
                            class="px-6 py-2.5 rounded-xl text-sm font-semibold card-elevated"
                            style="color:#374151;">
                            Batal
                        </RouterLink>
                    </div>
                </form>
            </div>

            <!-- Kolom kanan: Preview (2/5) -->
            <div class="lg:col-span-2">
                <div class="card-elevated rounded-2xl overflow-hidden sticky top-5">
                    <div class="px-5 py-4" style="border-bottom:1px solid #F3F4F6;">
                        <h3 class="text-sm font-bold" style="color:#111827;">Preview File</h3>
                        <p class="text-xs mt-0.5" style="color:#9CA3AF;">{{ selectedFile ? selectedFile.name : (existingFileName ?? 'Belum ada file') }}</p>
                    </div>
                    <div class="p-4">
                        <!-- Preview file baru (image) -->
                        <div v-if="previewUrl && isNewImage" class="rounded-xl overflow-hidden" style="background:#F9FAFB;">
                            <img :src="previewUrl" class="w-full object-contain" style="max-height:420px;"/>
                        </div>
                        <!-- Preview file baru (PDF) -->
                        <div v-else-if="previewUrl && isNewPdf" class="flex flex-col items-center justify-center py-10 text-center">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <p class="text-sm font-semibold" style="color:#374151;">{{ selectedFile?.name }}</p>
                            <p class="text-xs mt-1" style="color:#9CA3AF;">File PDF siap diupload</p>
                        </div>
                        <!-- Preview file existing (edit mode) -->
                        <div v-else-if="existingFileUrl && isExistingImage" class="rounded-xl overflow-hidden" style="background:#F9FAFB;">
                            <img :src="existingFileUrl" class="w-full object-contain" style="max-height:420px;"/>
                        </div>
                        <!-- Kosong -->
                        <div v-else class="flex flex-col items-center justify-center py-14 text-center">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-3" style="background:#F3F4F6;">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#D1D5DB;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>
                            <p class="text-sm font-medium" style="color:#6B7280;">Preview akan muncul di sini</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useFaq } from '@/composables/useFaq.js';
import axios from 'axios';

const route  = useRoute();
const router = useRouter();

const isEdit   = computed(() => !!route.params.id);
const isLoading = ref(true);
const isSaving  = ref(false);
const isDragging = ref(false);

const categories  = ref([]);
const pegawaiList = ref([]);
const selectedCategory = ref('');
const selectedFile = ref(null);
const previewUrl   = ref(null);
const existingFileName = ref(null);
const existingFileUrl  = ref(null);
const existingFileMime = ref(null);

const form = ref({
    user_id:               '',
    certification_type_id: '',
    certificate_number:    '',
    implementation_date:   '',
    issued_date:           '',
    expiry_date:           '',
});

const errors = ref({});

const filteredTypes = computed(() => {
    if (!selectedCategory.value) return [];
    const cat = categories.value.find(c => c.id === Number(selectedCategory.value));
    return cat?.types ?? [];
});

watch(selectedCategory, () => { form.value.certification_type_id = ''; });

const isNewImage = computed(() => selectedFile.value && ['image/jpeg','image/jpg','image/png','image/webp'].includes(selectedFile.value.type));
const isNewPdf   = computed(() => selectedFile.value && selectedFile.value.type === 'application/pdf');
const isExistingImage = computed(() => existingFileMime.value && ['image/jpeg','image/jpg','image/png','image/webp'].includes(existingFileMime.value));

function handleFile(e) {
    const file = e.target.files[0];
    if (file) setFile(file);
}
function handleDrop(e) {
    isDragging.value = false;
    const file = e.dataTransfer.files[0];
    if (file) setFile(file);
}
function setFile(file) {
    selectedFile.value = file;
    if (['image/jpeg','image/jpg','image/png','image/webp'].includes(file.type)) {
        previewUrl.value = URL.createObjectURL(file);
    } else {
        previewUrl.value = file.name;
    }
}

async function fetchCategories() {
    try {
        const { data } = await axios.get('/api/certification-categories');
        categories.value = data.data ?? data;
    } catch { /**/ }
}

async function fetchPegawai() {
    try {
        const { data } = await axios.get('/api/admin/pegawai');
        pegawaiList.value = data.data ?? [];
    } catch {
        pegawaiList.value = [];
    }
}

async function fetchDocument() {
    if (!isEdit.value) return;
    try {
        const { data } = await axios.get(`/api/admin/documents/${route.params.id}`);
        const d = data;

        // Set category
        const catId = d.certification_type?.category?.id;
        if (catId) selectedCategory.value = String(catId);

        // Set form setelah category di-set (delay 1 tick)
        await new Promise(r => setTimeout(r, 50));

        form.value.certification_type_id = d.certification_type?.id ?? '';
        form.value.certificate_number    = d.certificate_number ?? '';
        form.value.implementation_date   = d.implementation_date ?? '';
        form.value.issued_date           = d.issued_date ?? '';
        form.value.expiry_date           = d.expiry_date ?? '';

        if (d.has_file) {
            existingFileName.value = d.file_name;
            existingFileMime.value = d.file_mime;
            if (['image/jpeg','image/jpg','image/png','image/webp'].includes(d.file_mime)) {
                existingFileUrl.value = `/storage/${d.file_path}`;
            }
        }
    } catch { /**/ }
}

async function submitForm() {
    errors.value = {};
    isSaving.value = true;

    const payload = new FormData();
    if (!isEdit.value && form.value.user_id) payload.append('user_id', form.value.user_id);
    payload.append('certification_type_id', form.value.certification_type_id);
    if (form.value.certificate_number)   payload.append('certificate_number', form.value.certificate_number);
    if (form.value.implementation_date)  payload.append('implementation_date', form.value.implementation_date);
    if (form.value.issued_date)          payload.append('issued_date', form.value.issued_date);
    payload.append('expiry_date', form.value.expiry_date);
    if (selectedFile.value) payload.append('file', selectedFile.value);

    try {
        if (isEdit.value) {
            await axios.post(`/api/admin/documents/${route.params.id}`, payload, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            router.push({ name: 'admin.dokumen.detail', params: { id: route.params.id } });
        } else {
            const { data } = await axios.post('/api/admin/documents', payload, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            router.push({ name: 'admin.dokumen.detail', params: { id: data.document.id } });
        }
    } catch (e) {
        const errData = e.response?.data;
        if (errData?.errors) {
            Object.keys(errData.errors).forEach(k => {
                errors.value[k] = errData.errors[k][0];
            });
        } else {
            errors.value.general = errData?.message ?? 'Gagal menyimpan dokumen. Coba lagi.';
        }
    } finally {
        isSaving.value = false;
    }
}

onMounted(async () => {
    await Promise.all([fetchCategories(), isEdit.value ? fetchDocument() : fetchPegawai()]);
    if (!isEdit.value) await fetchPegawai();
    isLoading.value = false;
});

useFaq([
    { q: 'Dokumen admin langsung aktif?', a: 'Ya. Dokumen yang ditambahkan admin tidak perlu melalui proses approval. Status dihitung otomatis dari tanggal kadaluarsa.', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
    { q: 'Format file yang diterima?', a: 'File sertifikat bisa berupa gambar (JPG, PNG) atau dokumen PDF. Ukuran maksimal 5MB per file.', icon: 'M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13' },
    { q: 'Apa yang terjadi saat dokumen diedit?', a: 'Jika dokumen sudah approved, status otomatis dihitung ulang dari tanggal kadaluarsa baru. File lama disimpan di riwayat.', icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' },
]);
</script>
