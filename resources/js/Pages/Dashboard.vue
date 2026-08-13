<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const currency = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
});

const money = (value) => currency.format(value);

const summaryCards = [
    {
        label: 'Total a receber',
        value: 42880,
        detail: 'Titulos pendentes',
        trend: '+12,4%',
        color: 'text-amber-600',
        iconBg: 'bg-amber-50',
        icon: [
            'M12 2v20',
            'M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6',
        ],
    },
    {
        label: 'Total recebido',
        value: 28740,
        detail: 'Entradas realizadas',
        trend: '+8,2%',
        color: 'text-emerald-600',
        iconBg: 'bg-emerald-50',
        icon: [
            'M20 6 9 17l-5-5',
        ],
    },
    {
        label: 'Total a pagar',
        value: 31390,
        detail: 'Compromissos em aberto',
        trend: '-3,1%',
        color: 'text-rose-600',
        iconBg: 'bg-rose-50',
        icon: [
            'M3 6h18',
            'M7 6V4a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2',
            'M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6',
        ],
    },
    {
        label: 'Saldo previsto',
        value: 11490,
        detail: 'Receber menos pagar',
        trend: '+5,7%',
        color: 'text-sky-600',
        iconBg: 'bg-sky-50',
        icon: [
            'M3 3v18h18',
            'M7 15l4-4 3 3 5-7',
        ],
    },
];

const managementTotals = [
    { label: 'Recebido', value: 28740, percentage: 67, color: 'bg-emerald-500' },
    { label: 'Vencido a receber', value: 6120, percentage: 14, color: 'bg-amber-500' },
    { label: 'Pago', value: 19480, percentage: 62, color: 'bg-sky-500' },
    { label: 'Vencido a pagar', value: 4380, percentage: 10, color: 'bg-rose-500' },
];

const cashFlow = [
    { month: 'Mar', incoming: 12.4, outgoing: 8.2 },
    { month: 'Abr', incoming: 16.1, outgoing: 10.4 },
    { month: 'Mai', incoming: 14.7, outgoing: 12.2 },
    { month: 'Jun', incoming: 19.5, outgoing: 13.1 },
    { month: 'Jul', incoming: 22.8, outgoing: 14.6 },
    { month: 'Ago', incoming: 25.2, outgoing: 15.8 },
];

const maxCashFlow = Math.max(...cashFlow.flatMap((item) => [item.incoming, item.outgoing]));
const barHeight = (value) => Math.max(14, Math.round((value / maxCashFlow) * 100)) + '%';

const receivableStatus = [
    { label: 'Pendente', amount: 42880, color: 'bg-amber-400' },
    { label: 'Recebido', amount: 28740, color: 'bg-emerald-500' },
    { label: 'Vencido', amount: 6120, color: 'bg-rose-500' },
    { label: 'Cancelado', amount: 980, color: 'bg-slate-300' },
];

const payableStatus = [
    { label: 'Pendente', amount: 31390, color: 'bg-sky-500' },
    { label: 'Pago', amount: 19480, color: 'bg-emerald-500' },
    { label: 'Vencido', amount: 4380, color: 'bg-rose-500' },
    { label: 'Cancelado', amount: 1220, color: 'bg-slate-300' },
];

const upcomingBills = [
    { title: 'NF 1024 - Cliente Atlas', type: 'Receber', due: '15/08', value: 4250, status: 'Pendente' },
    { title: 'Hospedagem Cloud', type: 'Pagar', due: '16/08', value: 680, status: 'Pendente' },
    { title: 'Contrato Manutencao', type: 'Receber', due: '18/08', value: 3900, status: 'Pendente' },
    { title: 'Fornecedor Logistica', type: 'Pagar', due: '20/08', value: 2150, status: 'Vencido' },
];
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                    Painel financeiro
                </p>
                <h1 class="text-xl font-semibold text-slate-900">
                    Visao geral
                </h1>
            </div>
        </template>

        <div class="space-y-6">
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article
                    v-for="card in summaryCards"
                    :key="card.label"
                    class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-black/5"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">
                                {{ card.label }}
                            </p>
                            <p class="mt-2 text-2xl font-semibold text-slate-900">
                                {{ money(card.value) }}
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
            </section>

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(360px,0.95fr)]">
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
                                v-for="item in cashFlow"
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
                            Agosto
                        </span>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                        <div
                            v-for="item in managementTotals"
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
                            {{ money(9260) }}
                        </p>
                        <p class="mt-2 text-sm text-white/50">
                            Resultado entre valores recebidos e pagos.
                        </p>
                    </div>
                </article>
            </section>

            <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(340px,0.9fr)]">
                <article class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Contas a receber
                    </h2>
                    <div class="mt-5 space-y-4">
                        <div
                            v-for="item in receivableStatus"
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

                <article class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Contas a pagar
                    </h2>
                    <div class="mt-5 space-y-4">
                        <div
                            v-for="item in payableStatus"
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

                <article class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-slate-900">
                            Proximos vencimentos
                        </h2>
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            7 dias
                        </span>
                    </div>

                    <div class="mt-5 divide-y divide-slate-100">
                        <div
                            v-for="bill in upcomingBills"
                            :key="bill.title"
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
                </article>
            </section>
        </div>
    </AppLayout>
</template>
