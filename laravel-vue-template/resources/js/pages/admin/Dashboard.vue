<template>
    <div>
        <h2 class="text-2xl font-bold text-gray-900">
            Selamat datang, {{ auth.user?.name }}
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                Admin
            </span>
        </p>
    </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
</script>
