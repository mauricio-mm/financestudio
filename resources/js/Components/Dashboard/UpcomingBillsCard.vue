<script setup>
import { money } from '@/Utils/money';

defineProps({
    bills: {
        type: Array,
        required: true,
    },
    periodLabel: {
        type: String,
        default: '7 dias',
    },
});
</script>

<template>
    <article class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-black/5">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900">
                Proximos vencimentos
            </h2>
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                {{ periodLabel }}
            </span>
        </div>

        <div v-if="bills.length > 0" class="mt-5 divide-y divide-slate-100">
            <div
                v-for="bill in bills"
                :key="bill.id"
                class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0"
            >
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-800">
                        {{ bill.title }}
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                        {{ bill.type }} - vence {{ bill.due }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-slate-900">
                        {{ money(bill.value) }}
                    </p>
                    <p
                        class="mt-1 text-xs font-semibold"
                        :class="bill.status === 'Vencido' ? 'text-rose-600' : 'text-amber-600'"
                    >
                        {{ bill.status }}
                    </p>
                </div>
            </div>
        </div>

        <p v-else class="mt-5 rounded-lg bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
            Nenhum vencimento para os proximos dias.
        </p>
    </article>
</template>