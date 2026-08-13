<script setup>
import { computed } from 'vue';

const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
});

const maxCashFlow = computed(() => {
    const values = props.items.flatMap((item) => [item.incoming, item.outgoing]);

    return Math.max(...values, 1);
});

const barHeight = (value) => Math.max(14, Math.round((value / maxCashFlow.value) * 100)) + '%';
</script>

<template>
    <article class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-black/5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">
                    Fluxo de caixa
                </p>
                <h2 class="mt-1 text-lg font-semibold text-slate-900">
                    Entradas e saidas mensais
                </h2>
            </div>
            <div class="flex gap-4 text-xs text-slate-500">
                <span class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-emerald-500" />
                    Receber
                </span>
                <span class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-rose-500" />
                    Pagar
                </span>
            </div>
        </div>

        <div class="mt-8 h-72">
            <div class="grid h-full grid-cols-6 items-end gap-3 border-b border-slate-100 pb-8">
                <div
                    v-for="item in items"
                    :key="item.month"
                    class="relative flex h-full items-end justify-center gap-2"
                >
                    <div class="flex h-full w-full max-w-[58px] items-end justify-center gap-1.5">
                        <div
                            class="w-1/2 rounded-t bg-emerald-400 shadow-sm"
                            :style="{ height: barHeight(item.incoming) }"
                        />
                        <div
                            class="w-1/2 rounded-t bg-rose-400 shadow-sm"
                            :style="{ height: barHeight(item.outgoing) }"
                        />
                    </div>
                    <span class="absolute -bottom-7 text-xs font-medium text-slate-400">
                        {{ item.month }}
                    </span>
                </div>
            </div>
        </div>
    </article>
</template>
