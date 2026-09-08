<template>
    <div class="flex flex-col items-center justify-center min-h-screen px-4 text-center">
        <h1 class="text-5xl font-bold text-gray-900 mb-4">
            {{ appName }}
        </h1>
        <p class="text-xl text-gray-500 mb-8 max-w-lg">
            Template monolith Laravel + PostgreSQL + Vue + Tailwind CSS siap pakai.
        </p>
        <div class="flex gap-4">
            <RouterLink
                :to="{ name: 'dashboard' }"
                class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
            >
                Buka Dashboard
            </RouterLink>
            <RouterLink
                :to="{ name: 'login' }"
                class="inline-flex items-center px-6 py-3 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors"
            >
                Masuk
            </RouterLink>
        </div>
    </div>
</template>

<script setup>
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
</script>
