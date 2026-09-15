<template>
    <div class="space-y-5">

        <!-- Header -->
        <div class="card-elevated rounded-2xl px-6 py-5">
            <h2 class="text-2xl font-bold" style="color:#111827;">{{ title }}</h2>
            <p class="text-sm font-medium mt-0.5" style="color:#6B7280;">{{ description }}</p>
        </div>

        <!-- Placeholder card -->
        <div class="card-elevated rounded-2xl py-24 flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-5" style="background:#FEE2E2;">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
            <p class="text-base font-bold mb-1" style="color:#111827;">Segera Hadir</p>
            <p class="text-sm font-medium max-w-xs" style="color:#6B7280;">
                Halaman <strong>{{ title }}</strong> sedang dalam pengembangan dan akan segera tersedia.
            </p>
            <div class="flex items-center gap-2 mt-6 px-4 py-2.5 rounded-xl text-xs font-semibold" style="background:#F3F4F6; color:#6B7280;">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Sprint berikutnya
            </div>
        </div>

    </div>
</template>

<script setup>
defineProps({
    title:       { type: String, default: 'Halaman Ini' },
    description: { type: String, default: 'Halaman ini sedang dalam pengembangan.' },
});
</script>
