<script setup>
import { money } from '@/Utils/money';

defineProps({
    items: {
        type: Array,
        required: true,
    },
    periodLabel: {
        type: String,
        default: 'Agosto',
    },
    realizedBalance: {
        type: Number,
        required: true,
    },
});
</script>

<template>
    <article class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-black/5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">
                    Saldos
                </p>
                <h2 class="mt-1 text-lg font-semibold text-slate-900">
                    Posicao gerencial
                </h2>
            </div>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                {{ periodLabel }}
            </span>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
            <div
                v-for="item in items"
                :key="item.label"
                class="space-y-2"
            >
                <div class="flex items-center justify-between gap-4 text-sm">
                    <span class="font-medium text-slate-600">{{ item.label }}</span>
                    <span class="font-semibold text-slate-900">{{ money(item.value) }}</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100">
                    <div
                        class="h-2 rounded-full"
                        :class="item.color"
                        :style="{ width: item.percentage + '%' }"
                    />
                </div>
            </div>
        </div>

        <div class="mt-8 rounded-lg bg-slate-900 p-5 text-white">
            <p class="text-xs uppercase tracking-[0.18em] text-white/40">
                Saldo realizado
            </p>
            <p class="mt-2 text-3xl font-semibold">
                {{ money(realizedBalance) }}
            </p>
            <p class="mt-2 text-sm text-white/50">
                Resultado entre valores recebidos e pagos.
            </p>
        </div>
    </article>
</template>
