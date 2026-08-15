<script setup>
import { computed } from 'vue';
import { money } from '@/Utils/money';

const props = defineProps({
    card: {
        type: Object,
        required: true,
    },
});

const numberFormatter = new Intl.NumberFormat('pt-BR');
const formattedValue = computed(() => props.card.format === 'number'
    ? numberFormatter.format(props.card.value)
    : money(props.card.value));
</script>

<template>
    <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-black/5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">
                    {{ card.label }}
                </p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">
                    {{ formattedValue }}
                </p>
            </div>

            <div
                class="flex size-12 items-center justify-center rounded-lg"
                :class="[card.iconBg, card.color]"
            >
                <svg
                    class="size-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path v-for="path in card.icon" :key="path" :d="path" />
                </svg>
            </div>
        </div>

        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
            <span class="text-slate-400">{{ card.detail }}</span>
            <span class="font-semibold" :class="card.color">{{ card.trend }}</span>
        </div>
    </article>
</template>