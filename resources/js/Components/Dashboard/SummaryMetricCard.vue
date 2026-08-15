<script setup>
import { computed } from 'vue';
import { money } from '@/Utils/formatters';

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
            <div class="min-w-0">
                <p class="text-sm font-medium text-slate-500">
                    {{ card.label }}
                </p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">
                    {{ formattedValue }}
                </p>
                <p class="mt-1 text-xs font-medium" :class="card.accentClass">
                    {{ card.helper }}
                </p>
            </div>

            <div class="flex size-10 shrink-0 items-center justify-center rounded-lg" :class="card.iconBgClass">
                <component :is="card.icon" class="size-5" :class="card.accentClass" stroke-width="1.8" />
            </div>
        </div>

        <div v-if="card.rows?.length" class="mt-5 space-y-2 border-t border-slate-100 pt-3">
            <div
                v-for="row in card.rows"
                :key="row.label"
                class="flex items-center justify-between gap-3 text-xs"
            >
                <span class="text-slate-400">{{ row.label }}</span>
                <span class="font-semibold text-slate-700">{{ row.value }}</span>
            </div>
        </div>
    </article>
</template>