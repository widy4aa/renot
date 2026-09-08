<template>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <p class="text-sm font-medium text-gray-500">{{ title }}</p>
        <p class="text-3xl font-bold text-gray-900 mt-2">{{ value }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ description }}</p>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});
</script>
