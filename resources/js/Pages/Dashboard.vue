<script setup>
import { computed, markRaw } from 'vue';
import { ArrowDownCircle, ArrowUpCircle, Users, Wallet } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CashFlowChart from '@/Components/Dashboard/CashFlowChart.vue';
import FinancialRadarChart from '@/Components/Dashboard/FinancialRadarChart.vue';
import SummaryMetricCard from '@/Components/Dashboard/SummaryMetricCard.vue';
import UpcomingBillsCard from '@/Components/Dashboard/UpcomingBillsCard.vue';
import { money } from '@/Utils/formatters';

const props = defineProps({
    people: {
        type: Object,
        required: true,
    },
    metrics: {
        type: Object,
        required: true,
    },
    cashFlow: {
        type: Array,
        required: true,
    },
    upcomingBills: {
        type: Array,
        required: true,
    },
});

const numberFormatter = new Intl.NumberFormat('pt-BR');
const number = (value) => numberFormatter.format(value || 0);

const summaryCards = computed(() => [
    {
        label: 'Pessoas',
        value: props.people.total,
        format: 'number',
        helper: 'Base comercial',
        icon: markRaw(Users),
        accentClass: 'text-indigo-600',
        iconBgClass: 'bg-indigo-50',
        rows: [
            { label: 'Clientes', value: number(props.people.customers) },
            { label: 'Fornecedores', value: number(props.people.suppliers) },
        ],
    },
    {
        label: 'A receber',
        value: props.metrics.receivable_pending,
        helper: 'Pendente',
        icon: markRaw(ArrowDownCircle),
        accentClass: 'text-emerald-600',
        iconBgClass: 'bg-emerald-50',
        rows: [
            { label: 'Recebido', value: money(props.metrics.receivable_received) },
            { label: 'Vencido', value: money(props.metrics.receivable_overdue) },
        ],
    },
    {
        label: 'A pagar',
        value: props.metrics.payable_pending,
        helper: 'Pendente',
        icon: markRaw(ArrowUpCircle),
        accentClass: 'text-rose-600',
        iconBgClass: 'bg-rose-50',
        rows: [
            { label: 'Pago', value: money(props.metrics.payable_paid) },
            { label: 'Vencido', value: money(props.metrics.payable_overdue) },
        ],
    },
    {
        label: 'Saldo',
        value: props.metrics.forecast_balance,
        helper: 'Previsto',
        icon: markRaw(Wallet),
        accentClass: props.metrics.forecast_balance >= 0 ? 'text-sky-600' : 'text-rose-600',
        iconBgClass: props.metrics.forecast_balance >= 0 ? 'bg-sky-50' : 'bg-rose-50',
        rows: [
            { label: 'Realizado', value: money(props.metrics.realized_balance) },
            { label: 'Contas', value: number(props.metrics.receivable_count + props.metrics.payable_count) },
        ],
    },
]);
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
                <SummaryMetricCard
                    v-for="card in summaryCards"
                    :key="card.label"
                    :card="card"
                />
            </section>

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(320px,0.9fr)]">
                <CashFlowChart :items="cashFlow" />
                <FinancialRadarChart :metrics="metrics" />
            </section>

            <section>
                <UpcomingBillsCard :bills="upcomingBills" period-label="7 dias" />
            </section>
        </div>
    </AppLayout>
</template>