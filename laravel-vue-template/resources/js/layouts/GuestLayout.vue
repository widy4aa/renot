<template>
    <div class="min-h-screen bg-white flex flex-col">
        <header class="border-b border-gray-100 py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <RouterLink :to="{ name: 'home' }" class="text-xl font-bold text-gray-900">
                    {{ appName }}
                </RouterLink>
                <nav class="flex items-center gap-4">
                    <RouterLink :to="{ name: 'login' }" class="text-sm text-gray-600 hover:text-gray-900">
                        Masuk
                    </RouterLink>
                </nav>
            </div>
        </header>
        <main class="flex-1">
            <RouterView />
        </main>
    </div>
</template>

<script setup>
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
</script>
