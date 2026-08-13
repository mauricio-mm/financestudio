<script setup>
import { money } from '@/Utils/money';

defineProps({
    title: {
        type: String,
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <article class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-black/5">
        <h2 class="text-lg font-semibold text-slate-900">
            {{ title }}
        </h2>
        <div class="mt-5 space-y-4">
            <div
                v-for="item in items"
                :key="item.label"
                class="flex items-center gap-4"
            >
                <span class="size-3 rounded-full" :class="item.color" />
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="font-medium text-slate-600">{{ item.label }}</span>
                        <span class="font-semibold text-slate-900">{{ money(item.amount) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </article>
</template>
