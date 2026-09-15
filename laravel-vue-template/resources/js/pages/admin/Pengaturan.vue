<template>
    <div class="space-y-5">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="card-elevated rounded-2xl px-6 py-5">
            <h2 class="text-2xl font-bold" style="color:#111827;">Pengaturan Sistem</h2>
            <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">Konfigurasi jadwal reminder, template email, dan akun pengiriman email</p>
        </div>

        <!-- ── Tab Bar ────────────────────────────────────── -->
        <div class="flex gap-1 p-1 rounded-xl" style="background:#F3F4F6;">
            <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                class="flex-1 py-2 rounded-lg text-sm font-semibold transition-all"
                :style="activeTab === tab.id ? 'background:#fff; color:#111827; box-shadow:0 1px 4px rgba(0,0,0,0.08);' : 'color:#6B7280;'">
                {{ tab.label }}
            </button>
        </div>

        <!-- ── Tab 1: Jadwal Reminder ─────────────────────── -->
        <div v-if="activeTab === 'reminders'">
            <div class="card-elevated rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #F3F4F6; background:#FAFAFA;">
                    <div>
                        <p class="text-sm font-bold" style="color:#111827;">Jadwal Reminder</p>
                        <p class="text-xs font-medium mt-0.5" style="color:#6B7280;">Reminder dikirim H-X sebelum tanggal kadaluarsa</p>
                    </div>
                    <button @click="showAddReminder = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white"
                        style="background:#ED1B2F;" onmouseover="this.style.background='#c8102e'" onmouseout="this.style.background='#ED1B2F'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah
                    </button>
                </div>
                <div v-if="loadingReminders" class="divide-y" style="border-color:#F9FAFB;">
                    <div v-for="i in 5" :key="i" class="px-5 py-4 animate-pulse flex items-center gap-4">
                        <div class="h-3 bg-gray-200 rounded w-16"></div>
                        <div class="flex-1 h-3 bg-gray-100 rounded"></div>
                        <div class="w-10 h-5 bg-gray-100 rounded-full"></div>
                    </div>
                </div>
                <div v-else>
                    <div v-for="s in reminders" :key="s.id" class="px-5 py-4 flex items-center gap-4 border-b" style="border-color:#F9FAFB;">
                        <div class="w-16 shrink-0">
                            <p class="text-sm font-bold" style="color:#111827;">H-{{ s.days_before }}</p>
                        </div>
                        <div class="flex-1">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full" :style="urgencyBadge(s.urgency)">{{ urgencyLabel(s.urgency) }}</span>
                        </div>
                        <button @click="toggleReminder(s)" class="rounded-full transition-colors relative shrink-0"
                            :style="s.is_active ? 'background:#ACC42A;' : 'background:#E2E8F0;'" style="height:22px; width:40px;">
                            <span class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-all"
                                :style="s.is_active ? 'left:calc(100% - 1.125rem);' : 'left:0.125rem;'"></span>
                        </button>
                        <button @click="openDeleteReminder(s)" class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                            style="color:#ED1B2F;" onmouseover="this.style.background='#FEE2E2'" onmouseout="this.style.background='transparent'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showAddReminder" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="showAddReminder = false">
                    <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                        <h4 class="text-base font-bold mb-4" style="color:#111827;">Tambah Jadwal Reminder</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Hari Sebelum (H-X) <span style="color:#ED1B2F;">*</span></label>
                                <input v-model.number="newReminder.days_before" type="number" min="1" max="365" placeholder="contoh: 90"
                                    class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Tingkat Urgensi <span style="color:#ED1B2F;">*</span></label>
                                <select v-model="newReminder.urgency" class="w-full px-4 py-2.5 rounded-xl text-sm font-medium outline-none"
                                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'">
                                    <option value="rendah">Rendah</option>
                                    <option value="sedang">Sedang</option>
                                    <option value="tinggi">Tinggi</option>
                                    <option value="sangat_tinggi">Sangat Tinggi</option>
                                </select>
                            </div>
                            <p v-if="reminderError" class="text-xs" style="color:#ED1B2F;">{{ reminderError }}</p>
                        </div>
                        <div class="flex gap-3 mt-5">
                            <button @click="showAddReminder = false; reminderError = ''" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                            <button @click="saveReminder" :disabled="isSaving" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                                style="background:#ED1B2F;" onmouseover="this.style.background='#c8102e'" onmouseout="this.style.background='#ED1B2F'">
                                {{ isSaving ? 'Menyimpan...' : 'Tambah' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ── Tab 2: Template Email ──────────────────────── -->
        <div v-if="activeTab === 'email'" class="space-y-4">
            <div v-if="loadingTemplates" class="space-y-3">
                <div v-for="i in 3" :key="i" class="card-elevated rounded-xl p-5 animate-pulse">
                    <div class="h-4 bg-gray-200 rounded w-1/3 mb-3"></div>
                    <div class="h-24 bg-gray-100 rounded-xl"></div>
                </div>
            </div>
            <div v-else v-for="tpl in emailTemplates" :key="tpl.id" class="card-elevated rounded-xl px-5 py-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-bold" style="color:#111827;">{{ templateLabel(tpl.type) }}</p>
                        <p class="text-xs font-medium mt-0.5" style="color:#9CA3AF;">{{ tpl.type }}</p>
                    </div>
                    <button @click="saveTemplate(tpl)" :disabled="isSaving"
                        class="px-4 py-1.5 rounded-lg text-xs font-semibold text-white disabled:opacity-50"
                        style="background:#ED1B2F;" onmouseover="this.style.background='#c8102e'" onmouseout="this.style.background='#ED1B2F'">
                        {{ isSaving && savingTemplateId === tpl.id ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Subjek Email</label>
                        <input v-model="tpl.subject" type="text" class="w-full px-3 py-2 rounded-lg text-sm font-medium outline-none"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide mb-1" style="color:#9CA3AF;">Isi Email</label>
                        <textarea v-model="tpl.body" rows="6" class="w-full px-3 py-2 rounded-lg text-xs font-mono outline-none resize-y"
                            style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827; min-height:120px;"
                            @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'">
                        </textarea>
                    </div>
                    <div v-if="tpl.placeholders" class="text-xs px-3 py-2 rounded-lg" style="background:#F9FAFB; color:#6B7280;">
                        <span class="font-semibold">Placeholder tersedia:</span> {{ Array.isArray(tpl.placeholders) ? tpl.placeholders.join(', ') : tpl.placeholders }}
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Tab 3: Akun Email (Multiple Profiles) ─────── -->
        <div v-if="activeTab === 'smtp'" class="space-y-4">

            <!-- Header + Tambah -->
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold" style="color:#111827;">Profile Email</p>
                    <p class="text-xs mt-0.5" style="color:#6B7280;">Kelola beberapa akun email. Hanya satu yang aktif sebagai pengirim.</p>
                </div>
                <button @click="openMailConfigModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white"
                    style="background:#ED1B2F;" onmouseover="this.style.background='#c8102e'" onmouseout="this.style.background='#ED1B2F'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Profile
                </button>
            </div>

            <!-- Loading -->
            <div v-if="loadingMailConfigs" class="space-y-3">
                <div v-for="i in 2" :key="i" class="card-elevated rounded-xl p-5 animate-pulse flex gap-4">
                    <div class="w-10 h-10 bg-gray-200 rounded-xl"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                        <div class="h-3 bg-gray-100 rounded w-1/2"></div>
                    </div>
                </div>
            </div>

            <!-- Empty -->
            <div v-else-if="mailConfigs.length === 0" class="card-elevated rounded-xl py-12 text-center">
                <svg class="w-10 h-10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#D1D5DB;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <p class="text-sm font-medium" style="color:#6B7280;">Belum ada profile email. Klik "Tambah Profile" untuk memulai.</p>
            </div>

            <!-- Cards -->
            <div v-else class="space-y-3">
                <div v-for="cfg in mailConfigs" :key="cfg.id"
                    class="card-elevated rounded-xl px-5 py-4 transition-all"
                    :style="cfg.is_active ? 'border:1.5px solid #ACC42A;' : ''">

                    <div class="flex items-start gap-4">
                        <!-- Icon -->
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                            :style="cfg.is_active ? 'background:#F0FDF4;' : 'background:#F9FAFB;'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                :style="cfg.is_active ? 'color:#16a34a;' : 'color:#9CA3AF;'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-bold" style="color:#111827;">{{ cfg.name }}</p>
                                <span v-if="cfg.is_active"
                                    class="text-xs font-bold px-2 py-0.5 rounded-full"
                                    style="background:#DCFCE7; color:#15803D;">
                                    ● Aktif
                                </span>
                            </div>
                            <p class="text-xs mt-0.5 truncate" style="color:#6B7280;">
                                {{ cfg.mail_from_address }}
                                <span class="mx-1" style="color:#D1D5DB;">·</span>
                                {{ cfg.mail_host }}:{{ cfg.mail_port }}
                            </p>
                            <p class="text-xs mt-0.5" style="color:#9CA3AF;">Nama pengirim: {{ cfg.mail_from_name }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-1.5 shrink-0 flex-wrap justify-end">
                            <!-- Aktifkan (hanya jika tidak aktif) -->
                            <button v-if="!cfg.is_active"
                                @click="activateConfig(cfg)"
                                :disabled="isActivating === cfg.id"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold disabled:opacity-50"
                                style="background:#F0FDF4; color:#15803D; border:1px solid #BBF7D0;"
                                onmouseover="this.style.background='#DCFCE7'" onmouseout="this.style.background='#F0FDF4'">
                                {{ isActivating === cfg.id ? '...' : 'Aktifkan' }}
                            </button>

                            <!-- Test -->
                            <button @click="openTestModal(cfg)"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold"
                                style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE;"
                                onmouseover="this.style.background='#DBEAFE'" onmouseout="this.style.background='#EFF6FF'">
                                Uji Coba
                            </button>

                            <!-- Edit -->
                            <button @click="openMailConfigModal(cfg)"
                                class="w-7 h-7 rounded-lg flex items-center justify-center"
                                style="color:#6B7280;" onmouseover="this.style.background='#F3F4F6'" onmouseout="this.style.background='transparent'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>

                            <!-- Hapus (disabled jika aktif) -->
                            <button @click="openDeleteConfig(cfg)"
                                :disabled="cfg.is_active"
                                class="w-7 h-7 rounded-lg flex items-center justify-center disabled:opacity-30 disabled:cursor-not-allowed"
                                style="color:#ED1B2F;" onmouseover="this.style.background='#FEE2E2'" onmouseout="this.style.background='transparent'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal hapus reminder -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="deleteReminderTarget" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40); backdrop-filter:blur(2px);" @click.self="deleteReminderTarget = null">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Hapus Jadwal H-{{ deleteReminderTarget?.days_before }}?</h4>
                    <p class="text-sm text-center mt-2" style="color:#6B7280;">Jadwal reminder ini akan dihapus permanen.</p>
                    <div class="flex gap-3 mt-6">
                        <button @click="deleteReminderTarget = null" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="deleteReminder" :disabled="isProcessing" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                            style="background:#ED1B2F;" onmouseover="this.style.background='#c8102e'" onmouseout="this.style.background='#ED1B2F'">
                            {{ isProcessing ? '...' : 'Ya, Hapus' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Modal tambah/edit profile email -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showMailConfigModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);" @click.self="closeMailConfigModal">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                    <div class="px-6 py-5 border-b" style="border-color:#F3F4F6;">
                        <h4 class="text-base font-bold" style="color:#111827;">
                            {{ editingConfig ? 'Edit Profile Email' : 'Tambah Profile Email Baru' }}
                        </h4>
                        <p class="text-xs mt-0.5" style="color:#6B7280;">
                            {{ editingConfig ? `Mengubah profile "${editingConfig.name}"` : 'Simpan konfigurasi SMTP sebagai profile baru' }}
                        </p>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <!-- Nama Profile -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Nama Profile <span style="color:#ED1B2F;">*</span></label>
                            <input v-model="configForm.name" type="text" placeholder="contoh: Gmail Dev, Pertamina SMTP"
                                class="w-full px-3 py-2.5 rounded-xl text-sm font-medium outline-none"
                                style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <!-- Host -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">SMTP Host <span style="color:#ED1B2F;">*</span></label>
                                <input v-model="configForm.mail_host" type="text" placeholder="smtp.gmail.com"
                                    class="w-full px-3 py-2.5 rounded-xl text-sm font-medium outline-none"
                                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                            </div>
                            <!-- Port -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Port <span style="color:#ED1B2F;">*</span></label>
                                <select v-model="configForm.mail_port"
                                    class="w-full px-3 py-2.5 rounded-xl text-sm font-medium outline-none"
                                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'">
                                    <option :value="587">587 — STARTTLS</option>
                                    <option :value="465">465 — SSL/TLS</option>
                                    <option :value="25">25 — Default</option>
                                    <option :value="2525">2525 — Alternatif</option>
                                </select>
                            </div>
                        </div>

                        <!-- Username -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Email / Username <span style="color:#ED1B2F;">*</span></label>
                            <input v-model="configForm.mail_username" type="email" placeholder="noreply@perusahaan.com"
                                class="w-full px-3 py-2.5 rounded-xl text-sm font-medium outline-none"
                                style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">
                                Password / App Password
                                <span v-if="editingConfig" style="color:#9CA3AF; font-weight:400; text-transform:none;"> — kosongkan jika tidak ingin mengubah</span>
                                <span v-else style="color:#ED1B2F;">*</span>
                            </label>
                            <div class="relative">
                                <input v-model="configForm.mail_password"
                                    :type="showPassword ? 'text' : 'password'"
                                    :placeholder="editingConfig ? 'Kosongkan jika tidak berubah' : 'Masukkan password atau App Password Gmail'"
                                    class="w-full px-3 py-2.5 pr-10 rounded-xl text-sm font-medium outline-none"
                                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2" style="color:#9CA3AF;">
                                    <svg v-if="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                            <p class="text-xs mt-1" style="color:#9CA3AF;">
                                Gmail: buat App Password di <span class="font-semibold">myaccount.google.com/apppasswords</span> (butuh 2FA aktif)
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <!-- From Address -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Alamat Pengirim <span style="color:#ED1B2F;">*</span></label>
                                <input v-model="configForm.mail_from_address" type="email" placeholder="noreply@perusahaan.com"
                                    class="w-full px-3 py-2.5 rounded-xl text-sm font-medium outline-none"
                                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                            </div>
                            <!-- From Name -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Nama Pengirim <span style="color:#ED1B2F;">*</span></label>
                                <input v-model="configForm.mail_from_name" type="text" placeholder="ReNot - Pertamina"
                                    class="w-full px-3 py-2.5 rounded-xl text-sm font-medium outline-none"
                                    style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                    @focus="e => e.target.style.borderColor='#ED1B2F'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                            </div>
                        </div>

                        <p v-if="configFormError" class="text-xs font-medium px-3 py-2 rounded-lg" style="background:#FEF2F2; color:#991B1B;">{{ configFormError }}</p>
                    </div>

                    <div class="flex gap-3 px-6 pb-5">
                        <button @click="closeMailConfigModal" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="saveMailConfig" :disabled="isSavingConfig"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                            style="background:#ED1B2F;" onmouseover="this.style.background='#c8102e'" onmouseout="this.style.background='#ED1B2F'">
                            {{ isSavingConfig ? 'Menyimpan...' : (editingConfig ? 'Simpan Perubahan' : 'Tambah Profile') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Modal Uji Coba per Profile -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="testTarget" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);" @click.self="closeTestModal">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
                    <div class="px-6 py-5 border-b" style="border-color:#F3F4F6;">
                        <p class="text-sm font-bold" style="color:#111827;">Uji Coba — {{ testTarget?.name }}</p>
                        <p class="text-xs mt-0.5" style="color:#6B7280;">{{ testTarget?.mail_host }}:{{ testTarget?.mail_port }} · {{ testTarget?.mail_from_address }}</p>
                    </div>
                    <div class="px-6 py-5 space-y-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#9CA3AF;">Email Tujuan Test</label>
                            <input v-model="testEmail" type="email" placeholder="tujuan@gmail.com"
                                class="w-full px-3 py-2.5 rounded-xl text-sm font-medium outline-none"
                                style="background:#F9FAFB; border:1.5px solid #E2E8F0; color:#111827;"
                                @focus="e => e.target.style.borderColor='#006CB8'" @blur="e => e.target.style.borderColor='#E2E8F0'"/>
                        </div>
                        <p v-if="testError" class="text-xs font-medium px-3 py-2 rounded-lg" style="background:#FEF2F2; color:#991B1B;">{{ testError }}</p>
                        <p v-if="testSuccess" class="text-xs font-medium px-3 py-2 rounded-lg" style="background:#F0FDF4; color:#166534;">{{ testSuccess }}</p>
                    </div>
                    <div class="flex gap-3 px-6 pb-5">
                        <button @click="closeTestModal" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Tutup</button>
                        <button @click="sendTest" :disabled="isTesting || !testEmail"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-40"
                            style="background:#006CB8;" onmouseover="this.style.background='#0057a0'" onmouseout="this.style.background='#006CB8'">
                            {{ isTesting ? 'Mengirim...' : 'Kirim Test' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Modal hapus profile -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="deleteConfigTarget" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);" @click.self="deleteConfigTarget = null">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full">
                    <h4 class="text-base font-bold text-center" style="color:#111827;">Hapus Profile "{{ deleteConfigTarget?.name }}"?</h4>
                    <p class="text-sm text-center mt-2" style="color:#6B7280;">Profile ini akan dihapus permanen dan tidak dapat dikembalikan.</p>
                    <div class="flex gap-3 mt-6">
                        <button @click="deleteConfigTarget = null" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated" style="color:#374151;">Batal</button>
                        <button @click="deleteConfig" :disabled="isProcessing" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                            style="background:#ED1B2F;" onmouseover="this.style.background='#c8102e'" onmouseout="this.style.background='#ED1B2F'">
                            {{ isProcessing ? '...' : 'Ya, Hapus' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useFaq } from '@/composables/useFaq.js';
import axios from 'axios';

const activeTab = ref('reminders');
const tabs = [
    { id: 'reminders', label: 'Jadwal Reminder' },
    { id: 'email',     label: 'Template Email' },
    { id: 'smtp',      label: 'Akun Email' },
];

const isSaving         = ref(false);
const isProcessing     = ref(false);
const loadingReminders = ref(true);
const loadingTemplates = ref(true);
const reminders        = ref([]);
const emailTemplates   = ref([]);
const showAddReminder  = ref(false);
const deleteReminderTarget = ref(null);
const reminderError    = ref('');
const savingTemplateId = ref(null);
const newReminder      = ref({ days_before: '', urgency: 'rendah' });

// ── Mail Config state ──────────────────────────────────────
const loadingMailConfigs = ref(true);
const mailConfigs        = ref([]);
const showMailConfigModal = ref(false);
const editingConfig      = ref(null);
const isSavingConfig     = ref(false);
const isActivating       = ref(null);
const configFormError    = ref('');
const showPassword       = ref(false);
const deleteConfigTarget = ref(null);

const configForm = ref({
    name: '',
    mail_host: '',
    mail_port: 587,
    mail_username: '',
    mail_password: '',
    mail_from_address: '',
    mail_from_name: '',
});

// ── Test state ─────────────────────────────────────────────
const testTarget  = ref(null);
const testEmail   = ref('');
const testError   = ref('');
const testSuccess = ref('');
const isTesting   = ref(false);

// ── Reminder helpers ───────────────────────────────────────
const urgencyMap = { rendah: { label: 'Rendah', style: 'background:#EFF6FF; color:#1D4ED8;' }, sedang: { label: 'Sedang', style: 'background:#FEF3C7; color:#92400E;' }, tinggi: { label: 'Tinggi', style: 'background:#FEE2E2; color:#991B1B;' }, sangat_tinggi: { label: 'Sangat Tinggi', style: 'background:#FCE7F3; color:#9D174D;' } };
function urgencyBadge(u) { return urgencyMap[u]?.style ?? 'background:#F3F4F6; color:#6B7280;'; }
function urgencyLabel(u) { return urgencyMap[u]?.label ?? u; }

const templateLabels = { reminder_akan_expired: 'Reminder Akan Expired', dokumen_expired: 'Dokumen Expired', dokumen_pending: 'Dokumen Pending Approval', dokumen_approved: 'Dokumen Disetujui', dokumen_ditolak: 'Dokumen Ditolak' };
function templateLabel(type) { return templateLabels[type] ?? type; }

// ── Fetch ──────────────────────────────────────────────────
async function fetchReminders() {
    loadingReminders.value = true;
    try { const { data } = await axios.get('/api/admin/pengaturan/reminders'); reminders.value = data.data; }
    catch { reminders.value = []; } finally { loadingReminders.value = false; }
}

async function fetchTemplates() {
    loadingTemplates.value = true;
    try { const { data } = await axios.get('/api/admin/pengaturan/email-templates'); emailTemplates.value = data.data; }
    catch { emailTemplates.value = []; } finally { loadingTemplates.value = false; }
}

async function fetchMailConfigs() {
    loadingMailConfigs.value = true;
    try { const { data } = await axios.get('/api/admin/pengaturan/mail-configs'); mailConfigs.value = data.data; }
    catch { mailConfigs.value = []; } finally { loadingMailConfigs.value = false; }
}

// ── Reminder actions ───────────────────────────────────────
async function toggleReminder(s) {
    try { const { data } = await axios.patch(`/api/admin/pengaturan/reminders/${s.id}/toggle`); s.is_active = data.is_active; } catch { /**/ }
}
function openDeleteReminder(s) { deleteReminderTarget.value = s; }
async function deleteReminder() {
    isProcessing.value = true;
    try {
        await axios.delete(`/api/admin/pengaturan/reminders/${deleteReminderTarget.value.id}`);
        reminders.value = reminders.value.filter(r => r.id !== deleteReminderTarget.value.id);
        deleteReminderTarget.value = null;
    } catch { /**/ } finally { isProcessing.value = false; }
}
async function saveReminder() {
    reminderError.value = '';
    if (!newReminder.value.days_before || newReminder.value.days_before < 1) { reminderError.value = 'Hari harus diisi dan minimal 1.'; return; }
    isSaving.value = true;
    try {
        const { data } = await axios.post('/api/admin/pengaturan/reminders', newReminder.value);
        reminders.value.push(data.schedule);
        reminders.value.sort((a, b) => b.days_before - a.days_before);
        showAddReminder.value = false;
        newReminder.value = { days_before: '', urgency: 'rendah' };
    } catch (e) {
        reminderError.value = e.response?.data?.errors?.days_before?.[0] ?? e.response?.data?.message ?? 'Gagal menambah jadwal.';
    } finally { isSaving.value = false; }
}

// ── Template actions ───────────────────────────────────────
async function saveTemplate(tpl) {
    isSaving.value = true; savingTemplateId.value = tpl.id;
    try { await axios.put(`/api/admin/pengaturan/email-templates/${tpl.id}`, { subject: tpl.subject, body: tpl.body }); }
    catch { /**/ } finally { isSaving.value = false; savingTemplateId.value = null; }
}

// ── Mail Config actions ────────────────────────────────────
function openMailConfigModal(cfg = null) {
    editingConfig.value = cfg;
    configFormError.value = '';
    showPassword.value = false;
    if (cfg) {
        configForm.value = { name: cfg.name, mail_host: cfg.mail_host, mail_port: cfg.mail_port, mail_username: cfg.mail_username, mail_password: '', mail_from_address: cfg.mail_from_address, mail_from_name: cfg.mail_from_name };
    } else {
        configForm.value = { name: '', mail_host: '', mail_port: 587, mail_username: '', mail_password: '', mail_from_address: '', mail_from_name: '' };
    }
    showMailConfigModal.value = true;
}

function closeMailConfigModal() { showMailConfigModal.value = false; editingConfig.value = null; }

async function saveMailConfig() {
    configFormError.value = '';
    isSavingConfig.value = true;
    try {
        if (editingConfig.value) {
            const { data } = await axios.put(`/api/admin/pengaturan/mail-configs/${editingConfig.value.id}`, configForm.value);
            const idx = mailConfigs.value.findIndex(c => c.id === editingConfig.value.id);
            if (idx !== -1) { mailConfigs.value[idx] = data.config; }
        } else {
            const { data } = await axios.post('/api/admin/pengaturan/mail-configs', configForm.value);
            mailConfigs.value.push(data.config);
        }
        closeMailConfigModal();
    } catch (e) {
        const errors = e.response?.data?.errors;
        configFormError.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message ?? 'Gagal menyimpan.');
    } finally { isSavingConfig.value = false; }
}

async function activateConfig(cfg) {
    isActivating.value = cfg.id;
    try {
        await axios.post(`/api/admin/pengaturan/mail-configs/${cfg.id}/activate`);
        mailConfigs.value.forEach(c => { c.is_active = c.id === cfg.id; });
    } catch { /**/ } finally { isActivating.value = null; }
}

function openDeleteConfig(cfg) { deleteConfigTarget.value = cfg; }
async function deleteConfig() {
    isProcessing.value = true;
    try {
        await axios.delete(`/api/admin/pengaturan/mail-configs/${deleteConfigTarget.value.id}`);
        mailConfigs.value = mailConfigs.value.filter(c => c.id !== deleteConfigTarget.value.id);
        deleteConfigTarget.value = null;
    } catch (e) {
        alert(e.response?.data?.message ?? 'Gagal menghapus.');
        deleteConfigTarget.value = null;
    } finally { isProcessing.value = false; }
}

function openTestModal(cfg) { testTarget.value = cfg; testEmail.value = ''; testError.value = ''; testSuccess.value = ''; }
function closeTestModal() { testTarget.value = null; testError.value = ''; testSuccess.value = ''; }
async function sendTest() {
    testError.value = ''; testSuccess.value = '';
    isTesting.value = true;
    try {
        const { data } = await axios.post(`/api/admin/pengaturan/mail-configs/${testTarget.value.id}/test`, { test_email: testEmail.value });
        testSuccess.value = data.message;
    } catch (e) {
        testError.value = e.response?.data?.message ?? 'Gagal mengirim test.';
    } finally { isTesting.value = false; }
}

onMounted(() => { fetchReminders(); fetchTemplates(); fetchMailConfigs(); });

useFaq([
    { q: 'Bagaimana cara ganti akun email?', a: 'Tambah profile baru di tab "Akun Email", lalu klik "Aktifkan". Profile yang aktif akan langsung digunakan untuk semua pengiriman email tanpa perlu restart server.', icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
    { q: 'Apakah bisa punya banyak profile?', a: 'Ya. Anda bisa menyimpan banyak profile (Gmail, SMTP perusahaan, dll). Gunakan tombol "Uji Coba" untuk test tanpa harus mengaktifkannya terlebih dahulu.', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
    { q: 'Gmail App Password itu apa?', a: 'App Password adalah kata sandi 16 karakter khusus dari Google. Buat di myaccount.google.com/apppasswords (perlu 2FA aktif). Jangan gunakan password akun Gmail biasa.', icon: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z' },
    { q: 'Bagaimana reminder dikirim?', a: 'Sistem menjalankan command reminders:send setiap hari menggunakan profile email yang sedang aktif.', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
]);
</script>
